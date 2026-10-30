<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Confirmar Contraseña</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">
            Esta es un área protegida del sistema. Por favor, confirmá tu contraseña antes de continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <!-- Contraseña con Ojito -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Contraseña</label>
            <div class="relative">
                <input 
                    id="password" 
                    :type="showPass ? 'text' : 'password'" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all pr-10" 
                    placeholder="••••••••" 
                />
                <button 
                    type="button" 
                    @click="showPass = !showPass" 
                    class="absolute right-3 top-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors" 
                    title="Ver / Ocultar"
                >
                    <x-mary-icon x-show="!showPass" name="o-eye" class="w-5 h-5" />
                    <x-mary-icon x-show="showPass" name="o-eye-slash" class="w-5 h-5" style="display: none;" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-500 font-semibold" />
        </div>

        <button 
            type="submit" 
            class="w-full py-3 px-4 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs shadow-md shadow-[#D97724]/20 transition-all duration-150 cursor-pointer mt-2"
        >
            Confirmar
        </button>
    </form>
</x-guest-layout>
