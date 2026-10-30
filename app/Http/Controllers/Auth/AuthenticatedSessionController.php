<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // Extrae y purga de la sesión cualquier URL previa guardada para evitar redirecciones cruzadas entre roles distintos
        $intended = $request->session()->pull('url.intended');

        // 1. El Administrador va a su panel (o a una sub-ruta válida dentro de /admin)
        if ($user->hasRole('Administrador')) {
            $target = ($intended && str_contains($intended, '/admin')) ? $intended : '/admin';
            return redirect($target);
        }

        // 2. El Encargado de Stock va exclusivamente a su panel de stock
        if ($user->hasRole('Encargado de Stock')) {
            $target = ($intended && str_contains($intended, '/stock')) ? $intended : '/stock';
            return redirect($target);
        }

        // 3. El Vendedor va exclusivamente a su panel de ventas
        if ($user->hasRole('Vendedor')) {
            $target = ($intended && str_contains($intended, '/vendedor')) ? $intended : '/vendedor';
            return redirect($target);
        }

        // 4. El Cliente (o cualquier otro) va a la tienda o su perfil, descartando rutas administrativas
        $target = ($intended && !str_contains($intended, '/admin') && !str_contains($intended, '/stock') && !str_contains($intended, '/vendedor'))
            ? $intended
            : '/';

        return redirect($target);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}