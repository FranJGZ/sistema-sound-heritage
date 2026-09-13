<x-app-layout>
    <x-slot name="header">
        {{-- LÍNEA QUE PIDE EL PROFESOR ADAPTADA A BREEZE (Título a la izquierda, botón a la derecha) --}}
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📦 {{ __('Proveedores') }}
            </h2>
            <a href="{{ route('supplier.create') }}" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                Crear Proveedor
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="p-3 text-gray-700">ID</th>
                            <th class="p-3 text-gray-700">Empresa / Proveedor</th>
                            <th class="p-3 text-gray-700">Contacto</th>
                            <th class="p-3 text-gray-700">Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier as $item)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-3 text-gray-500 font-bold">#{{ $item->id }}</td>
                                <td class="p-3 font-semibold text-gray-800">{{ $item->nombre }}</td>
                                <td class="p-3 text-gray-600">{{ $item->contacto ?? 'Sin asignar' }}</td>
                                <td class="p-3 text-gray-600">{{ $item->telefono ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
