<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: localStorage.getItem('theme') || 'light' }" 
      :data-theme="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Tienda Online' }}</title>
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaNegro.png') }}" media="(prefers-color-scheme: light)">
        <link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaBlanco.png') }}" media="(prefers-color-scheme: dark)">

        <!-- Fonts -->
         <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <!-- Estilos base de Livewire -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-200 antialiased font-sans text-base-content">
        
        <!-- El receptor de los carteles flotantes de MaryUI -->
        <x-toast /> 

        <main class="w-full">
        {{ $slot }}
         </main>

        <!-- Scripts obligatorios para que los eventos AJAX funcionen en pantalla -->
        @livewireScripts
    </body>
</html>
