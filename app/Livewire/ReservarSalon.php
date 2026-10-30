<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class ReservarSalon extends Component
{
    use Toast;

    // =========================================================================
    // 1. PROPIEDADES DE LA RESERVA (PARÁMETROS DEL SALÓN)
    // =========================================================================
    public string $type = 'Ensayo de Banda';
    public int $attendees_count = 4;
    public ?string $date = null;
    public string $shift = 'Tarde (15:00 a 19:00 hs)';
    public ?string $notes = null;

    // =========================================================================
    // 2. DATOS DEL TITULAR / CLIENTE
    // =========================================================================
    public string $first_name = '';
    public string $last_name = '';
    public string $document_number = '';
    public string $email = '';
    public string $phone = '';

    // =========================================================================
    // 3. MODALIDAD Y MEDIO DE PAGO (PASO 4: SEÑA 30% O TOTAL)
    // =========================================================================
    public string $payment_mode = 'seña'; // 'seña' (30%) o 'total' (100%)
    public string $payment_method = 'Mercado Pago'; // 'Mercado Pago', 'Efectivo en Mostrador'

    // Estado de éxito / visualización
    public bool $reservationConfirmed = false;
    public ?int $createdReservationId = null;
    public ?string $dateConflictError = null;

    protected function rules(): array
    {
        return [
            'type'            => 'required|string',
            'attendees_count' => 'required|integer|min:1|max:150',
            'date'            => 'required|date|after_or_equal:today',
            'shift'           => 'required|string',
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
            'notes'           => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'date.required'             => 'Tenés que seleccionar una fecha para la reserva.',
            'date.after_or_equal'       => 'La fecha debe ser igual o posterior a hoy.',
            'first_name.required'       => 'El nombre del titular es obligatorio.',
            'last_name.required'        => 'El apellido del titular es obligatorio.',
            'document_number.required'  => 'El DNI o CUIT es obligatorio.',
            'document_number.regex'     => 'El DNI o CUIT debe contener únicamente números.',
            'document_number.min'       => 'El DNI debe tener al menos 7 dígitos.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.email'               => 'Ingresá un correo electrónico válido.',
            'phone.required'            => 'El teléfono o WhatsApp de contacto es obligatorio.',
            'attendees_count.min'       => 'La cantidad mínima es 1 persona.',
            'attendees_count.max'       => 'La capacidad máxima del auditorio es de 150 personas.',
        ];
    }

    public function mount(): void
    {
        // Por defecto fecha a 7 días en adelante
        $this->date = Carbon::now()->addDays(7)->format('Y-m-d');

        // Precarga de datos del usuario autenticado si existe
        if (Auth::check()) {
            $user = Auth::user();
            $customer = Customer::where('email', $user->email)
                ->orWhere('user_id', $user->id)
                ->first();

            if ($customer) {
                $this->first_name = $customer->first_name ?: (explode(' ', $user->name, 2)[0] ?? '');
                $this->last_name = $customer->last_name ?: (explode(' ', $user->name, 2)[1] ?? '');
                $this->email = $customer->email ?: $user->email;
                if (!empty($customer->document_number) && !str_starts_with($customer->document_number, 'DNI-') && $customer->document_number !== '0') {
                    $this->document_number = $customer->document_number;
                }
                if (!empty($customer->phone)) {
                    $this->phone = $customer->phone;
                }
            } else {
                $parts = explode(' ', $user->name, 2);
                $this->first_name = $parts[0] ?? '';
                $this->last_name = $parts[1] ?? '';
                $this->email = $user->email;
            }
        }

        $this->validateDateAvailability();
    }

    public function updatedDate(): void
    {
        $this->validateDateAvailability();
    }

    public function updatedShift(): void
    {
        $this->validateDateAvailability();
    }

    public function updatedType(): void
    {
        // Reactividad en precio
    }

    public function updatedAttendeesCount(): void
    {
        if ($this->attendees_count < 1) {
            $this->attendees_count = 1;
        } elseif ($this->attendees_count > 150) {
            $this->attendees_count = 150;
        }
    }

    public function incrementarPersonas(): void
    {
        if ($this->attendees_count < 150) {
            $this->attendees_count++;
        }
    }

    public function decrementarPersonas(): void
    {
        if ($this->attendees_count > 1) {
            $this->attendees_count--;
        }
    }

    // =========================================================================
    // 3. TARIFAS Y DESCUENTOS GRUPALES POR TRAMOS DE 5 (OPCIÓN 1)
    // =========================================================================
    public function getTarifasBaseProperty(): array
    {
        return [
            'Ensayo de Banda' => [
                'name'        => 'Ensayo de Banda',
                'description' => 'Uso de sala acústica con backline completo (amplificadores valvulares, batería acústica y microfonía)',
                'price'       => 35000.0,
            ],
            'Ensayo General' => [
                'name'        => 'Ensayo General / Puesta en Escena',
                'description' => 'Ensayo con consola digital, monitoreo in-ear, prueba integral de escenario y luces',
                'price'       => 50000.0,
            ],
            'Grabación Acústica' => [
                'name'        => 'Grabación Acústica',
                'description' => 'Sesión en sala tratada con par estéreo de condensador, ruteo a control room y monitoreo',
                'price'       => 70000.0,
            ],
            'Lanzamiento de Videoclip' => [
                'name'        => 'Lanzamiento de Videoclip / Rodaje',
                'description' => 'Uso integral del escenario con parrilla de iluminación, backline y soporte técnico de sala',
                'price'       => 85000.0,
            ],
            'Masterclass Externa' => [
                'name'        => 'Masterclass / Taller Externo',
                'description' => 'Auditorio dispuesto con sonido, proyector, micrófonos inalámbricos y sillas para asistentes',
                'price'       => 60000.0,
            ],
            'Evento Privado' => [
                'name'        => 'Evento Privado / Concierto Exclusivo',
                'description' => 'Alquiler del auditorio a puertas cerradas con operador de sonido y personal de sala',
                'price'       => 110000.0,
            ],
        ];
    }

    public function getBasePriceProperty(): float
    {
        return (float) ($this->tarifasBase[$this->type]['price'] ?? 35000.0);
    }

    public function getDiscountPercentProperty(): int
    {
        $qty = $this->attendees_count;

        if ($qty >= 20) {
            return 25;
        } elseif ($qty >= 15) {
            return 20;
        } elseif ($qty >= 10) {
            return 15;
        } elseif ($qty >= 5) {
            return 10;
        }

        return 0;
    }

    public function getDiscountAmountProperty(): float
    {
        return (float) ($this->basePrice * ($this->discountPercent / 100));
    }

    public function getTotalPriceProperty(): float
    {
        return (float) max(0, $this->basePrice - $this->discountAmount);
    }

    public function getDepositAmountProperty(): float
    {
        return (float) round($this->totalPrice * 0.30, 2);
    }

    public function getAmountToPayNowProperty(): float
    {
        return $this->payment_mode === 'total' ? $this->totalPrice : $this->depositAmount;
    }

    public function getBalanceDueProperty(): float
    {
        return $this->payment_mode === 'total' ? 0.0 : (float) max(0, $this->totalPrice - $this->depositAmount);
    }

    // Mensaje informativo sobrio cuando falta 1 persona para desbloquear el siguiente tramo
    public function getNextTierNoticeProperty(): ?string
    {
        $qty = $this->attendees_count;

        if ($qty === 4) {
            return 'Sumando 1 persona más alcanzás el bloque de 5 y obtenés un 10% de descuento grupal en el total.';
        } elseif ($qty === 9) {
            return 'Sumando 1 persona más alcanzás el bloque de 10 y obtenés un 15% de descuento grupal en el total.';
        } elseif ($qty === 14) {
            return 'Sumando 1 persona más alcanzás el bloque de 15 y obtenés un 20% de descuento grupal en el total.';
        } elseif ($qty === 19) {
            return 'Sumando 1 persona más alcanzás el bloque de 20 y obtenés el beneficio máximo de 25% de descuento grupal.';
        }

        return null;
    }

    // =========================================================================
    // 4. VALIDACIÓN DE DISPONIBILIDAD (EVITA CHOQUES CON RECITALES Y RESERVAS)
    // =========================================================================
    public function validateDateAvailability(): void
    {
        $this->dateConflictError = null;

        if (empty($this->date)) {
            return;
        }

        $venueId = 1; // Auditorio Principal

        // Choque con eventos propios de Sound Heritage
        $hasOwnEvent = Event::where('venue_id', $venueId)
            ->whereDate('date', $this->date)
            ->exists();

        if ($hasOwnEvent) {
            $this->dateConflictError = 'El salón ya se encuentra ocupado por un Recital o Evento propio de Sound Heritage en esa fecha. Por favor elegí otro día.';
            return;
        }

        // Choque con reserva activa o pendiente en la misma fecha y turno
        $hasReservation = Reservation::where('venue_id', $venueId)
            ->whereDate('date', $this->date)
            ->where('shift', $this->shift)
            ->where('status', '!=', 'Cancelada')
            ->exists();

        if ($hasReservation) {
            $this->dateConflictError = "El salón ya cuenta con una reserva activa para esa fecha en el turno {$this->shift}. Por favor seleccioná otro turno o fecha disponible.";
        }
    }

    // =========================================================================
    // 5. CONFIRMACIÓN Y REGISTRO DE LA RESERVA EN ERP
    // =========================================================================
    public function confirmarReserva(): void
    {
        $this->validate();
        $this->validateDateAvailability();

        if (!empty($this->dateConflictError)) {
            $this->error(
                title: 'Fecha no disponible',
                description: $this->dateConflictError,
                position: 'toast-bottom toast-end',
                icon: 'o-exclamation-triangle',
                timeout: 5000
            );
            return;
        }

        // Buscar o crear cliente
        $cleanDoc = trim($this->document_number);
        $cleanEmail = trim($this->email);

        $customer = Customer::where('document_number', $cleanDoc)->first();
        if (!$customer) {
            $customer = Customer::where('email', $cleanEmail)->first();
        }

        $defaultCityId = \App\Models\City::value('id') ?? 1;

        if (!$customer) {
            $customer = Customer::create([
                'first_name'      => trim($this->first_name),
                'last_name'       => trim($this->last_name),
                'document_number' => $cleanDoc,
                'email'           => $cleanEmail,
                'phone'           => trim($this->phone),
                'address'         => 'Registrado desde Storefront Web',
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

        // Registrar la solicitud de reserva en estado Pendiente
        $depositRecorded = ($this->payment_mode === 'total') ? $this->totalPrice : $this->depositAmount;
        $paymentStatus = ($this->payment_method === 'Mercado Pago')
            ? 'pending'
            : (($this->payment_mode === 'total') ? 'Pendiente de Pago Total' : 'Pendiente de Seña');

        $reservation = Reservation::create([
            'type'            => $this->type,
            'date'            => $this->date,
            'shift'           => $this->shift,
            'price'           => $this->totalPrice,
            'deposit_amount'  => $depositRecorded,
            'payment_method'  => $this->payment_method,
            'payment_status'  => $paymentStatus,
            'attendees_count' => $this->attendees_count,
            'status'          => 'Pendiente',
            'customer_id'     => $customer->id,
            'venue_id'        => 1,
            'notes'           => $this->notes,
        ]);

        $this->createdReservationId = $reservation->id;
        $this->reservationConfirmed = true;

        // Disparar correo al cliente informando la recepción de la reserva y los datos de pago
        try {
            if (!empty($customer->email)) {
                \Illuminate\Support\Facades\Mail::to($customer->email)
                    ->send(new \App\Mail\ReservationPendingMail($reservation));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando mail de reserva pendiente: ' . $e->getMessage());
        }

        // Si el medio de pago es Mercado Pago, generar Checkout Pro y redirigir a abonar seña o total
        if ($this->payment_method === 'Mercado Pago') {
            try {
                $mpService = app(\App\Services\MercadoPagoService::class);
                $amountToPay = ($this->payment_mode === 'total') ? $this->totalPrice : $this->depositAmount;
                $extRef = ($this->payment_mode === 'total') ? "RES-{$reservation->id}-TOTAL" : "RES-{$reservation->id}-SENA";

                $pref = $mpService->createCheckoutPreference(
                    items: [
                        [
                            'title'       => "Reserva Auditorio Sound Heritage - {$this->type} (" . ($this->payment_mode === 'total' ? 'Pago Total' : 'Seña 30%') . ")",
                            'quantity'    => 1,
                            'unit_price'  => (float) $amountToPay,
                            'description' => "Turno: {$this->shift} para {$this->attendees_count} personas. Fecha: {$this->date}",
                        ]
                    ],
                    payer: [
                        'name'    => $customer->first_name,
                        'surname' => $customer->last_name,
                        'email'   => $customer->email,
                    ],
                    externalReference: $extRef
                );

                if ($pref) {
                    if (!empty($pref['id'])) {
                        $reservation->update(['mp_order_id' => $pref['id']]);
                    }

                    $redirectUrl = config('services.mercadopago.sandbox')
                        ? ($pref['sandbox_init_point'] ?? $pref['init_point'] ?? null)
                        : ($pref['init_point'] ?? null);

                    if ($redirectUrl) {
                        $this->redirect($redirectUrl, navigate: false);
                        return;
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error generando Checkout Pro para reserva: ' . $e->getMessage());
            }
        }

        $this->success(
            title: '¡Solicitud enviada con éxito!',
            description: "Tu reserva #{$reservation->id} quedó registrada en estado Pendiente. Te enviamos los detalles al correo.",
            position: 'toast-bottom toast-end',
            icon: 'o-check-circle',
            css: 'alert-success font-bold text-white',
            timeout: 6000
        );
    }

    public function reiniciarFormulario(): void
    {
        $this->reservationConfirmed = false;
        $this->createdReservationId = null;
        $this->notes = null;
        $this->payment_mode = 'seña';
        $this->payment_method = 'Mercado Pago';
        $this->date = Carbon::now()->addDays(7)->format('Y-m-d');
        $this->validateDateAvailability();
    }

    public function render()
    {
        return view('livewire.reservar-salon', [
            'tarifas' => $this->tarifasBase,
            'venue'   => Venue::find(1) ?? (object) [
                'name'     => 'Auditorio Sound Heritage',
                'location' => 'Calle Colón 1234, Posadas, Misiones',
                'capacity' => 150,
            ],
        ]);
    }
}