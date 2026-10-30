<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Iniciar Sesión</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">Ingresá tus credenciales para acceder a tu cuenta.</p>
    </div>

    <x-auth-session-status class="mb-4 text-xs font-semibold text-emerald-600 dark:text-emerald-400" :status="session('status')" />

    <!-- Botón de Google OAuth -->
    <div class="mb-5">
        <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 py-2.5 px-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 text-zinc-700 dark:text-zinc-200 text-xs font-bold uppercase tracking-wider shadow-xs transition-colors duration-150">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Continuar con Google</span>
        </a>
        <div class="mt-2.5 p-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed border border-zinc-200/80 dark:border-zinc-800 flex items-start gap-2">
            <x-mary-icon name="o-information-circle" class="w-4 h-4 text-[#D97724] shrink-0 mt-0.5" />
            <span><strong>¿Compraste en nuestro local?</strong> Iniciá sesión con el mismo correo que brindaste en mostrador para asociar tus facturas y reservas automáticamente.</span>
        </div>
    </div>

    <div class="relative flex py-2 items-center mb-4">
        <div class="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
        <span class="flex-shrink mx-3 text-[11px] uppercase tracking-wider font-bold text-zinc-400">o con tu correo</span>
        <div class="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPass: false }">
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
                autocomplete="username"
                class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#D97724] focus:border-transparent transition-all" 
                placeholder="ejemplo@correo.com" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-500 font-semibold" />
        </div>

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

        <!-- Recordarme & Olvidé contraseña -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-[#D97724] focus:ring-[#D97724] bg-zinc-50 dark:bg-zinc-900" 
                />
                <span class="text-xs text-zinc-600 dark:text-zinc-400 font-medium">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-[#D97724] hover:underline font-bold" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Botón Principal Ámbar -->
        <button 
            type="submit" 
            class="w-full py-3 px-4 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs shadow-md shadow-[#D97724]/20 transition-all duration-150 cursor-pointer mt-2"
        >
            Ingresar
        </button>

        <p class="text-center text-xs text-zinc-600 dark:text-zinc-400 pt-3 font-medium">
            ¿No tenés cuenta?
            <a href="{{ route('register') }}" class="text-[#D97724] font-bold hover:underline">Registrate acá</a>
        </p>
    </form>
</x-guest-layout>
