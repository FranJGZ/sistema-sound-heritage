<x-guest-layout>
    <!-- Encabezado del Formulario -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-sound-blue">
            Crear Cuenta
        </h2>
        <p class="text-sm text-gray-500 mt-2">
            Únete al sistema de gestión corporativo
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nombre Completo -->
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" class="text-sound-blue font-bold" />
            <x-text-input id="name" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Correo Electrónico -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-sound-blue font-bold" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" class="text-sound-blue font-bold" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Contraseña -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-sound-blue font-bold" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-sound-blue focus:ring-sound-blue"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-sound-blue rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sound-gold transition-colors" href="{{ route('login') }}">
                {{ __('¿Ya tienes una cuenta?') }}
            </a>

            <!-- Botón personalizado dorado -->
            <button type="submit" style="background-color: #0b1031;" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-sound-blue focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                {{ __('Registrarse') }}
            </button>
        </div>
    </form>
</x-guest-layout>
