<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Sound Heritage | ERP') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

        <!-- Header / Navegación -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

                <!-- Logo y Marca -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/Logo_SH.png') }}" alt="Sound Heritage Logo" style="max-height: 100px; width: auto;" class="object-contain">
                    <span class="font-extrabold text-xl text-sound-blue tracking-widest uppercase border-l-2 border-sound-gold pl-3 ml-1 hidden sm:block">
                        Admin
                    </span>
                </div>

                <!-- Enlaces de Autenticación -->
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-sound-blue hover:text-sound-gold transition-colors">
                                Ir al Panel de Control
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold bg-sound-gold text-gray-800 px-5 py-2.5 rounded-md shadow-sm hover:bg-[#9a7645] focus:ring-2 focus:ring-sound-blue transition-all uppercase tracking-wider">
                                Ingresar
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm font-bold bg-sound-gold text-gray-800 px-5 py-2.5 rounded-md shadow-sm hover:bg-[#9a7645] focus:ring-2 focus:ring-sound-blue transition-all uppercase tracking-wider">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Contenido Principal (Hero Section) -->
        <main class="flex-grow flex items-center justify-center p-6">
            <div class="max-w-5xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden border-t-4 border-t-sound-gold">
                <div class="p-8 sm:p-16 text-center">

                    <h1 class="text-4xl sm:text-5xl font-extrabold text-sound-blue tracking-tight mb-4">
                        Gestión Corporativa <br class="sm:hidden">
                        <span class="text-sound-gold">Sound Heritage</span>
                    </h1>

                    <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">
                        Plataforma centralizada para la administración integral del catálogo de instrumentos, control de stock y directorio de proveedores musicales.
                    </p>

                    <!-- Tarjetas de Características -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left mt-8">

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md hover:border-sound-gold/30 transition-all group">
                            <div class="w-12 h-12 bg-sound-blue/5 rounded-lg flex items-center justify-center text-sound-blue mb-4 group-hover:bg-sound-blue group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                            </div>
                            <h3 class="font-bold text-sound-blue text-lg">Directorio de Proveedores</h3>
                            <p class="text-sm text-gray-500 mt-2">Administración de fabricantes, discográficas y distribuidores autorizados.</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md hover:border-sound-gold/30 transition-all group">
                            <div class="w-12 h-12 bg-sound-gold/10 rounded-lg flex items-center justify-center text-sound-gold mb-4 group-hover:bg-sound-gold group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <h3 class="font-bold text-sound-blue text-lg">Catálogo & Stock</h3>
                            <p class="text-sm text-gray-500 mt-2">Control detallado de inventario, incluyendo instrumentos de cuerda, percusión y accesorios.</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md hover:border-sound-gold/30 transition-all group">
                            <div class="w-12 h-12 bg-sound-blue/5 rounded-lg flex items-center justify-center text-sound-blue mb-4 group-hover:bg-sound-blue group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="font-bold text-sound-blue text-lg">Control de Accesos</h3>
                            <p class="text-sm text-gray-500 mt-2">Seguridad avanzada para proteger la información comercial y la trazabilidad del sistema.</p>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between text-sm text-gray-500 gap-4">
                <div>
                    &copy; {{ date('Y') }} <strong>Sound Heritage</strong>. Todos los derechos reservados.
                </div>
                <div class="flex space-x-4">
                    <span class="text-gray-400">Sistema Interno de Gestión v1.0</span>
                </div>
            </div>
        </footer>

    </body>
</html>
