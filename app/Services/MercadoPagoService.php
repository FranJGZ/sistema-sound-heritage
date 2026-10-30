<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoService
{
    protected string $webAccessToken;
    protected string $qrAccessToken;
    protected string $posId;
    protected ?string $webhookSecret;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->webAccessToken = (string) config('services.mercadopago.web_access_token');
        // Si no hay token específico para QR, usar el web access token
        $this->qrAccessToken  = (string) (config('services.mercadopago.qr_access_token') ?: $this->webAccessToken);
        $this->posId          = (string) config('services.mercadopago.pos_id', 'CAJA_1');
        $this->webhookSecret  = config('services.mercadopago.webhook_secret');
        $this->isSandbox      = (bool) config('services.mercadopago.sandbox', true);
    }

    /**
     * Canal Tienda Web: Crear Preferencia de Pago en Checkout Pro.
     */
    public function createCheckoutPreference(
        array $items,
        array $payer,
        string $externalReference,
        ?string $backUrlBase = null
    ): ?array {
        $baseUrl = $backUrlBase ?: url('/');
        $webhookUrl = url('/api/mercadopago/webhook');

        $payerEmail = (string) ($payer['email'] ?? 'test_user_buyer@testuser.com');
        if ($this->isSandbox && !str_starts_with(strtolower($payerEmail), 'test_')) {
            $payerEmail = 'test_user_' . substr(md5($payerEmail), 0, 8) . '@testuser.com';
        }

        $payload = [
            'items' => array_map(function ($item) {
                return [
                    'title'       => substr((string) ($item['title'] ?? 'Producto'), 0, 100),
                    'quantity'    => (int) ($item['quantity'] ?? 1),
                    'unit_price'  => (float) ($item['unit_price'] ?? 0),
                    'currency_id' => 'ARS',
                ];
            }, $items),
            'payer' => [
                'name'    => $payer['name'] ?? 'Comprador',
                'surname' => $payer['surname'] ?? 'Prueba',
                'email'   => $payerEmail,
            ],
            'back_urls' => [
                'success' => $baseUrl . '/checkout/success',
                'pending' => $baseUrl . '/checkout/pending',
                'failure' => $baseUrl . '/checkout/failure',
            ],
            'external_reference' => $externalReference,
            'notification_url'   => $webhookUrl,
        ];

        // Mercado Pago exige HTTPS para auto_return. Si estamos bajo HTTP (ej. IP directa), omitirlo para evitar error 400.
        if (str_starts_with($baseUrl, 'https://') || app()->environment('testing')) {
            $payload['auto_return'] = 'approved';
        }

        try {
            $response = Http::withToken($this->webAccessToken)
                ->timeout(15)
                ->post('https://api.mercadopago.com/checkout/preferences', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error MercadoPago createCheckoutPreference: ' . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Exception MercadoPago createCheckoutPreference: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Canal Mostrador / ERP: Crear Orden para cobrar en Caja con QR Estático.
     */
    public function createQrOrder(
        float $amount,
        string $description,
        string $externalReference,
        ?string $posId = null,
        array $items = []
    ): ?array {
        $targetPosId = $posId ?: $this->posId;
        $idempotencyKey = (string) Str::uuid();

        $formattedAmount = number_format($amount, 2, '.', '');

        // Si no se pasaron ítems detallados, armamos uno genérico con el monto total
        if (empty($items)) {
            $items = [
                [
                    'title'        => substr($description, 0, 100),
                    'quantity'     => 1,
                    'unit_price'   => $formattedAmount,
                    'unit_measure' => 'unit',
                ]
            ];
        }

        $payload = [
            'type'               => 'qr',
            'total_amount'       => $formattedAmount,
            'description'        => substr($description, 0, 150),
            'external_reference' => $externalReference,
            'expiration_time'    => 'PT15M', // 15 minutos de vigencia en la caja
            'config' => [
                'qr' => [
                    'external_pos_id' => $targetPosId,
                    'mode'            => 'static',
                ],
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => $formattedAmount,
                    ]
                ],
            ],
            'items' => array_map(function ($it) {
                return [
                    'title'        => substr((string) ($it['title'] ?? 'Concepto'), 0, 100),
                    'quantity'     => (int) ($it['quantity'] ?? 1),
                    'unit_price'   => number_format((float) ($it['unit_price'] ?? 0), 2, '.', ''),
                    'unit_measure' => (string) ($it['unit_measure'] ?? 'unit'),
                ];
            }, $items),
        ];

        try {
            $token = $this->qrAccessToken ?: $this->webAccessToken;
            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Idempotency-Key' => $idempotencyKey,
                ])
                ->timeout(15)
                ->post('https://api.mercadopago.com/v1/orders', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error MercadoPago createQrOrder: ' . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Exception MercadoPago createQrOrder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancelar una orden de QR activa en una caja (ej: si el cliente cancela o paga en efectivo).
     */
    public function cancelQrOrder(string $orderId): bool
    {
        try {
            $token = $this->qrAccessToken ?: $this->webAccessToken;
            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Idempotency-Key' => (string) Str::uuid(),
                ])
                ->timeout(10)
                ->post("https://api.mercadopago.com/v1/orders/{$orderId}/cancel");

            if ($response->successful()) {
                return true;
            }

            Log::error("Error MercadoPago cancelQrOrder: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('Exception MercadoPago cancelQrOrder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Consultar estado en tiempo real de una orden.
     */
    public function getOrder(string $orderId): ?array
    {
        try {
            $token = $this->qrAccessToken ?: $this->webAccessToken;
            $response = Http::withToken($token)
                ->timeout(10)
                ->get("https://api.mercadopago.com/v1/orders/{$orderId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Exception MercadoPago getOrder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validación de firma criptográfica HMAC-SHA256 del Webhook según especificación oficial.
     */
    public function validateWebhookSignature(?string $xSignature, ?string $xRequestId, ?string $dataId): bool
    {
        // Si no está configurado el secret (ambiente local/demo), permitir simulación
        if (empty($this->webhookSecret)) {
            return true;
        }

        if (empty($xSignature)) {
            return false;
        }

        // Extraer ts y v1 del header 'ts=...,v1=...'
        $ts = null;
        $v1 = null;
        $parts = explode(',', $xSignature);
        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                if ($kv[0] === 'ts') {
                    $ts = $kv[1];
                } elseif ($kv[0] === 'v1') {
                    $v1 = $kv[1];
                }
            }
        }

        if (!$ts || !$v1) {
            return false;
        }

        // Armar el template según documentación oficial de Mercado Pago:
        // id:[data.id_url];request-id:[x-request-id_header];ts:[ts_header];
        $manifest = '';
        if (!empty($dataId)) {
            $manifest .= 'id:' . strtolower($dataId) . ';';
        }
        if (!empty($xRequestId)) {
            $manifest .= 'request-id:' . $xRequestId . ';';
        }
        $manifest .= 'ts:' . $ts . ';';

        $computedSignature = hash_hmac('sha256', $manifest, $this->webhookSecret);

        return hash_equals($computedSignature, $v1);
    }

    /**
     * Reembolsar un pago completo en Mercado Pago (Checkout Pro o QR) al anular una factura.
     */
    /**
     * Cancela cualquier orden que esté activa en la Caja física liberando el punto de venta para una nueva orden.
     */
    public function cancelAnyActiveCajaOrder(string $posId = 'CAJA1'): void
    {
        // 1. Limpiar en facturas pendientes con orden en CAJA1
        \App\Models\Invoice::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get()
            ->each(function ($inv) {
                try {
                    $this->cancelQrOrder($inv->mp_order_id);
                } catch (\Throwable $e) {}
                $inv->update(['mp_order_id' => null]);
            });

        // 2. Limpiar en reservas pendientes con orden en CAJA1
        \App\Models\Reservation::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get()
            ->each(function ($res) {
                try {
                    $this->cancelQrOrder($res->mp_order_id);
                } catch (\Throwable $e) {}
                $res->update(['mp_order_id' => null]);
            });

        // 3. Limpiar en inscripciones pendientes con orden en CAJA1
        \App\Models\Attendance::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get()
            ->each(function ($att) {
                try {
                    $this->cancelQrOrder($att->mp_order_id);
                } catch (\Throwable $e) {}
                $att->update(['mp_order_id' => null]);
            });
    }

    /**
     * Ejecuta un reembolso completo en Mercado Pago intentando con los tokens de QR y Web.
     */
    public function executeRefund(?string $paymentId, ?string $orderId, $model = null, string $contextName = 'operación'): ?array
    {
        $tokens = array_filter(array_unique([
            $this->qrAccessToken,
            $this->webAccessToken,
        ]));

        $idempotency = (string) Str::uuid();

        // 1. Intentar reembolso por Payment ID
        if (!empty($paymentId)) {
            foreach ($tokens as $token) {
                try {
                    $response = Http::withToken($token)
                        ->withHeaders(['X-Idempotency-Key' => $idempotency])
                        ->timeout(15)
                        ->post("https://api.mercadopago.com/v1/payments/{$paymentId}/refunds");

                    if ($response->successful()) {
                        $data = $response->json();
                        $refundId = (string) ($data['id'] ?? null);
                        Log::info("MercadoPago: Reembolso exitoso para pago #{$paymentId} de {$contextName} (ID Reembolso: #{$refundId})");

                        if ($model) {
                            $attributes = [
                                'mp_refund_id'   => $refundId,
                                'refunded_at'    => now(),
                                'payment_status' => 'refunded',
                            ];
                            $model->fill($attributes);
                            $model->saveQuietly();
                            try {
                                $model->newModelQuery()->where($model->getKeyName(), $model->getKey())->update($attributes);
                            } catch (\Throwable $e) {}
                        }

                        return [
                            'refund_id' => $refundId,
                            'amount'    => (float) ($data['amount'] ?? 0),
                            'status'    => $data['status'] ?? 'approved',
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::error("MercadoPago exception intentando reembolsar pago #{$paymentId}: " . $e->getMessage());
                }
            }
        }

        // 2. Intentar reembolso por Orders API (QR)
        if (!empty($orderId)) {
            foreach ($tokens as $token) {
                try {
                    $response = Http::withToken($token)
                        ->withHeaders(['X-Idempotency-Key' => $idempotency])
                        ->timeout(15)
                        ->post("https://api.mercadopago.com/v1/orders/{$orderId}/refund");

                    if ($response->successful()) {
                        $data = $response->json();
                        $refundId = (string) ($data['id'] ?? null);
                        Log::info("MercadoPago: Reembolso exitoso para orden #{$orderId} de {$contextName} (ID Reembolso: #{$refundId})");

                        if ($model) {
                            $attributes = [
                                'mp_refund_id'   => $refundId,
                                'refunded_at'    => now(),
                                'payment_status' => 'refunded',
                            ];
                            $model->fill($attributes);
                            $model->saveQuietly();
                            try {
                                $model->newModelQuery()->where($model->getKeyName(), $model->getKey())->update($attributes);
                            } catch (\Throwable $e) {}
                        }

                        return [
                            'refund_id' => $refundId,
                            'amount'    => (float) ($data['amount'] ?? 0),
                            'status'    => $data['status'] ?? 'approved',
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::error("MercadoPago exception intentando reembolsar orden #{$orderId}: " . $e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Reembolsar una factura en Mercado Pago.
     */
    public function refundInvoice(\App\Models\Invoice $invoice): bool
    {
        $res = $this->executeRefund($invoice->mp_payment_id, $invoice->mp_order_id, $invoice, "factura #{$invoice->id}");
        return $res !== null;
    }

    /**
     * Reembolsar una reserva en Mercado Pago.
     */
    public function refundReservation(\App\Models\Reservation $reservation): bool
    {
        $res = $this->executeRefund($reservation->mp_payment_id, $reservation->mp_order_id, $reservation, "reserva #{$reservation->id}");
        return $res !== null;
    }

    /**
     * Reembolsar una entrada de evento en Mercado Pago.
     */
    public function refundAttendance(\App\Models\Attendance $attendance): bool
    {
        $res = $this->executeRefund($attendance->mp_payment_id, $attendance->mp_order_id, $attendance, "entrada #{$attendance->id}");
        return $res !== null;
    }
}
