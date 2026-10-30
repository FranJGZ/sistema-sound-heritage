<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || 'light' }"
      :data-theme="theme"
      :class="theme === 'dark' ? 'dark' : ''">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sound Heritage') }} - Acceso</title>
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaNegro.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased flex flex-col transition-colors duration-200">

        <!-- 1. HEADER CANÓNICO EN 2 FILAS SOUND HERITAGE -->
        <header class="sticky top-0 z-50 w-full bg-[#181715] text-white shadow-md">
            <!-- Fila 1: Logo, Acceso, Carrito y Tema -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                
                <!-- Logo Blanco Sound Heritage -->
                <a href="/" class="flex items-center gap-3 hover:opacity-90 transition-opacity shrink-0">
                    <div class="h-12 w-12 flex items-center justify-center shrink-0">
                        <img 
                            src="{{ asset('img/LogoTiendaBlanco.png') }}" 
                            alt="Sound Heritage Icono" 
                            style="height: 96px; width: auto; max-width: none; object-fit: contain; transform: translate(1px, 12px); display: block; pointer-events: none;" 
                        />
                    </div>
                    <img 
                        src="{{ asset('img/LetrasTiendaBlanco.png') }}" 
                        alt="Sound Heritage" 
                        class="h-6 w-auto object-contain block shrink-0" 
                    />
                </a>

                <!-- Acciones Rápidas -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Tema Claro / Oscuro -->
                    <button 
                        type="button" 
                        @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme)" 
                        class="btn btn-ghost btn-circle btn-sm text-zinc-300 hover:text-white hover:bg-white/10 transition-colors" 
                        title="Alternar Modo Día / Noche"
                    >
                        <span x-show="theme === 'dark'">
                            <x-mary-icon name="o-sun" class="w-4 h-4 text-[#D97724]" />
                        </span>
                        <span x-show="theme !== 'dark'">
                            <x-mary-icon name="o-moon" class="w-4 h-4 text-zinc-300" />
                        </span>
                    </button>

                    <div class="hidden sm:flex items-center gap-3 text-xs font-semibold px-2">
                        <a href="{{ route('register') }}" class="text-zinc-300 hover:text-[#D97724] transition-colors">
                            Registrate
                        </a>
                        <span class="text-zinc-600">|</span>
                        <a href="{{ route('login') }}" class="text-zinc-300 hover:text-[#D97724] transition-colors">
                            Iniciá sesión
                        </a>
                    </div>

                    <!-- Botón de Carrito -->
                    <a href="{{ route('checkout') }}" class="btn btn-ghost btn-circle btn-sm relative text-white hover:bg-white/10 transition-colors" title="Ver Carrito">
                        <x-mary-icon name="o-shopping-cart" class="w-5 h-5 text-white" />
                        @if(collect(session('carrito', []))->sum('cantidad') > 0)
                            <span class="absolute -top-1 -right-1 bg-[#D97724] text-white text-[10px] font-black min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1 shadow-md">
                                {{ collect(session('carrito', []))->sum('cantidad') }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Fila 2: Enlaces de Navegación -->
            <div class="border-t border-white/10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center justify-between text-xs font-semibold">
                    <nav class="flex items-center gap-7 tracking-wide text-zinc-300">
                        <a href="{{ route('productos') }}" class="hover:text-[#D97724] transition-colors">Productos</a>
                        <a href="{{ route('ofertas') }}" class="hover:text-[#D97724] transition-colors">Ofertas</a>
                        <a href="{{ route('eventos') }}" class="hover:text-[#D97724] transition-colors">Eventos</a>
                        <a href="{{ route('reservar-salon') }}" class="hover:text-[#D97724] transition-colors">Reservar Salón</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- 2. CONTENIDO PRINCIPAL CENTRADO -->
        <main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md bg-white dark:bg-[#201E1C] border border-zinc-200 dark:border-zinc-800 shadow-2xl rounded-3xl p-6 sm:p-8 transition-colors duration-200">
                {{ $slot }}
            </div>
        </main>

        <!-- 3. FOOTER SOUND HERITAGE -->
        <footer class="bg-[#181715] text-white border-t border-[#7E2B2D]/20 pt-8 pb-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-400">
                    <p>© {{ date('Y') }} Sound Heritage S.R.L. - Espacio Cultural &amp; Tienda de Instrumentos Musicales.</p>
                    <p>Showroom &amp; Auditorio: Calle Colón 1234, Posadas, Misiones.</p>
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
