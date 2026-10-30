<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirige al proveedor de Google para iniciar el flujo de autenticación OAuth.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Procesa la respuesta de Google utilizando estrictamente la estructura existente de la base de datos.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo completar el inicio de sesión con Google. Por favor, intentá nuevamente.',
            ]);
        }

        // 1. Buscamos al usuario por su correo electrónico existente
        $user = User::withTrashed()
            ->where('email', $googleUser->getEmail())
            ->first();

        // 2. Si la cuenta está suspendida, bloqueamos el acceso
        if ($user && $user->trashed()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Estas credenciales no coinciden con nuestros registros.',
            ]);
        }

        // 3. Si ya existía, validamos su correo de inmediato
        if ($user) {
            if (! $user->email_verified_at) {
                $user->update([
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            // 4. Si es nuevo, lo creamos con los campos ya existentes en la tabla users
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Usuario Google',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('Cliente');
        }

        // Sincroniza y asegura la ficha de cliente asociada
        $user->syncProfileFromRoles();

        Auth::login($user, remember: true);

        // 5. Redirección según rol evitando rutas cruzadas cacheadas
        $intended = session()->pull('url.intended');

        if ($user->hasRole('Administrador')) {
            $target = ($intended && str_contains($intended, '/admin')) ? $intended : '/admin';
            return redirect($target);
        }

        if ($user->hasRole('Encargado de Stock')) {
            $target = ($intended && str_contains($intended, '/stock')) ? $intended : '/stock';
            return redirect($target);
        }

        if ($user->hasRole('Vendedor')) {
            $target = ($intended && str_contains($intended, '/vendedor')) ? $intended : '/vendedor';
            return redirect($target);
        }

        return redirect('/?google_logged_in=1');
    }
}
