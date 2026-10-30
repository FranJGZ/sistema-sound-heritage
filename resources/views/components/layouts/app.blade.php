<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: localStorage.getItem('theme') || 'light' }" 
      :class="theme === 'dark' ? 'dark' : ''"
      :data-theme="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Sound Heritage - Instrumentos Musicales & Audio Pro' }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaNegro.png') }}" media="(prefers-color-scheme: light)">
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaBlanco.png') }}" media="(prefers-color-scheme: dark)">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <!-- Estilos base de Livewire -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] antialiased font-sans text-[#181715] dark:text-[#F3EFE6] transition-colors duration-200 flex flex-col justify-between">
        
        <!-- El receptor de los carteles flotantes de MaryUI -->
        <x-toast /> 

        <!-- ENCABEZADO CANÓNICO PERMANENTE (Visible en el 100% de las vistas) -->
        <x-storefront.navbar />

        <main class="w-full flex-1">
            {{ $slot }}
        </main>

        <!-- PIE DE PÁGINA CANÓNICO (Visible en el 100% de las vistas) -->
        <x-storefront.footer />

        <!-- Scripts obligatorios para que los eventos AJAX funcionen en pantalla -->
        @livewireScripts
    </body>
</html>
