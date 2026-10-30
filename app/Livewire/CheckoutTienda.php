<?php

namespace App\Livewire;

use App\Mail\EventTicketsMail;
use App\Mail\PurchaseConfirmationMail;
use App\Models\Attendance;
use App\Models\City;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\PaymentDetail;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class CheckoutTienda extends Component
{
    use Toast;

    // =========================================================================
    // 1. DATOS DEL CLIENTE / IDENTIFICACIÓN (PASO 2)
    // =========================================================================
    public string $first_name = '';
    public string $last_name = '';
    public string $document_number = '';
    public string $email = '';
    public string $phone = '';

    // =========================================================================
    // 2. LOGÍSTICA / ENTREGA (PASO 3)
    // =========================================================================
    public string $delivery_method = 'Retiro en Local Sound Heritage'; 
    public string $shipping_address = '';
    public string $shipping_city = 'Posadas';
    public string $shipping_postal_code = '';
    public ?string $shipping_notes = null;

    // =========================================================================
    // 3. MEDIO DE PAGO (PASO 4)
    // =========================================================================
    public string $payment_method = 'Mercado Pago';

    // =========================================================================
    // MODALIDAD MODULAR / ESTILO MERCADO LIBRE
    // =========================================================================
    public bool $editingAddress = false;
    public string $tax_condition = 'Consumidor Final';

    public function toggleEditingAddress(): void
    {
        $this->editingAddress = !$this->editingAddress;
    }

    // =========================================================================
    // 4. ESTADO DE FINALIZACIÓN
    // =========================================================================
    public bool $orderConfirmed = false;
    public ?int $confirmedInvoiceId = null;
    public array $createdAttendanceIds = [];
    public ?Invoice $confirmedInvoice = null;

    protected function rules(): array
    {
        $rules = [
            'first_name'      => 'required|string|min:2|max:100',
            'last_name'       => 'required|string|min:2|max:100',
            'document_number' => [
                'required',
                'string',
                'min:7',
                'max:11',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    if ($value === '0' || $value === '00000000' || str_starts_with($value, 'DNI-')) {
                        $fail('El número de documento ingresado no es válido.');
                        return;
                    }

                    $existingCustomer = Customer::where('document_number', $value)->first();
                    if ($existingCustomer && $existingCustomer->user_id) {
                        if (!Auth::check() || Auth::id() !== $existingCustomer->user_id) {
                            $fail('El DNI ingresado pertenece a una cuenta registrada. Iniciá sesión para continuar.');
                        }
                    }
                },
            ],
            'email'           => 'required|email|max:150',
            'phone'           => 'required|string|min:6|max:30',
        ];

        if ($this->hasPhysicalProducts && $this->delivery_method === 'Envío a Domicilio') {
            $rules['shipping_address'] = 'required|string|min:5|max:200';
            $rules['shipping_city']    = 'required|string|min:2|max:100';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'first_name.required'      => 'Ingresá tu nombre.',
            'last_name.required'       => 'Ingresá tu apellido.',
            'document_number.required' => 'El DNI es obligatorio para la factura.',
            'document_number.regex'    => 'El DNI debe contener únicamente números (sin puntos ni letras).',
            'document_number.min'      => 'El DNI debe tener al menos 7 dígitos.',
            'email.required'           => 'Ingresá tu correo electrónico para enviarte los tickets.',
            'email.email'              => 'El formato de correo no es válido.',
            'phone.required'           => 'Ingresá tu teléfono de contacto.',
            'shipping_address.required'=> 'Ingresá la dirección completa para el envío a domicilio.',
            'shipping_city.required'   => 'Ingresá la localidad o ciudad de entrega.',
        ];
    }

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $customer = Customer::with('city')->where('email', $user->email)
                ->orWhere('user_id', $user->id)
                ->first();

            if ($customer) {
                $this->first_name = $customer->first_name ?: (explode(' ', $user->name, 2)[0] ?? '');
                $this->last_name = $customer->last_name ?: (explode(' ', $user->name, 2)[1] ?? '');
                $this->email = $customer->email ?: $user->email;
                if (!empty($customer->document_number) && !str_starts_with($customer->document_number, 'DNI-') && $customer->document_number !== '0') {
                    $this->document_number = $customer->document_number;
                }
                if (!empty($customer->phone) && $customer->phone !== '0') {
                    $this->phone = $customer->phone;
                }
                if (!empty($customer->address) && $customer->address !== 'Registrado desde Storefront Web') {
                    $this->shipping_address = $customer->address;
                }
                if (!empty($customer->city?->name)) {
                    $this->shipping_city = $customer->city->name;
                }
                $this->tax_condition = $customer->tax_condition ?? 'Consumidor Final';
                $this->editingAddress = empty($this->shipping_address);
            } else {
                $parts = explode(' ', $user->name, 2);
                $this->first_name = $parts[0] ?? '';
                $this->last_name = $parts[1] ?? '';
                $this->email = $user->email;
                $this->editingAddress = true;
            }
        } else {
            $this->editingAddress = true;
        }

        if (!$this->hasPhysicalProducts && $this->hasEvents) {
            $this->delivery_method = 'Entrega Digital Inmediata';
        }

        $ref = request()->query('external_reference') ?: request()->query('ref');
        $status = request()->query('collection_status') ?: request()->query('status');

        if ($ref && str_starts_with($ref, 'INV-')) {
            $invId = (int) substr($ref, 4);
            $inv = Invoice::with(['customer', 'invoiceDetails.product', 'paymentDetails.paymentMethod'])->find($invId);
            if ($inv) {
                if (request()->routeIs('checkout.failure') || $status === 'rejected') {
                    $inv->update(['payment_status' => 'failed']);
                    $this->error(
                        title: 'Pago no procesado',
                        description: 'La operación en Mercado Pago no pudo completarse o fue cancelada. Tu carrito continúa activo para que puedas reintentar.',
                        position: 'toast-bottom toast-end',
                        icon: 'o-x-circle',
                        css: 'alert-error font-bold text-white',
                        timeout: 10000
                    );
                } elseif (request()->routeIs('checkout.success') || $status === 'approved') {
                    $inv->update(['payment_status' => 'completed']);
                    session()->forget('carrito');
                    $this->orderConfirmed = true;
                    $this->confirmedInvoiceId = $inv->id;
                    $this->confirmedInvoice = $inv;

                    try {
                        if ($inv->customer?->email) {
                            Mail::to($inv->customer->email)->send(
                                new PurchaseConfirmationMail($inv, $this->delivery_method, collect())
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('Error enviando correo de confirmación: ' . $e->getMessage());
                    }
                } else {
                    // pending
                    $inv->update(['payment_status' => 'pending']);
                    session()->forget('carrito');
                    $this->orderConfirmed = true;
                    $this->confirmedInvoiceId = $inv->id;
                    $this->confirmedInvoice = $inv;
                }
            }
        }
    }

    public function getCarritoProperty(): array
    {
        return session()->get('carrito', []);
    }

    public function getHasPhysicalProductsProperty(): bool
    {
        foreach ($this->carrito as $item) {
            if (($item['type'] ?? 'product') === 'product') {
                return true;
            }
        }
        return false;
    }

    public function getHasEventsProperty(): bool
    {
        foreach ($this->carrito as $item) {
            if (($item['type'] ?? '') === 'event') {
                return true;
            }
        }
        return false;
    }

    public function getCartCountProperty(): int
    {
        $count = 0;
        foreach ($this->carrito as $item) {
            $count += ($item['cantidad'] ?? 1);
        }
        return $count;
    }

    public function getIsCashProperty(): bool
    {
        return !str_contains(strtolower($this->payment_method), 'mercado pago');
    }

    public function getCartSubtotalProperty(): float
    {
        $total = 0.0;
        $isCash = $this->isCash;
        foreach ($this->carrito as $item) {
            $price = (float) ($item['price'] ?? 0);
            $desc = (int) ($item['discount_percentage'] ?? 0);
            $qty = (int) ($item['cantidad'] ?? 1);
            $isEvent = (($item['type'] ?? '') === 'event');

            $offerPrice = ($desc > 0) ? ($price * (1 - ($desc / 100))) : $price;
            $finalPrice = ($isCash && !$isEvent) ? round($offerPrice * 0.90, 2) : $offerPrice;
            $total += ($finalPrice * $qty);
        }
        return (float) $total;
    }

    public function getCartListSubtotalProperty(): float
    {
        $total = 0.0;
        foreach ($this->carrito as $item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['cantidad'] ?? 1);
            $total += ($price * $qty);
        }
        return (float) $total;
    }

    public function getCartSavingsProperty(): float
    {
        return (float) max(0, $this->cartListSubtotal - $this->cartSubtotal);
    }

    public function incrementarCantidad(string|int $key): void
    {
        $carrito = $this->carrito;
        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad']++;
            session()->put('carrito', $carrito);
        }
    }

    public function decrementarCantidad(string|int $key): void
    {
        $carrito = $this->carrito;
        if (isset($carrito[$key])) {
            if ($carrito[$key]['cantidad'] > 1) {
                $carrito[$key]['cantidad']--;
            } else {
                unset($carrito[$key]);
            }
            session()->put('carrito', $carrito);
        }
    }

    public function eliminarItem(string|int $key): void
    {
        $carrito = $this->carrito;
        unset($carrito[$key]);
        session()->put('carrito', $carrito);

        $this->success(
            title: 'Ítem eliminado',
            position: 'toast-bottom toast-end',
            icon: 'o-trash',
            timeout: 2000
        );
    }

    public function confirmarCompra(): void
    {
        if (empty($this->carrito)) {
            $this->error(
                title: 'Tu selección está vacía',
                description: 'Agregá productos o entradas para continuar.',
                position: 'toast-bottom toast-end',
                icon: 'o-shopping-cart'
            );
            return;
        }

        $this->validate();

        DB::beginTransaction();
        try {
            // 1. Cliente
            $cleanDoc = trim($this->document_number);
            $cleanEmail = Auth::check() ? Auth::user()->email : trim($this->email);

            $customer = Customer::where('document_number', $cleanDoc)->first();
            if (!$customer) {
                $customer = Customer::where('email', $cleanEmail)->first();
            }

            $defaultCityId = City::value('id') ?? 1;

            if (!$customer) {
                $customer = Customer::create([
                    'first_name'      => trim($this->first_name),
                    'last_name'       => trim($this->last_name),
                    'document_number' => $cleanDoc,
                    'email'           => $cleanEmail,
                    'phone'           => trim($this->phone),
                    'address'         => $this->delivery_method === 'Envío a Domicilio' ? trim($this->shipping_address) : 'Retiro en Local Sound Heritage',
                    'tax_condition'   => 'Consumidor Final',
                    'city_id'         => $defaultCityId,
                    'user_id'         => Auth::id(),
                ]);
            } else {
                $updates = [];
                if (empty($customer->email) && !empty($cleanEmail)) {
                    $updates['email'] = $cleanEmail;
                }
                if (empty($customer->phone) && !empty($this->phone)) {
                    $updates['phone'] = trim($this->phone);
                }
                if ($customer->document_number === '0' || str_starts_with($customer->document_number, 'DNI-') || str_starts_with($customer->document_number, 'WEB-')) {
                    $updates['document_number'] = $cleanDoc;
                }
                if (Auth::check() && empty($customer->user_id)) {
                    $updates['user_id'] = Auth::id();
                }
                if (!empty($updates)) {
                    $customer->update($updates);
                }
            }

            // 2. Factura
            $nextReceiptNumber = Invoice::getNextReceiptNumber('0001');
            $storeId = Store::value('id') ?? 1;
            $employeeId = Employee::value('id') ?? 1;

            $isMercadoPago = str_contains(strtolower($this->payment_method), 'mercado pago')
                && !empty(config('services.mercadopago.web_access_token'))
                && !app()->runningUnitTests();

            $invoice = Invoice::create([
                'invoice_type'   => 'B',
                'point_of_sale'  => '0001',
                'receipt_number' => $nextReceiptNumber,
                'issue_date'     => now()->format('Y-m-d'),
                'due_date'       => now()->addDays(15)->format('Y-m-d'),
                'total'          => $this->cartSubtotal,
                'store_id'       => $storeId,
                'customer_id'    => $customer->id,
                'employee_id'    => $employeeId,
                'payment_status' => $isMercadoPago ? 'pending' : 'completed',
            ]);

            // 3. Detalles de Factura y Creación de Entradas
            $createdAttendances = collect();

            foreach ($this->carrito as $key => $item) {
                $isEvent = (($item['type'] ?? '') === 'event');
                $qty = (int) ($item['cantidad'] ?? 1);
                $unitPrice = (float) ($item['price'] ?? 0);
                $desc = (int) ($item['discount_percentage'] ?? 0);
                $offerPrice = ($desc > 0) ? ($unitPrice * (1 - ($desc / 100))) : $unitPrice;
                $finalPrice = (!$isMercadoPago && !$isEvent) ? round($offerPrice * 0.90, 2) : $offerPrice;
                $subtotal = $finalPrice * $qty;

                if ($isEvent) {
                    $eventId = $item['event_id'] ?? null;
                    InvoiceDetail::create([
                        'invoice_id'  => $invoice->id,
                        'product_id'  => null,
                        'description' => $item['name'] ?? 'Entrada a Evento Cultural',
                        'quantity'    => $qty,
                        'unit_price'  => $finalPrice,
                        'subtotal'    => $subtotal,
                    ]);

                    if ($eventId) {
                        for ($i = 0; $i < $qty; $i++) {
                            $att = Attendance::create([
                                'registration_date' => now()->format('Y-m-d'),
                                'customer_id'       => $customer->id,
                                'event_id'          => $eventId,
                            ]);
                            $createdAttendances->push($att);
                        }
                    }
                } else {
                    $productId = is_numeric($key) ? (int)$key : ($item['id'] ?? null);
                    InvoiceDetail::create([
                        'invoice_id'  => $invoice->id,
                        'product_id'  => $productId,
                        'description' => $item['name'] ?? 'Producto Sound Heritage',
                        'quantity'    => $qty,
                        'unit_price'  => $finalPrice,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            // 4. Pago
            $pmName = $this->payment_method;
            $paymentMethod = PaymentMethod::where('name', 'like', "%{$pmName}%")->first()
                ?? PaymentMethod::firstOrCreate(['name' => $pmName]);

            PaymentDetail::create([
                'invoice_id'        => $invoice->id,
                'payment_method_id' => $paymentMethod->id,
            ]);

            DB::commit();

            // Si el medio de pago es Mercado Pago, redirigir a Checkout Pro para cobrar
            if ($isMercadoPago) {
                $mpService = app(\App\Services\MercadoPagoService::class);
                $mpItems = [];
                foreach ($this->carrito as $item) {
                    $desc = (int) ($item['discount_percentage'] ?? 0);
                    $uPrice = (float) ($item['price'] ?? 0);
                    $fPrice = ($desc > 0) ? ($uPrice * (1 - ($desc / 100))) : $uPrice;
                    $mpItems[] = [
                        'title'      => $item['name'] ?? 'Producto Sound Heritage',
                        'quantity'   => (int) ($item['cantidad'] ?? 1),
                        'unit_price' => $fPrice,
                    ];
                }

                $pref = $mpService->createCheckoutPreference(
                    items: $mpItems,
                    payer: [
                        'name'    => $customer->first_name,
                        'surname' => $customer->last_name,
                        'email'   => $customer->email,
                    ],
                    externalReference: "INV-{$invoice->id}"
                );

                if ($pref) {
                    $redirectUrl = config('services.mercadopago.sandbox')
                        ? ($pref['sandbox_init_point'] ?? $pref['init_point'] ?? null)
                        : ($pref['init_point'] ?? null);

                    if ($redirectUrl) {
                        $this->redirect($redirectUrl, navigate: false);
                        return;
                    }
                }
            }

            // En pagos directos / mostrador (o ambiente de testing), confirmar orden inmediatamente
            try {
                $recipientEmail = !empty($cleanEmail) ? $cleanEmail : ($customer->email ?? null);
                if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    if ($this->hasPhysicalProducts) {
                        Mail::to($recipientEmail)->send(
                            new PurchaseConfirmationMail($invoice, $this->delivery_method, $createdAttendances)
                        );
                    } else {
                        Mail::to($recipientEmail)->send(
                            new EventTicketsMail($customer, $createdAttendances, $invoice)
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error enviando correo de confirmación de compra: ' . $e->getMessage());
            }

            session()->forget('carrito');
            $this->orderConfirmed = true;
            $this->confirmedInvoiceId = $invoice->id;
            $this->confirmedInvoice = $invoice->load(['customer', 'invoiceDetails.product', 'paymentDetails.paymentMethod']);
            $this->createdAttendanceIds = $createdAttendances->pluck('id')->toArray();

            $this->success(
                title: '¡Compra confirmada con éxito!',
                description: "Se generó tu factura #{$invoice->receipt_number} y te enviamos los comprobantes a tu correo.",
                position: 'toast-bottom toast-end',
                icon: 'o-check-circle',
                css: 'alert-success font-bold text-white',
                timeout: 6000
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en proceso de checkout: ' . $e->getMessage());
            $this->error(
                title: 'Error al procesar la compra',
                description: 'Ocurrió un problema al registrar la orden: ' . $e->getMessage(),
                position: 'toast-bottom toast-end',
                icon: 'o-exclamation-triangle'
            );
        }
    }

    public function descargarFacturaPdf($invoiceId)
    {
        $invoice = Invoice::with(['customer', 'invoiceDetails.product', 'paymentDetails.paymentMethod'])->findOrFail($invoiceId);
        $itemCount = max(1, $invoice->invoiceDetails->count());
        $height = 430 + ($itemCount * 38);

        $pdf = Pdf::loadView('reports.invoice-ticket-pdf', ['invoice' => $invoice])
            ->setPaper([0, 0, 226.77, $height], 'portrait');

        $filename = "ticket-FC-{$invoice->invoice_type}-{$invoice->point_of_sale}-{$invoice->receipt_number}.pdf";
        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function descargarEntradaPdf($attendanceId)
    {
        $attendance = Attendance::with(['customer', 'event.venue'])->findOrFail($attendanceId);
        $pdf = Pdf::loadView('reports.event-ticket-pdf', ['record' => $attendance])
            ->setPaper([0, 0, 226.77, 450], 'portrait');

        $filename = "Entrada-" . str_pad((string)$attendance->id, 6, '0', STR_PAD_LEFT) . ".pdf";
        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.checkout-tienda', [
            'carrito' => $this->carrito,
        ]);
    }
}

