<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Nueva Contraseña</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">Ingresá y confirmá tu nueva contraseña de acceso.</p>
    </div>

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
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Correo Electrónico</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus 
                autocomplete="username"
                class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-500 font-semibold" />
        </div>

        <!-- Nueva Contraseña con Ojito -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Nueva Contraseña</label>
            <div class="relative">
                <input 
                    id="password" 
                    :type="showPass ? 'text' : 'password'" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    x-model="password"
                    class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all pr-10" 
                    placeholder="••••••••" 
                />
                <button 
                    type="button" 
                    @click="showPass = !showPass" 
                    class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                >
                    <x-mary-icon x-show="!showPass" name="o-eye" class="w-5 h-5" />
                    <x-mary-icon x-show="showPass" name="o-eye-slash" class="w-5 h-5" style="display: none;" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-500 font-semibold" />

            <!-- Checklist de requisitos en vivo -->
            <div class="mt-2.5 p-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl text-xs space-y-1.5 border border-zinc-200/80 dark:border-zinc-800">
                <div class="font-bold text-[11px] text-zinc-500 uppercase tracking-wider">Requisitos mínimos:</div>
                <div :class="hasMin ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-zinc-400'" class="flex items-center gap-2 transition-colors">
                    <span x-text="hasMin ? '✓' : '•'"></span> Mínimo 8 caracteres
                </div>
                <div :class="hasLetter ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-zinc-400'" class="flex items-center gap-2 transition-colors">
                    <span x-text="hasLetter ? '✓' : '•'"></span> Al menos una letra
                </div>
                <div :class="hasNumber ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-zinc-400'" class="flex items-center gap-2 transition-colors">
                    <span x-text="hasNumber ? '✓' : '•'"></span> Al menos un número
                </div>
            </div>
        </div>

        <!-- Confirmar Contraseña con Ojito -->
        <div>
            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Confirmar Contraseña</label>
            <div class="relative">
                <input 
                    id="password_confirmation" 
                    :type="showConfirm ? 'text' : 'password'" 
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password"
                    class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all pr-10" 
                    placeholder="••••••••" 
                />
                <button 
                    type="button" 
                    @click="showConfirm = !showConfirm" 
                    class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                >
                    <x-mary-icon x-show="!showConfirm" name="o-eye" class="w-5 h-5" />
                    <x-mary-icon x-show="showConfirm" name="o-eye-slash" class="w-5 h-5" style="display: none;" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-rose-500 font-semibold" />
        </div>

        <button 
            type="submit" 
            class="w-full py-3 px-4 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs shadow-md shadow-[#D97724]/20 transition-all duration-150 cursor-pointer mt-2"
        >
            Restablecer Contraseña
        </button>
    </form>
</x-guest-layout>
