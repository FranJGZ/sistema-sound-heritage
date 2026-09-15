<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">

            <div class="flex items-center space-x-3">
                <svg style="width: 28px; height: 28px;" class="text-sound-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                </svg>
                <h2 class="font-bold text-xl md:text-2xl text-sound-blue tracking-tight shrink-0">
                    {{ __('Directorio de Proveedores') }}
                </h2>
            </div>

            <!-- Botón Crear y Logo del Header -->
            <div class="flex items-center space-x-4 md:space-x-6">

                <!-- BOTÓN TOTALMENTE VISIBLE -->
                <a href="{{ route('supplier.create') }}" class="inline-flex items-center px-4 py-2 bg-sound-gold border border-transparent rounded-md font-bold text-xs text-blue uppercase tracking-widest hover:bg-[#9a7645] focus:ring-2 focus:ring-sound-blue focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                    Registrar Proveedor
                </a>

                <!-- Logo de Sound Heritage -->
                <div class="hidden md:flex items-center border-l border-sound-gold/30 pl-6">
                    <img src="{{ asset('img/Logo_SH.png') }}" alt="Sound Heritage" style="max-height: 100px; width: auto;" class="object-contain">
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Contenedor de la Tabla -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-t-sound-gold">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <!-- Cabecera de la tabla con color corporativo -->
                        <thead>
                            <tr class="bg-sound-blue text-white text-xs uppercase tracking-wider">
                                <th class="p-4 font-semibold">ID</th>
                                <th class="p-4 font-semibold">Empresa / Proveedor</th>
                                <th class="p-4 font-semibold">Dirección</th>
                                <th class="p-4 font-semibold">Teléfono</th>
                                <th class="p-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">

                            <!-- Bucle Forelse (maneja listas llenas y vacías) -->
                            @forelse ($supplier as $item)
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="p-4 text-gray-500 font-bold text-sm">
                                        #{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="p-4 font-bold text-sound-blue">
                                        {{ $item->name }}
                                    </td>
                                    <td class="p-4 text-gray-600 text-sm">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ $item->address ?? 'No asignada' }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-gray-600 text-sm">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $item->phone ?? 'N/A' }}
                                        </div>
                                    </td>
                                <td class="p-4 text-right space-x-2">
                                    <!-- Botón de Editar -->
                                    <a href="{{ route('supplier.edit', $item->id) }}" class="inline-flex items-center px-3 py-1 bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 rounded-md text-xs font-bold uppercase tracking-wider transition-colors">
                                        Editar
                                    </a>

                                    <!-- Botón de Eliminar -->
                                    <form action="{{ route('supplier.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Confirmas la eliminación de este proveedor del sistema?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-md text-xs font-bold uppercase tracking-wider transition-colors">
                                        Eliminar
                                        </button>
                                    </form>
                                </td>
                                </tr>
                            @empty
                                <!-- Estado Vacío: Se muestra si no hay proveedores -->
                                <tr>
                                    <td colspan="5" class="p-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                </svg>
                                            </div>
                                            <p class="text-lg font-bold text-sound-blue">Aún no hay proveedores registrados</p>
                                            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Comienza agregando el primer fabricante o distribuidor al catálogo de la tienda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
