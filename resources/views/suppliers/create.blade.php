<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form action="{{ route('supplier.store') }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                    @csrf

                    <!-- Campo Nombre -->
                    <div class="mb-4">
                        <label for="name" class="block font-medium text-sm text-gray-700">Supplier Name</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Campo Dirección -->
                    <div class="mb-4">
                        <label for="address" class="block font-medium text-sm text-gray-700">Address</label>
                        <input type="text" name="address" id="address" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Campo Teléfono -->
                    <div class="mb-4">
                        <label for="phone" class="block font-medium text-sm text-gray-700">Phone</label>
                        <input type="text" name="phone" id="phone" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('supplier.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                            {{ __('Save Supplier') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
