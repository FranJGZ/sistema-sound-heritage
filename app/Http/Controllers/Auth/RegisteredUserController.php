<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', app()->environment('testing') ? 'email' : 'email:rfc,dns', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'document_number' => [
                'nullable',
                'string',
                'min:7',
                'max:11',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    $existing = \App\Models\Customer::where('document_number', $value)->whereNotNull('user_id')->first();
                    if ($existing) {
                        $fail('El DNI ingresado ya pertenece a una cuenta registrada. Iniciá sesión para continuar.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('Cliente');

        // Sincroniza y crea inmediatamente la ficha de cliente asociada (o vincula si compró previamente como invitado)
        $user->syncProfileFromRoles([
            'document_number' => $request->document_number,
            'phone'           => $request->phone,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\WelcomeUserMail($user, null, 'web')
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de bienvenida en registro web: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
