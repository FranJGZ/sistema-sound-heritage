<x-app-layout>
    <x-slot name="header">
        {{-- LÍNEA QUE PIDE EL PROFESOR ADAPTADA A BREEZE (Título a la izquierda, botón a la derecha) --}}
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📦 Proveedores
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
                            <th class="p-3 text-gray-700">Dirección</th>
                            <th class="p-3 text-gray-700">Teléfono</th>
                            <th class="p-3 text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier as $item)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-3 text-gray-500 font-bold">#{{ $item->id }}</td>
                                <td class="p-3 font-semibold text-gray-800">{{ $item->name }}</td>
                                <td class="p-3 text-gray-600">{{ $item->address ?? 'No asignada' }}</td>
                                <td class="p-3 text-gray-600">{{ $item->phone ?? 'N/A' }}</td>
                                <td class="p-3">
                                    <form action="{{ route('supplier.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold transition duration-150 ease-in-out">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
