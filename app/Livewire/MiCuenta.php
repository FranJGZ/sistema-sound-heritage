<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\City;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class MiCuenta extends Component
{
    public string $activeSection = 'dashboard'; // 'dashboard', 'reservas', 'facturas', 'entradas', 'domicilios', 'datos', 'seguridad'

    // Datos Personales Editables
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';

    // Datos Fiscales Bloqueados (Read-only por AFIP)
    public string $document_number = '';
    public string $tax_condition = '';
    public string $tax_id = '';

    // Domicilio de Entrega Editable
    public string $address = '';
    public ?int $city_id = null;

    // Seguridad / Cambio de Contraseña
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Mensajes de éxito locales
    public ?string $successMessage = null;
    public bool $showEditModal = false;

    public function openEditModal(): void
    {
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetErrorBag();
    }

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Obtener o vincular Customer
        $customer = $user->customer ?? Customer::where('email', $user->email)->first();
        if (!$customer) {
            $city = City::first();
            if (!$city) {
                $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
                $city = City::firstOrCreate(['name' => 'Posadas', 'province_id' => $prov->id]);
            }
            $parts = explode(' ', $user->name, 2);
            $customer = Customer::create([
                'user_id'         => $user->id,
                'email'           => $user->email,
                'first_name'      => $parts[0] ?? $user->name,
                'last_name'       => $parts[1] ?? 'Cliente',
                'document_number' => 'DNI-' . str_pad((string)$user->id, 8, '0', STR_PAD_LEFT),
                'address'         => 'Calle Colón 1234',
                'phone'           => '3764123456',
                'city_id'         => $city->id,
                'tax_condition'   => 'Consumidor Final',
            ]);
        } elseif (!$customer->user_id) {
            $customer->user_id = $user->id;
            $customer->save();
        }

        // Cargar propiedades
        $this->first_name = $customer->first_name ?? $user->name;
        $this->last_name = $customer->last_name ?? '';
        $this->email = $customer->email ?? $user->email;
        $this->phone = $customer->phone ?? '';
        $this->document_number = $customer->document_number ?? 'No registrado';
        $this->tax_condition = $customer->tax_condition ?? 'Consumidor Final';
        $this->tax_id = $customer->tax_id ?? '';
        $this->address = $customer->address ?? '';
        $this->city_id = $customer->city_id;

        $requestedSection = request()->query('section');
        if ($requestedSection && in_array($requestedSection, ['dashboard', 'reservas', 'facturas', 'entradas', 'domicilios', 'datos', 'seguridad'], true)) {
            $this->activeSection = $requestedSection;
        }
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
        $this->successMessage = null;
        $this->resetErrorBag();
    }

    public function reenviarVerificacionEmail(): void
    {
        $user = Auth::user();
        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $this->successMessage = 'Te enviamos un enlace de verificación a ' . $user->email . '.';
        }
    }

    public function actualizarDatos(): void
    {
        $this->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'nullable|string|max:100',
            'document_number' => [
                'required',
                'string',
                'min:7',
                'max:11',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    $existingCustomer = Customer::where('document_number', $value)->first();
                    if ($existingCustomer && $existingCustomer->user_id && $existingCustomer->user_id !== Auth::id()) {
                        $fail('Este número de DNI ya se encuentra registrado por otro usuario.');
                    }
                },
            ],
            'email'           => 'required|email|max:150|unique:users,email,' . Auth::id(),
            'phone'           => 'nullable|string|max:50',
        ], [
            'first_name.required'      => 'El nombre es obligatorio.',
            'document_number.required' => 'El DNI es obligatorio.',
            'document_number.regex'    => 'El DNI debe contener solo números.',
            'email.required'           => 'El correo electrónico es obligatorio.',
            'email.email'              => 'Ingresá un correo electrónico válido.',
            'email.unique'             => 'Este correo ya se encuentra en uso.',
        ]);

        $user = Auth::user();
        $user->name = trim($this->first_name . ' ' . $this->last_name);
        $user->email = $this->email;
        $user->save();

        $customer = $user->customer;
        if ($customer) {
            $customer->first_name = $this->first_name;
            $customer->last_name = $this->last_name;
            $customer->document_number = $this->document_number;
            $customer->email = $this->email;
            $customer->phone = $this->phone;
            $customer->save();
        }

        $this->showEditModal = false;
        $this->successMessage = 'Tus datos personales fueron actualizados correctamente.';
    }

    public function actualizarDomicilio(): void
    {
        $this->validate([
            'address' => 'required|string|max:200',
            'city_id' => 'nullable|exists:cities,id',
        ], [
            'address.required' => 'La dirección es obligatoria.',
        ]);

        $customer = Auth::user()->customer;
        if ($customer) {
            $customer->address = $this->address;
            $customer->city_id = $this->city_id;
            $customer->save();
        }

        $this->successMessage = 'Tu domicilio de entrega fue guardado con éxito.';
    }

    public function actualizarContrasena(): void
    {
        $this->validate([
            'current_password'          => 'required|current_password',
            'new_password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'new_password_confirmation' => 'required',
        ], [
            'current_password.required'         => 'Ingresá tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'new_password.required'             => 'Ingresá una nueva contraseña.',
            'new_password.confirmed'            => 'Las contraseñas no coinciden.',
            'new_password.min'                  => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->successMessage = 'Tu contraseña fue modificada exitosamente.';
    }

    public function descargarFacturaPdf(int $invoiceId)
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            abort(403);
        }

        $invoice = Invoice::with(['invoiceDetails.product', 'store', 'customer'])
            ->where('customer_id', $customer->id)
            ->findOrFail($invoiceId);

        $itemCount = $invoice->invoiceDetails->count();
        $height = 430 + ($itemCount * 38);

        $pdf = Pdf::loadView('reports.invoice-ticket-pdf', ['invoice' => $invoice])
            ->setPaper([0, 0, 226.77, $height], 'portrait');

        $filename = "ticket-FC-{$invoice->invoice_type}-{$invoice->point_of_sale}-{$invoice->receipt_number}.pdf";
        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function descargarEntradaPdf(int $attendanceId)
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            abort(403);
        }

        $attendance = Attendance::with(['customer', 'event.venue'])
            ->where('customer_id', $customer->id)
            ->findOrFail($attendanceId);

        $pdf = Pdf::loadView('reports.event-ticket-pdf', ['record' => $attendance])
            ->setPaper([0, 0, 226.77, 450], 'portrait');

        $filename = "Entrada-" . str_pad((string) $attendance->id, 6, '0', STR_PAD_LEFT) . ".pdf";
        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function getCustomerProperty()
    {
        return Auth::user()->customer;
    }

    public function getReservasProperty()
    {
        return $this->customer ? $this->customer->reservations()->with('venue')->latest()->get() : collect();
    }

    public function getFacturasProperty()
    {
        return $this->customer ? $this->customer->invoices()->with('invoiceDetails')->latest()->get() : collect();
    }

    public function getEntradasProperty()
    {
        return $this->customer ? $this->customer->attendances()->with(['event.venue'])->latest()->get() : collect();
    }

    public function render()
    {
        return view('livewire.mi-cuenta', [
            'ciudades'  => City::orderBy('name')->get(),
            'reservas'  => $this->reservas,
            'facturas'  => $this->facturas,
            'entradas'  => $this->entradas,
            'customer'  => $this->customer,
        ]);
    }
}
