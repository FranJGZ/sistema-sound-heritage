<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">

            <!-- Título con Ícono de "Editar/Lápiz" -->
            <div class="flex items-center space-x-3">
                <svg style="width: 28px; height: 28px;" class="text-sound-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <h2 class="font-bold text-xl md:text-2xl text-sound-blue tracking-tight shrink-0">
                    {{ __('Gestión de Proveedores') }}
                </h2>
            </div>

            <!-- Logo del Header -->
            <div class="flex items-center space-x-6">
                <div class="hidden md:flex items-center border-l border-sound-gold/30 pl-6">
                    <img src="{{ asset('img/Logo_SH.png') }}" alt="Sound Heritage" style="max-height: 100px; width: auto;" class="object-contain">
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="md:grid md:grid-cols-3 md:gap-8">

                <!-- Columna Izquierda: Información de contexto -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-bold leading-6 text-sound-blue">
                            Editar Proveedor
                        </h3>
                        <p class="mt-3 text-sm text-gray-600">
                            Modifica los datos comerciales de <strong>{{ $supplier->name }}</strong>.
                        </p>

                        <div class="mt-6 text-xs text-sound-blue bg-sound-blue/5 p-4 rounded-md border border-sound-blue/10 shadow-sm">
                            <strong class="text-sound-gold block mb-1 text-sm">Actualización:</strong>
                            Mantener esta información al día es vital para evitar retrasos en la reposición de stock.
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Formulario -->
                <div class="mt-6 md:mt-0 md:col-span-2">
                    <div class="shadow-lg overflow-hidden sm:rounded-md border-t-4 border-t-sound-gold bg-white">

                        <!-- IMPORTANTE: El action ahora apunta a 'update' y recibe el ID -->
                        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST" onsubmit="let btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = 'Actualizando...'; btn.classList.add('opacity-50', 'cursor-not-allowed');">
                            @csrf
                            <!-- IMPORTANTE: Directiva para indicar que es una petición de actualización -->
                            @method('PUT')

                            <div class="px-4 py-6 sm:p-8">
                                <div class="grid grid-cols-6 gap-6">

                                    <!-- Nombre -->
                                    <div class="col-span-6">
                                        <label for="name" class="block text-sm font-bold text-sound-blue">Razón Social / Nombre del Proveedor</label>
                                        <div class="mt-2 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                                <svg class="h-5 w-5 text-sound-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                            </span>
                                            <!-- old('name', $supplier->name) carga el dato existente -->
                                            <input type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}" required
                                                class="flex-1 focus:ring-sound-blue focus:border-sound-blue block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 transition-colors">
                                        </div>
                                        @error('name') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Dirección -->
                                    <div class="col-span-6">
                                        <label for="address" class="block text-sm font-bold text-sound-blue">Dirección Fiscal / Depósito</label>
                                        <div class="mt-2 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                                <svg class="h-5 w-5 text-sound-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </span>
                                            <input type="text" name="address" id="address" value="{{ old('address', $supplier->address) }}" required
                                                class="flex-1 focus:ring-sound-blue focus:border-sound-blue block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 transition-colors">
                                        </div>
                                        @error('address') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Teléfono -->
                                    <div class="col-span-6 sm:col-span-4 lg:col-span-3">
                                        <label for="phone" class="block text-sm font-bold text-sound-blue">Línea de Contacto</label>
                                        <div class="mt-2 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                                <svg class="h-5 w-5 text-sound-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            </span>
                                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}" required
                                                class="flex-1 focus:ring-sound-blue focus:border-sound-blue block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 transition-colors">
                                        </div>
                                        @error('phone') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Footer del formulario / Acciones -->
                            <div class="px-4 py-4 bg-gray-50 border-t border-gray-200 text-right sm:px-6 flex items-center justify-end space-x-4">
                                <a href="{{ route('supplier.index') }}" class="text-sm font-bold text-gray-500 hover:text-sound-blue transition-colors">
                                    {{ __('Cancelar') }}
                                </a>
                                <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-sound-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sound-gold transition-all uppercase tracking-wider">
                                    {{ __('Actualizar Proveedor') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
