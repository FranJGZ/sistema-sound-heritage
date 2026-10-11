<x-guest-layout>
    <h2 class="text-2xl font-extrabold text-base-content mb-1">Confirmar Contraseña</h2>
    <p class="text-xs text-base-content/60 mb-6">
        Esta es un área segura del sistema. Por favor, confirmá tu contraseña antes de continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <!-- Contraseña con Ojito -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Contraseña</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm pr-10" placeholder="••••••••" />
                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content" title="Ver / Ocultar">
                    <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <button type="submit" class="btn btn-primary w-full font-bold uppercase text-xs mt-2">
            Confirmar
        </button>
    </form>
</x-guest-layout>