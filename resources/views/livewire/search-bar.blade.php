<div 
    x-data="{ searchOpen: false }" 
    @click.outside="searchOpen = false" 
    class="flex-1 max-w-md lg:max-w-xl mx-2 lg:mx-6 relative hidden md:block"
>
    <!-- Campo de Búsqueda -->
    <div class="relative flex items-center">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            wire:keydown.enter="submitSearch"
            @focus="searchOpen = true"
            placeholder="¿Qué estás buscando? Instrumentos, marcas, modelos..." 
            class="w-full h-10 pl-10 pr-10 rounded-full bg-white/10 hover:bg-white/15 focus:bg-white text-white focus:text-zinc-900 placeholder-zinc-400 text-xs shadow-xs focus:outline-none focus:ring-2 focus:ring-[#D97724] transition-all font-medium border border-white/15 focus:border-transparent"
        />
        <x-mary-icon name="o-magnifying-glass" class="w-4 h-4 text-zinc-400 absolute left-3.5 pointer-events-none" />
        
        @if(!empty($search))
            <button 
                type="button" 
                wire:click="limpiar" 
                class="absolute right-3.5 text-zinc-400 hover:text-white transition-colors cursor-pointer"
                title="Borrar texto"
            >
                <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
            </button>
        @endif
    </div>

    <!-- Menú Desplegable Flotante del Buscador -->
    @if(mb_strlen(trim($search)) >= 2)
        <div 
            x-show="searchOpen" 
            x-cloak 
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute top-12 left-0 right-0 bg-white dark:bg-[#201E1C] rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 p-4 z-50 space-y-3 text-zinc-900 dark:text-zinc-100"
        >
            <!-- Filtros Rápidos de Ámbito -->
            <div class="flex items-center gap-1.5 pb-2 border-b border-zinc-100 dark:border-zinc-800 text-[11px] font-bold">
                <span class="text-zinc-400 mr-1">Filtrar:</span>
                <button 
                    type="button" 
                    wire:click="$set('scope', 'all')" 
                    class="px-2.5 py-1 rounded-full transition-colors cursor-pointer {{ $scope === 'all' ? 'bg-[#D97724] text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300' }}"
                >
                    Todo
                </button>
                <button 
                    type="button" 
                    wire:click="$set('scope', 'cuerda')" 
                    class="px-2.5 py-1 rounded-full transition-colors cursor-pointer {{ $scope === 'cuerda' ? 'bg-[#D97724] text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300' }}"
                >
                    Cuerdas
                </button>
                <button 
                    type="button" 
                    wire:click="$set('scope', 'amplificacion')" 
                    class="px-2.5 py-1 rounded-full transition-colors cursor-pointer {{ $scope === 'amplificacion' ? 'bg-[#D97724] text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300' }}"
                >
                    Amplis
                </button>
                <button 
                    type="button" 
                    wire:click="$set('scope', 'teclado')" 
                    class="px-2.5 py-1 rounded-full transition-colors cursor-pointer {{ $scope === 'teclado' ? 'bg-[#D97724] text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300' }}"
                >
                    Teclados
                </button>
            </div>

            <!-- Resultados en Vivo según lo tipeado -->
            <div class="pt-1">
                <p class="text-xs text-zinc-500 mb-2">Resultados para "<strong class="text-zinc-900 dark:text-white">{{ $search }}</strong>":</p>
                <div class="max-h-60 overflow-y-auto space-y-1">
                    @forelse($coincidencias as $p)
                        <div 
                            wire:click="selectProduct({{ $p->id }})" 
                            class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-between cursor-pointer group transition-colors"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#D97724] shrink-0"></span>
                                <div class="truncate">
                                    <span class="text-xs font-bold group-hover:text-[#D97724] transition-colors">{{ $p->name }}</span>
                                    <span class="text-[10px] text-zinc-400 block">{{ $p->brand }} · {{ $p->category }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-black text-[#D97724] shrink-0 ml-2">
                                $ {{ number_format($p->price * 0.90, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-zinc-400">
                            No se encontraron productos para "{{ $search }}".
                        </div>
                    @endforelse
                </div>
                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-center">
                    <button 
                        type="button" 
                        wire:click="submitSearch" 
                        class="text-xs text-[#D97724] hover:underline font-bold"
                    >
                        Ver todos los resultados en el catálogo →
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

