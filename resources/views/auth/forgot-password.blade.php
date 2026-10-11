<x-guest-layout>
    <h2 class="text-2xl font-extrabold text-base-content mb-1">Recuperar Contraseña</h2>
    <p class="text-xs text-base-content/60 mb-6">
        Ingresá tu correo electrónico y te enviaremos un enlace para elegir una contraseña nueva.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Correo Electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" placeholder="ejemplo@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <button type="submit" class="btn btn-primary w-full font-bold uppercase text-xs mt-2">
            Enviar enlace de recuperación
        </button>

        <p class="text-center text-xs text-base-content/70 pt-2">
            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Volver a Iniciar Sesión</a>
        </p>
    </form>
</x-guest-layout>