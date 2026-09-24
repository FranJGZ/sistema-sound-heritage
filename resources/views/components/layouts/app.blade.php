<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: localStorage.getItem('theme') || 'light' }" 
      :data-theme="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Tienda Online' }}</title>
        
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
