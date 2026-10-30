<div 
    x-data="{ open: false }" 
    @open-cart.window="open = true" 
    @close-cart.window="open = false" 
    @keydown.escape.window="open = false"
>
    <!-- Fondo oscuro backdrop con desenfoque suave -->
    <div 
        x-show="open" 
        x-cloak 
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 cursor-pointer"
    ></div>

    <!-- Panel lateral (Slide-over) -->
    <div 
        x-show="open" 
        x-cloak 
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 max-w-full w-full sm:w-[420px] bg-white dark:bg-[#181715] z-50 shadow-2xl flex flex-col justify-between text-zinc-900 dark:text-zinc-100 border-l border-zinc-200 dark:border-zinc-800"
    >
        <!-- Encabezado del Carrito -->
        <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-[#201E1C]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#D97724]/10 flex items-center justify-center text-[#D97724]">
                    <x-mary-icon name="o-shopping-bag" class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="font-extrabold text-sm uppercase tracking-wide">Tu Selección</h3>
                    <p class="text-[11px] text-zinc-400">{{ $this->cartCount }} {{ $this->cartCount == 1 ? 'ítem seleccionado' : 'ítems seleccionados' }}</p>
                </div>
            </div>
            <button 
                @click="open = false" 
                type="button" 
                class="btn btn-ghost btn-circle btn-sm text-zinc-400 hover:text-zinc-700 dark:hover:text-white"
                title="Cerrar"
            >
                <x-mary-icon name="o-x-mark" class="w-5 h-5" />
            </button>
        </div>

        <!-- Lista de Productos en Carrito -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            @forelse($this->cart as $id => $item)
                <div class="flex gap-3.5 p-3 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/60 dark:border-zinc-800/60 items-center transition-all">
                    <!-- Foto del Instrumento o Ticket de Evento en Carrito -->
                    <div class="w-16 h-16 shrink-0 bg-white dark:bg-[#181715] rounded-xl flex items-center justify-center p-1 border border-zinc-100 dark:border-zinc-800">
                        @if(!empty($item['type']) && $item['type'] === 'event')
                            <div class="flex flex-col items-center justify-center text-center">
                                <x-mary-icon name="o-ticket" class="w-6 h-6 text-[#D97724]" />
                                <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-tighter mt-0.5">Pase</span>
                            </div>
                        @elseif(!empty($item['image']))
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="max-h-full max-w-full object-contain" />
                        @else
                            <x-mary-icon name="o-musical-note" class="w-6 h-6 text-[#D97724]" />
                        @endif
                    </div>

                    <!-- Datos y Cantidades con Optimistic UI -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold truncate leading-tight">{{ $item['name'] }}</h4>
                        @if(!empty($item['type']) && $item['type'] === 'event' && !empty($item['date']))
                            <p class="text-[10px] text-zinc-400 font-medium flex items-center gap-1 mt-0.5">
                                <x-mary-icon name="o-calendar" class="w-3 h-3 text-[#D97724] shrink-0" />
                                <span>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }} @if(!empty($item['time']))- {{ substr($item['time'], 0, 5) }} hs @endif</span>
                            </p>
                        @endif
                        @php
                            $itemPct = (!empty($item['type']) && $item['type'] === 'event') ? 0 : (!empty($item['discount_percentage']) ? ($item['discount_percentage'] / 100) : 0.10);
                            $itemPriceFinal = ($item['price'] ?? 0) * (1 - $itemPct);
                        @endphp
                        <p class="text-[11px] font-black text-[#D97724] mt-0.5">$ {{ number_format($itemPriceFinal, 0, ',', '.') }}</p>
                        
                        <div class="flex items-center justify-between mt-2">
                            <!-- Selector Optimistic UI (0 ms de Lag) -->
                            <div 
                                x-data="{ 
                                    qty: {{ (int) ($item['cantidad'] ?? 1) }},
                                    inc() { this.qty++; $wire.incrementarCantidad('{{ $id }}'); },
                                    dec() { if (this.qty > 1) { this.qty--; } $wire.decrementarCantidad('{{ $id }}'); }
                                }" 
                                class="flex items-center border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden h-6 bg-white dark:bg-[#181715]"
                            >
                                <button 
                                    type="button"
                                    @click="dec()" 
                                    class="w-6 h-full flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-800 text-xs font-bold"
                                >
                                    -
                                </button>
                                <span class="px-2 text-xs font-semibold" x-text="qty"></span>
                                <button 
                                    type="button"
                                    @click="inc()" 
                                    class="w-6 h-full flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-800 text-xs font-bold"
                                >
                                    +
                                </button>
                            </div>
                            <button 
                                wire:click="eliminarDelCarrito('{{ $id }}')" 
                                class="text-xs text-rose-500 hover:text-rose-700 font-semibold transition-colors"
                                title="Quitar"
                            >
                                <x-mary-icon name="o-trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center py-12 text-zinc-400">
                    <x-mary-icon name="o-shopping-bag" class="w-16 h-16 stroke-1 mb-3 text-zinc-300 dark:text-zinc-700" />
                    <p class="font-bold text-base text-zinc-800 dark:text-zinc-200">Tu selección está vacía</p>
                    <p class="text-xs mt-1 max-w-[200px]">Explorá el catálogo para conocer los instrumentos y recitales disponibles.</p>
                    <button 
                        @click="open = false" 
                        type="button" 
                        class="btn bg-[#D97724] text-white hover:bg-[#c06517] btn-sm mt-4 rounded-full font-bold uppercase text-xs border-none"
                    >
                        Ver Productos
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Footer Financiero del Carrito con Legalidad Transparente -->
        @if($this->cartCount > 0)
            <div class="p-4 sm:p-5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-[#201E1C] space-y-3">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-zinc-500">
                        <span>Subtotal Precio de Lista:</span>
                        <span class="line-through">$ {{ number_format($this->cartListSubtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[#D97724] font-bold bg-[#D97724]/10 px-2.5 py-1 rounded-lg border border-[#D97724]/20">
                        <span>Ahorro pago contado (10% OFF):</span>
                        <span>-$ {{ number_format($this->cartSavings, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-zinc-900 dark:text-white pt-1 border-t border-zinc-200 dark:border-zinc-800">
                        <span>Total Efectivo / Transferencia:</span>
                        <span class="text-[#D97724] text-xl">$ {{ number_format($this->cartSubtotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="space-y-2 pt-1">
                    <a 
                        href="{{ route('checkout') }}"
                        class="btn bg-[#D97724] hover:bg-[#c06517] text-white w-full font-black uppercase tracking-wide text-xs shadow-lg shadow-[#D97724]/20 rounded-xl border-none"
                    >
                        Iniciar Compra / Checkout
                    </a>
                    
                    <div class="flex gap-2">
                        <button 
                            @click="open = false" 
                            type="button" 
                            class="btn btn-ghost btn-sm flex-1 font-bold text-xs text-zinc-600 dark:text-zinc-400"
                        >
                            Seguir viendo
                        </button>
                        <button 
                            wire:click="vaciarCarrito" 
                            type="button" 
                            class="btn btn-ghost btn-sm text-zinc-400 hover:text-rose-500 text-xs font-semibold"
                        >
                            Vaciar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

