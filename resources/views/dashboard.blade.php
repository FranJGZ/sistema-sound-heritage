<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-sound-blue uppercase tracking-widest">
                {{ __('Panel de Control') }}
            </h2>
            <div class="flex items-center space-x-6">
                <div class="hidden md:flex items-center border-l border-sound-gold/30 pl-6">
                    <img src="{{ asset('img/Logo_SH.png') }}" alt="Sound Heritage" style="max-height: 100px; width: auto;" class="object-contain">
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensaje de Bienvenida Destacado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-t-sound-gold mb-8">
                <div class="p-6 bg-sound-blue">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white">Bienvenido al sistema de gestión corporativo</h3>
                            <p class="text-sm text-gray-300 mt-2">Selecciona uno de los módulos a continuación para administrar los recursos de la tienda de música.</p>
                        </div>
                        <div class="hidden md:flex h-12 w-12 rounded-full border-2 border-sound-gold items-center justify-center opacity-80">
                            <svg class="w-6 h-6 text-sound-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuadrícula de Módulos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Módulo: Proveedores -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:shadow-lg transition-shadow duration-300 group">
                    <div>
                        <div class="w-12 h-12 bg-sound-gold/10 rounded-lg flex items-center justify-center text-sound-gold mb-4 border border-sound-gold/30 group-hover:bg-sound-gold/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-sound-blue mb-2">Proveedores</h4>
                        <p class="text-sm text-gray-600 mb-6">
                            Gestión integral de fabricantes, sellos discográficos y distribuidores autorizados.
                        </p>
                    </div>
                    <a href="{{ route('supplier.index') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-sound-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-opacity-90 focus:ring-2 focus:ring-sound-gold focus:ring-offset-2 transition ease-in-out duration-150">
                        Ingresar al Módulo
                    </a>
                </div>

                <!-- Módulo: Catálogo -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col justify-between opacity-60 cursor-not-allowed">
                    <div>
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mb-4 border border-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-500 mb-2">Catálogo & Stock</h4>
                        <p class="text-sm text-gray-500 mb-6">
                            Control de instrumentos, accesorios y vinilos en depósito.
                        </p>
                    </div>
                    <button disabled class="inline-flex items-center justify-center w-full px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-500 uppercase tracking-widest cursor-not-allowed">
                        Próximamente
                    </button>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
