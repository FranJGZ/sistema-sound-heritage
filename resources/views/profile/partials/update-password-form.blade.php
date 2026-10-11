<section>
    <header>
        <h2 class="text-lg font-bold text-base-content">
            Actualizar Contraseña
        </h2>

        <p class="mt-1 text-xs text-base-content/70">
            Asegurate de que tu cuenta utilice una contraseña segura para proteger tus datos.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5"
          x-data="{
              password: '',
              showCurrent: false,
              showNew: false,
              showConfirm: false,
              get hasMin() { return this.password.length >= 8; },
              get hasLetter() { return /[a-zA-Z]/.test(this.password); },
              get hasNumber() { return /[0-9]/.test(this.password); }
          }">
        @csrf
        @method('put')

        <!-- Contraseña Actual -->
        <div>
            <label for="update_password_current_password" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Contraseña Actual</label>
            <div class="relative">
                <input id="update_password_current_password" :type="showCurrent ? 'text' : 'password'" name="current_password" required autocomplete="current-password"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm pr-10" placeholder="••••••••" />
                <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content" title="Ver / Ocultar">
                    <svg x-show="!showCurrent" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showCurrent" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <!-- Nueva Contraseña -->
        <div>
            <label for="update_password_password" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Nueva Contraseña</label>
            <div class="relative">
                <input id="update_password_password" :type="showNew ? 'text' : 'password'" name="password" required autocomplete="new-password"
                       x-model="password"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm pr-10" placeholder="••••••••" />
                <button type="button" @click="showNew = !showNew" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content" title="Ver / Ocultar">
                    <svg x-show="!showNew" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showNew" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />

            <!-- Checklist en vivo -->
            <div class="mt-2 p-2.5 bg-base-200/60 rounded-xl text-xs space-y-1 border border-base-300">
                <div class="font-bold text-[11px] text-base-content/70">Requisitos de la contraseña:</div>
                <div :class="hasMin ? 'text-success font-semibold' : 'text-base-content/50'" class="flex items-center gap-1.5 transition-colors">
                    <span x-text="hasMin ? '✓' : '•'"></span> Mínimo 8 caracteres
                </div>
                <div :class="hasLetter ? 'text-success font-semibold' : 'text-base-content/50'" class="flex items-center gap-1.5 transition-colors">
                    <span x-text="hasLetter ? '✓' : '•'"></span> Al menos una letra
                </div>
                <div :class="hasNumber ? 'text-success font-semibold' : 'text-base-content/50'" class="flex items-center gap-1.5 transition-colors">
                    <span x-text="hasNumber ? '✓' : '•'"></span> Al menos un número
                </div>
            </div>
        </div>

        <!-- Confirmar Nueva Contraseña -->
        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Confirmar Nueva Contraseña</label>
            <div class="relative">
                <input id="update_password_password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm pr-10" placeholder="••••••••" />
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content" title="Ver / Ocultar">
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn btn-primary font-bold uppercase text-xs">
                Guardar Contraseña
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-xs text-success font-semibold"
                >Contraseña actualizada correctamente.</p>
            @endif
        </div>
    </form>
</section>
