<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || 'light' }"
      :data-theme="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sound Heritage') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaNegro.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-200 antialiased font-sans text-base-content flex flex-col">

        <!-- 1. MISMO HEADER SUPERIOR STICKY DE LA TIENDA -->
        <header class="sticky top-0 z-50 w-full bg-base-100/90 backdrop-blur-md border-b border-base-300 shadow-sm transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

                <!-- PARTE A: LOGO Y MARCA -->
                <a href="/" class="flex items-center hover:opacity-90 transition-opacity shrink-0" style="display: flex; flex-direction: row; align-items: center; text-decoration: none;">
                    <div x-show="theme !== 'dark'">
                        <div style="display: flex; flex-direction: row; align-items: center; gap: 10px; flex-wrap: nowrap;">
                            <div style="height: 44px; width: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="{{ asset('img/LogoTiendaNegro.png') }}" alt="Sound Heritage Icono" style="height: 44px; width: 44px; object-fit: contain; display: block;" />
                            </div>
                            <img src="{{ asset('img/LetrasTiendaNegro.png') }}" alt="Sound Heritage" style="height: 24px; width: auto; object-fit: contain; display: block; flex-shrink: 0;" />
                        </div>
                    </div>

                    <div x-show="theme === 'dark'" style="display: none;">
                        <div style="display: flex; flex-direction: row; align-items: center; gap: 10px; flex-wrap: nowrap;">
                            <div style="height: 44px; width: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="{{ asset('img/LogoTiendaBlanco.png') }}" alt="Sound Heritage Icono" style="height: 92px; width: auto; max-width: none; object-fit: contain; transform: translate(1px, 11.5px); display: block; pointer-events: none;" />
                            </div>
                            <img src="{{ asset('img/LetrasTiendaBlanco.png') }}" alt="Sound Heritage" style="height: 24px; width: auto; object-fit: contain; display: block; flex-shrink: 0;" />
                        </div>
                    </div>
                </a>

                <!-- PARTE B: BARRA DE BÚSQUEDA CENTRAL -->
                <div class="flex-1 max-w-lg hidden md:block">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Buscar instrumentos, accesorios..."
                            class="input input-bordered input-sm w-full pl-10 bg-base-200 focus:bg-base-100 rounded-full text-sm"
                        />
                        <x-mary-icon name="o-magnifying-glass" class="w-4 h-4 text-base-content/50 absolute left-3.5 top-2.5" />
                    </div>
                </div>

                <!-- PARTE C: BOTONES DE ACCIÓN (TEMA + LINKS + CARRITO) -->
                <div class="flex items-center gap-2">
                    <button
                        @click="theme = (theme === 'light' ? 'dark' : 'light'); localStorage.setItem('theme', theme)"
                        class="btn btn-ghost btn-circle btn-sm"
                        title="Cambiar tema"
                        type="button"
                    >
                        <x-mary-icon x-show="theme === 'light'" name="o-moon" class="w-5 h-5 text-base-content" />
                        <x-mary-icon x-show="theme === 'dark'" name="o-sun" class="w-5 h-5 text-warning" />
                    </button>

                    <div class="hidden sm:flex items-center gap-3 text-xs font-semibold px-2">
                        <a href="{{ route('register') }}" class="text-base-content hover:text-primary transition-colors">
                            Registrate
                        </a>
                        <span class="text-base-content/30">|</span>
                        <a href="{{ route('login') }}" class="text-base-content hover:text-primary transition-colors">
                            Iniciá sesión
                        </a>
                    </div>

                    <a href="/" class="btn btn-ghost btn-circle btn-sm relative" title="Carrito">
                        <x-mary-icon name="o-shopping-cart" class="w-5 h-5 text-base-content" />
                        @if(collect(session('carrito', []))->sum('cantidad') > 0)
                            <span style="position: absolute; top: -2px; right: -2px; background-color: #dc2626; color: #ffffff; font-size: 10px; font-weight: 800; min-width: 17px; height: 17px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; padding: 0 4px; line-height: 1;">
                                {{ collect(session('carrito', []))->sum('cantidad') }}
                            </span>
                        @endif
                    </a>
                </div>

            </div>
        </header>

        <!-- 2. CONTENEDOR CENTRAL DEL FORMULARIO (LOGIN / REGISTRO) -->
        <main class="flex-grow flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-base-100 border border-base-300 shadow-xl rounded-2xl p-6 sm:p-8">
                {{ $slot }}
            </div>
        </main>

        @livewireScripts
    </body>
</html>