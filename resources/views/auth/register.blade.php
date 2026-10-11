<x-guest-layout>
    <h2 class="text-2xl font-extrabold text-base-content mb-1">Crear Cuenta</h2>
    <p class="text-xs text-base-content/60 mb-6">Completá tus datos para registrarte en Sound Heritage.</p>

    <!-- Botón de Google OAuth -->
    <div class="mb-5">
        <a href="{{ route('auth.google') }}" class="btn btn-outline w-full flex items-center justify-center gap-3 border-base-300 hover:border-base-content/20 hover:bg-base-200 text-base-content text-xs font-bold uppercase tracking-wider shadow-sm transition-all">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            Registrarse con Google
        </a>
        <div class="mt-2.5 p-2.5 bg-base-200/60 rounded-xl text-[11px] text-center text-base-content/70 leading-relaxed border border-base-300/50">
            💡 <strong>¿Compraste en nuestro local?</strong> Registrate con la misma cuenta de Google o correo que diste en el mostrador para vincular tus compras.
        </div>
    </div>

    <div class="divider text-xs text-base-content/40 uppercase mb-5">o con tu correo</div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4"

          x-data="{
              password: '',
              showPass: false,
              showConfirm: false,
              get hasMin() { return this.password.length >= 8; },
              get hasLetter() { return /[a-zA-Z]/.test(this.password); },
              get hasNumber() { return /[0-9]/.test(this.password); }
          }">
        @csrf

        <!-- Nombre -->
        <div>
            <label for="name" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Nombre y Apellido</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" placeholder="Ej: Juan Pérez" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Correo Electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" placeholder="ejemplo@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- DNI y Teléfono de Contacto (Opcionales para agilizar compras) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="document_number" class="block text-xs font-bold uppercase text-base-content/80 mb-1">DNI (Opcional)</label>
                <input id="document_number" type="text" name="document_number" value="{{ old('document_number') }}"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" placeholder="Ej: 35123456" />
                <x-input-error :messages="$errors->get('document_number')" class="mt-1" />
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Teléfono (Opcional)</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" placeholder="Ej: 3764123456" />
                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
            </div>
        </div>

        <!-- Contraseña con Ojito -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Contraseña</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                       x-model="password"
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

            <!-- Checklist de requisitos en vivo -->
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

        <!-- Confirmar Contraseña con Ojito -->
        <div>
            <label for="password_confirmation" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Confirmar Contraseña</label>
            <div class="relative">
                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
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
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="btn btn-primary w-full font-bold uppercase text-xs mt-2">
            Registrarme
        </button>

        <p class="text-center text-xs text-base-content/70 pt-2">
            ¿Ya estás registrado?
            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Iniciá sesión</a>
        </p>
    </form>
</x-guest-layout>