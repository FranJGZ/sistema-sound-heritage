<x-guest-layout>
    <h2 class="text-2xl font-extrabold text-base-content mb-1">Nueva Contraseña</h2>
    <p class="text-xs text-base-content/60 mb-6">Ingresá y confirmá tu nueva contraseña de acceso.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4"
          x-data="{
              password: '',
              showPass: false,
              showConfirm: false,
              get hasMin() { return this.password.length >= 8; },
              get hasLetter() { return /[a-zA-Z]/.test(this.password); },
              get hasNumber() { return /[0-9]/.test(this.password); }
          }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Correo Electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Nueva Contraseña con Ojito -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase text-base-content/80 mb-1">Nueva Contraseña</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                       x-model="password"
                       class="input input-bordered w-full bg-base-200 focus:bg-base-100 text-sm pr-10" placeholder="••••••••" />
                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content">
                    <x-mary-icon x-show="!showPass" name="o-eye" class="w-5 h-5" />
                    <x-mary-icon x-show="showPass" name="o-eye-slash" class="w-5 h-5" style="display: none;" />
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
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-base-content/50 hover:text-base-content">
                    <x-mary-icon x-show="!showConfirm" name="o-eye" class="w-5 h-5" />
                    <x-mary-icon x-show="showConfirm" name="o-eye-slash" class="w-5 h-5" style="display: none;" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="btn btn-primary w-full font-bold uppercase text-xs mt-2">
            Guardar nueva contraseña
        </button>
    </form>
</x-guest-layout>