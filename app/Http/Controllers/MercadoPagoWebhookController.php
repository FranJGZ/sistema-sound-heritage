<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentDetail;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    protected MercadoPagoService $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    public function handle(Request $request): JsonResponse
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');
        $dataId     = $request->query('data_id') ?: $request->input('data.id') ?: $request->input('id') ?: $request->query('id');

        Log::info('MercadoPago Webhook recibido', [
            'headers' => [
                'x-signature'  => $xSignature,
                'x-request-id' => $xRequestId,
            ],
            'query'   => $request->query(),
            'payload' => $request->all(),
        ]);

        // Validación de firma de seguridad HMAC si está configurado el secret y viene la firma
        if ($xSignature && config('services.mercadopago.webhook_secret')) {
            if (!$this->mpService->validateWebhookSignature($xSignature, $xRequestId, (string) $dataId)) {
                Log::warning('MercadoPago Webhook: Firma criptográfica inválida.');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $type   = $request->input('type') ?: $request->query('type');
        $topic  = $request->input('topic') ?: $request->query('topic');
        $action = $request->input('action');
        $data   = $request->input('data', []);
        $id     = $data['id'] ?? $request->input('id') ?? $request->query('id');

        // CASO 1: Notificación de Merchant Order (Webhooks v1 de QR / Órdenes)
        if ($topic === 'merchant_order' || $type === 'merchant_order') {
            if ($id) {
                $this->processMerchantOrderNotification((string) $id);
            }
            return response()->json(['status' => 'ok']);
        }

        // CASO 2: Notificación de Orden (Webhooks v2 Orders API)
        if ($type === 'order' || str_starts_with((string) $action, 'order.')) {
            $this->processOrderNotification($action ?: 'order.processed', $data);
            return response()->json(['status' => 'ok']);
        }

        // CASO 3: Notificación de Pago individual (Checkout Pro clásico o IPN Payment)
        if ($topic === 'payment' || $type === 'payment' || str_starts_with((string) $action, 'payment.')) {
            $paymentId = $id ?: ($data['id'] ?? null);
            if ($paymentId) {
                $this->processPaymentNotification((string) $paymentId);
            }
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function processMerchantOrderNotification(string $merchantOrderId): void
    {
        try {
            $token = config('services.mercadopago.qr_access_token') ?: config('services.mercadopago.web_access_token');
            $response = Http::withToken($token)
                ->timeout(10)
                ->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

            if (!$response->successful()) {
                Log::error("MercadoPago Webhook: No se pudo obtener detalle de merchant_order {$merchantOrderId}");
                return;
            }

            $orderData         = $response->json();
            $status            = $orderData['status'] ?? null;
            $orderStatus       = $orderData['order_status'] ?? null;
            $totalAmount       = (float) ($orderData['total_amount'] ?? 0);
            $paidAmount        = (float) ($orderData['paid_amount'] ?? 0);
            $externalReference = $orderData['external_reference'] ?? null;
            $payments          = $orderData['payments'] ?? [];
            $paymentId         = null;

            foreach ($payments as $payment) {
                if (($payment['status'] ?? null) === 'approved') {
                    $paymentId = $payment['id'] ?? null;
                    break;
                }
            }

            if (!$paymentId && !empty($payments)) {
                $paymentId = $payments[0]['id'] ?? null;
            }

            if (($orderStatus === 'paid' || ($totalAmount > 0 && $paidAmount >= $totalAmount) || $paymentId) && $externalReference) {
                $this->reconcileReference($externalReference, (string) $merchantOrderId, $paymentId ? (string) $paymentId : null);
            }
        } catch (\Throwable $e) {
            Log::error("MercadoPago Webhook exception en processMerchantOrderNotification: {$e->getMessage()}");
        }
    }

    protected function processOrderNotification(string $action, array $data): void
    {
        $orderId           = $data['id'] ?? null;
        $externalReference = $data['external_reference'] ?? null;
        $status            = $data['status'] ?? null;
        $payments          = $data['transactions']['payments'] ?? [];
        $paymentId         = !empty($payments) ? ($payments[0]['id'] ?? null) : null;

        // Si faltan datos en el payload, los consultamos a la API
        if (!$externalReference && $orderId) {
            $remoteOrder = $this->mpService->getOrder((string) $orderId);
            if ($remoteOrder) {
                $externalReference = $remoteOrder['external_reference'] ?? null;
                $status            = $remoteOrder['status'] ?? $status;
                if (!empty($remoteOrder['transactions']['payments'])) {
                    $paymentId = $remoteOrder['transactions']['payments'][0]['id'] ?? $paymentId;
                }
            }
        }

        if (!$externalReference) {
            Log::warning("MercadoPago Webhook: No se encontró external_reference para la orden {$orderId}");
            return;
        }

        // Solo procesamos órdenes acreditadas / aprobadas
        if ($status !== 'processed' && $status !== 'closed' && $status !== 'paid') {
            Log::info("MercadoPago Webhook: Orden {$orderId} con estado {$status}, no requiere acción contable inmediata.");
            return;
        }

        $this->reconcileReference($externalReference, (string) $orderId, (string) $paymentId);
    }

    protected function processPaymentNotification(string $paymentId): void
    {
        try {
            $token = config('services.mercadopago.web_access_token');
            $response = Http::withToken($token)
                ->timeout(10)
                ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if (!$response->successful()) {
                Log::error("MercadoPago Webhook: No se pudo obtener detalle del pago {$paymentId}");
                return;
            }

            $paymentData       = $response->json();
            $status            = $paymentData['status'] ?? null;
            $externalReference = $paymentData['external_reference'] ?? null;
            $orderId           = $paymentData['order']['id'] ?? null;

            if ($status === 'approved' && $externalReference) {
                $this->reconcileReference($externalReference, (string) $orderId, (string) $paymentId);
            }
        } catch (\Throwable $e) {
            Log::error("MercadoPago Webhook exception en processPaymentNotification: {$e->getMessage()}");
        }
    }

    protected function reconcileReference(string $reference, ?string $orderId, ?string $paymentId): void
    {
        // Conciliación de Factura: INV-{id}
        if (str_starts_with($reference, 'INV-')) {
            $invoiceId = (int) substr($reference, 4);
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $invoice->payment_status = 'completed';
                if ($orderId) {
                    $invoice->mp_order_id = $orderId;
                }
                if ($paymentId) {
                    $invoice->mp_payment_id = $paymentId;
                }
                $invoice->save();

                // Asociar detalle de cobro con medio Mercado Pago
                $paymentMethod = PaymentMethod::firstOrCreate(['name' => 'Mercado Pago']);
                PaymentDetail::firstOrCreate([
                    'invoice_id'        => $invoice->id,
                    'payment_method_id' => $paymentMethod->id,
                ]);

                // Enviar correo transaccional con el ticket PDF al cliente acreditado
                try {
                    $customer = $invoice->customer;
                    if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                        \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                            new \App\Mail\PurchaseConfirmationMail($invoice, 'Mostrador / Entrega Inmediata', collect())
                        );
                        Log::info("MercadoPago Webhook: Comprobante PDF enviado a {$customer->email} para factura #{$invoiceId}.");
                    }
                } catch (\Throwable $e) {
                    Log::error("MercadoPago Webhook: Error enviando comprobante por correo: " . $e->getMessage());
                }

                Log::info("MercadoPago Webhook: Factura #{$invoiceId} conciliada y marcada como pagada.");
            }
            return;
        }

        // Conciliación de Reserva: RES-{id} o RES-{id}-TOTAL / RES-{id}-SENA
        if (str_starts_with($reference, 'RES-')) {
            $parts = explode('-', $reference);
            $reservationId = (int) ($parts[1] ?? 0);
            $reservation = Reservation::find($reservationId);
            if ($reservation) {
                $isTotal = str_ends_with($reference, '-TOTAL');
                $reservation->payment_status = $isTotal ? 'Totalmente Abonada' : 'Señada (Resta 70%)';
                if ($isTotal) {
                    $reservation->deposit_amount = (float) $reservation->price;
                }
                // Opción A: No confirmar automáticamente por temas de disponibilidad técnica del salón.
                // El estado operativo permanece en 'Pendiente' hasta que el encargado presione [Aprobar Reserva].
                $reservation->payment_method = 'Mercado Pago';
                if ($orderId) {
                    $reservation->mp_order_id = $orderId;
                }
                if ($paymentId) {
                    $reservation->mp_payment_id = $paymentId;
                }
                $reservation->save();

                Log::info("MercadoPago Webhook: Reserva #{$reservationId} cobrada con éxito ({$reservation->payment_status}). Estado operativo: {$reservation->status}.");
            }
            return;
        }

        // Conciliación de Entrada / Asistencia a Evento: ATT-{id}
        if (str_starts_with($reference, 'ATT-')) {
            $attendanceId = (int) substr($reference, 4);
            $attendance = \App\Models\Attendance::find($attendanceId);
            if ($attendance) {
                $attendance->payment_status = 'completed';
                if ($orderId) {
                    $attendance->mp_order_id = $orderId;
                }
                if ($paymentId) {
                    $attendance->mp_payment_id = $paymentId;
                }
                $attendance->save();

                try {
                    $customer = $attendance->customer;
                    if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                        \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                            new \App\Mail\EventTicketsMail(collect([$attendance]), $attendance->event, 'Mercado Pago Webhook')
                        );
                        Log::info("MercadoPago Webhook: Entrada digital de evento #{$attendanceId} enviada a {$customer->email}");
                    }
                } catch (\Throwable $e) {
                    Log::error("MercadoPago Webhook: Error enviando entrada digital: " . $e->getMessage());
                }

                Log::info("MercadoPago Webhook: Inscripción de evento #{$attendanceId} acreditada exitosamente.");
            }
            return;
        }

        Log::warning("MercadoPago Webhook: Referencia no reconocida {$reference}");
    }
}
