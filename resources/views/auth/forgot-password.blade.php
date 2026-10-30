<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Recuperar Contraseña</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">
            Ingresá tu correo electrónico y te enviaremos un enlace para restablecer tu clave de acceso.
        </p>
    </div>

    <x-auth-session-status class="mb-4 text-xs font-semibold text-emerald-600 dark:text-emerald-400" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Correo Electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Correo Electrónico</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus
                class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all" 
                placeholder="ejemplo@correo.com" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-500 font-semibold" />
        </div>

        <button 
            type="submit" 
            class="w-full py-3 px-4 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs shadow-md shadow-[#D97724]/20 transition-all duration-150 cursor-pointer mt-2"
        >
            Enviar enlace de recuperación
        </button>

        <p class="text-center text-xs text-zinc-600 dark:text-zinc-400 pt-3 font-medium">
            <a href="{{ route('login') }}" class="text-[#D97724] font-bold hover:underline">Volver a Iniciar Sesión</a>
        </p>
    </form>
</x-guest-layout>
