<x-guest-layout>
    <!-- Estado de Sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Encabezado del Formulario -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-sound-blue">
            Iniciar Sesión
        </h2>
        <p class="text-sm text-gray-500 mt-2">
            Ingresa a tu panel de control de Sound Heritage
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo Electrónico -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-sound-blue font-bold" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" class="text-sound-blue font-bold" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Recuérdame -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-sound-blue shadow-sm focus:ring-sound-blue" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recuérdame') }}</span>
            </label>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-sound-blue rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sound-gold transition-colors" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <!-- Botón dorado personalizado -->
            <button type="submit" style="background-color: #0b1031;" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-sound-blue focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                {{ __('Ingresar') }}
            </button>
        </div>
    </form>
</x-guest-layout>
