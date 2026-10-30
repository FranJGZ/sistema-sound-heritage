<div 
    x-data="{ 
        catOpen: true,
        fechasOpen: true
    }"
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>

    <!-- ========================================================================= -->
    <!-- 2. BARRA SUPERIOR DE SUB-CABECERA (BREADCRUMB + ORDENAR POR + VISTAS)     -->
    <!-- ========================================================================= -->
    <div class="bg-white/80 dark:bg-[#181715]/90 backdrop-blur-xs border-b border-zinc-200/80 dark:border-zinc-800 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            
            <!-- Breadcrumb Ámbar Sound Heritage -->
            <div class="flex items-center gap-2 flex-wrap">
                @if(!$categoria)
                    <div class="flex items-center gap-2">
                        <span class="text-[#D97724] font-bold text-lg flex items-center">
                            <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3]" />
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                            Cartelera de Eventos
                        </h1>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="limpiarTodo" 
                            class="text-[#D97724] font-bold text-lg flex items-center hover:opacity-80 transition-opacity cursor-pointer group"
                            title="Volver a todos los eventos"
                        >
                            <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3] group-hover:-translate-x-0.5 transition-transform" />
                            <span class="text-zinc-500 dark:text-zinc-400 font-semibold text-sm group-hover:underline">Cartelera</span>
                        </button>
                        <span class="text-zinc-400">/</span>
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                            {{ $categorias[$categoria]['label'] ?? $categoria }}
                        </h1>
                    </div>
                @endif
                <span class="text-xs text-zinc-400 font-medium ml-2">
                    ({{ $eventos->total() }} {{ $eventos->total() === 1 ? 'evento disponible' : 'eventos disponibles' }})
                </span>
            </div>

            <!-- Controles a la Derecha: Select "Ordenar por" + Botones [:::] y [=] -->
            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                <div class="flex items-center gap-2">
                    <label for="sort-select" class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 hidden sm:inline">
                        Ordenar por:
                    </label>
                    <select 
                        id="sort-select" 
                        wire:model.live="sort"
                        class="select select-sm text-xs font-bold rounded-xl bg-white dark:bg-[#201E1C] border border-zinc-300 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-[#D97724] h-9"
                    >
                        <option value="proximos">Próximas fechas</option>
                        <option value="precio_asc">Menor precio de entrada</option>
                        <option value="precio_desc">Mayor precio de entrada</option>
                    </select>
                </div>

                <!-- Selector de Vista: Cuadrícula [:::] vs Lista [=] -->
                <div class="flex items-center bg-zinc-100 dark:bg-[#201E1C] p-1 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <button 
                        type="button" 
                        wire:click="$set('vista', 'grid')" 
                        class="p-1.5 rounded-lg transition-all {{ $vista === 'grid' ? 'bg-white dark:bg-zinc-800 text-[#D97724] shadow-xs font-bold' : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}"
                        title="Vista Cuadrícula"
                    >
                        <x-mary-icon name="o-squares-2x2" class="w-4 h-4" />
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('vista', 'list')" 
                        class="p-1.5 rounded-lg transition-all {{ $vista === 'list' ? 'bg-white dark:bg-zinc-800 text-[#D97724] shadow-xs font-bold' : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}"
                        title="Vista Lista"
                    >
                        <x-mary-icon name="o-bars-3-bottom-left" class="w-4 h-4" />
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. CUERPO PRINCIPAL (2 COLUMNAS: SIDEBAR IZQUIERDA + GRILLA DERECHA)      -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- ===================================================================== -->
            <!-- COLUMNA IZQUIERDA: CATEGORÍAS CULTURALES Y FILTRO ESTRICTO DE FECHAS  -->
            <!-- ===================================================================== -->
            <aside class="lg:col-span-3 space-y-5">
                
                <!-- ACCORDION 1: CATEGORÍAS CULTURALES -->
                <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 shadow-xs overflow-hidden">
                    <button 
                        type="button" 
                        @click="catOpen = !catOpen" 
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <span class="text-[#D97724] font-black text-xs" x-text="catOpen ? '▲' : '▼'"></span>
                            <span>Categorías</span>
                        </span>
                        @if($categoria)
                            <span 
                                wire:click.stop="seleccionarCategoria(null)" 
                                class="text-[11px] text-zinc-400 hover:text-[#D97724] font-semibold cursor-pointer"
                                title="Mostrar todas las categorías"
                            >
                                Ver todas
                            </span>
                        @endif
                    </button>

                    <div x-show="catOpen" x-collapse class="px-3 pb-3 pt-1 space-y-1 border-t border-zinc-100 dark:border-zinc-800">
                        @foreach($categorias as $key => $catDef)
                            @php
                                $isCatActive = ($categoria === $key);
                                $totalCount = $categoryCounts[$key] ?? 0;
                            @endphp
                            <div class="rounded-xl overflow-hidden transition-colors">
                                <div class="flex items-center justify-between px-2 py-2 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors {{ $isCatActive ? 'bg-[#D97724]/10 text-[#D97724] font-bold dark:bg-[#D97724]/20' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    <!-- Palomita ámbar a la izquierda -->
                                    <span class="text-[#D97724] font-black text-sm px-1 shrink-0">
                                        {{ $isCatActive ? '▼' : '›' }}
                                    </span>

                                    <!-- Nombre de la Categoría -->
                                    <button 
                                        type="button" 
                                        wire:click="seleccionarCategoria('{{ $key }}')" 
                                        class="flex-1 text-left px-1.5 py-0.5 text-xs font-semibold truncate hover:text-[#D97724] cursor-pointer"
                                    >
                                        {{ $catDef['label'] }}
                                    </button>

                                    <!-- Conteo total de la categoría -->
                                    <span class="text-[10px] text-zinc-400 px-1.5 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                        {{ $totalCount }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ACCORDION 2: FILTRO DE FECHAS (EXCLUSIVAMENTE FECHA DESDE / HASTA) -->
                <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 shadow-xs overflow-hidden">
                    <button 
                        type="button" 
                        @click="fechasOpen = !fechasOpen" 
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <span class="text-[#D97724] font-black text-xs" x-text="fechasOpen ? '▲' : '▼'"></span>
                            <span>Filtrar por Fecha</span>
                        </span>
                        @if(!empty($dateFrom) || !empty($dateTo))
                            <span 
                                wire:click.stop="limpiarFechas" 
                                class="text-[11px] text-[#D97724] hover:underline font-semibold cursor-pointer"
                                title="Limpiar rango de fechas"
                            >
                                Limpiar
                            </span>
                        @endif
                    </button>

                    <div x-show="fechasOpen" x-collapse class="px-5 pb-5 pt-3 space-y-4 border-t border-zinc-100 dark:border-zinc-800">
                        <!-- Fecha Desde -->
                        <div class="space-y-1">
                            <label for="fecha-desde" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Fecha Desde
                            </label>
                            <input 
                                id="fecha-desde"
                                type="date" 
                                wire:model.live="dateFrom" 
                                class="w-full text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-[#201E1C] text-zinc-800 dark:text-zinc-200 px-3 py-2 focus:ring-2 focus:ring-[#D97724] focus:outline-none"
                            />
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="space-y-1">
                            <label for="fecha-hasta" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Fecha Hasta
                            </label>
                            <input 
                                id="fecha-hasta"
                                type="date" 
                                wire:model.live="dateTo" 
                                class="w-full text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-[#201E1C] text-zinc-800 dark:text-zinc-200 px-3 py-2 focus:ring-2 focus:ring-[#D97724] focus:outline-none"
                            />
                        </div>

                        <!-- Botón Resetear si hay fechas aplicadas -->
                        @if(!empty($dateFrom) || !empty($dateTo))
                            <button 
                                type="button" 
                                wire:click="limpiarFechas" 
                                class="w-full btn btn-sm btn-ghost text-xs text-[#D97724] hover:bg-[#D97724]/10 font-bold border border-[#D97724]/30 rounded-xl"
                            >
                                <x-mary-icon name="o-arrow-path" class="w-3.5 h-3.5" />
                                Resetear fechas
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Botón Limpiar Todo (si hay algún filtro aplicado) -->
                @if($categoria || !empty($dateFrom) || !empty($dateTo) || !empty($search))
                    <div class="pt-1">
                        <button 
                            type="button" 
                            wire:click="limpiarTodo" 
                            class="w-full py-2.5 px-4 rounded-xl border border-[#7E2B2D]/40 bg-[#7E2B2D]/10 text-[#D97724] dark:text-[#D97724] text-xs font-bold hover:bg-[#7E2B2D]/20 transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                        >
                            <x-mary-icon name="o-trash" class="w-4 h-4" />
                            Resetear todos los filtros
                        </button>
                    </div>
                @endif

            </aside>

            <!-- ===================================================================== -->
            <!-- COLUMNA DERECHA: GRILLA / LISTA DE EVENTOS                            -->
            <!-- ===================================================================== -->
            <div class="lg:col-span-9 space-y-6">
                
                @if($eventos->count() > 0)
                    @if($vista === 'grid')
                        <!-- VISTA CUADRÍCULA (3 Columnas) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach($eventos as $evento)
                                @php
                                    $cupoRestante = max(0, $evento->capacity - ($evento->attendances_count ?? 0));
                                    $isAgotado = ($cupoRestante <= 0);
                                    $eventDate = \Carbon\Carbon::parse($evento->date);
                                    $qtySelected = $this->getCantidad($evento->id);
                                @endphp
                                <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-5 shadow-xs flex flex-col justify-between hover:shadow-lg hover:border-[#D97724]/40 transition-all duration-200 group relative">
                                    
                                    <div>
                                        <!-- Fila Superior: Fecha y Hora -->
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800/70 px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                                                <x-mary-icon name="o-calendar" class="w-3.5 h-3.5 text-[#D97724]" />
                                                {{ $eventDate->format('d/m/Y') }}
                                            </span>
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                                                <x-mary-icon name="o-clock" class="w-3.5 h-3.5 text-[#D97724]" />
                                                {{ substr($evento->time, 0, 5) }} hs
                                            </span>
                                        </div>

                                        <!-- Título del Evento -->
                                        <a href="{{ route('evento.detalle', $evento->id) }}" class="block">
                                            <h3 class="text-base font-black text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors line-clamp-2 leading-snug">
                                                {{ $evento->name }}
                                            </h3>
                                        </a>

                                        <!-- Descripción -->
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed mt-2.5">
                                            {{ $evento->description ?? 'Concierto y actividad cultural exclusiva en vivo en el escenario del auditorio Sound Heritage.' }}
                                        </p>
                                    </div>

                                    <!-- Fila de Compra y Precio -->
                                    <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex items-baseline justify-between mb-3">
                                            <span class="text-[11px] uppercase font-bold text-zinc-400">Entrada general:</span>
                                            <span class="text-xl font-black text-zinc-900 dark:text-white">
                                                $ {{ number_format($evento->price, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        @if(!$isAgotado)
                                            <!-- Botón Directo Añadir al Carrito -->
                                            <button 
                                                type="button" 
                                                wire:click="agregarAlCarrito({{ $evento->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="agregarAlCarrito({{ $evento->id }})"
                                                class="w-full btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm h-10 rounded-xl font-bold uppercase text-xs border-none shadow-md shadow-[#D97724]/20 flex items-center justify-center gap-1.5 cursor-pointer"
                                            >
                                                <span wire:loading.remove wire:target="agregarAlCarrito({{ $evento->id }})">
                                                    <x-mary-icon name="o-shopping-cart" class="w-4 h-4 inline" />
                                                    Añadir al carrito
                                                </span>
                                                <span wire:loading wire:target="agregarAlCarrito({{ $evento->id }})">
                                                    <span class="loading loading-spinner loading-xs"></span>
                                                    Añadiendo...
                                                </span>
                                            </button>
                                        @else
                                            <button 
                                                disabled 
                                                class="btn btn-disabled btn-sm w-full rounded-xl text-xs font-bold uppercase"
                                            >
                                                Agotado
                                            </button>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- VISTA LISTA -->
                        <div class="space-y-4">
                            @foreach($eventos as $evento)
                                @php
                                    $cupoRestante = max(0, $evento->capacity - ($evento->attendances_count ?? 0));
                                    $isAgotado = ($cupoRestante <= 0);
                                    $eventDate = \Carbon\Carbon::parse($evento->date);
                                    $qtySelected = $this->getCantidad($evento->id);
                                @endphp
                                <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-lg hover:border-[#D97724]/40 transition-all duration-200 group">
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                                            <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800/70 px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                                                <x-mary-icon name="o-calendar" class="w-3.5 h-3.5 text-[#D97724]" />
                                                {{ $eventDate->format('d/m/Y') }} • {{ substr($evento->time, 0, 5) }} hs
                                            </span>
                                        </div>

                                        <a href="{{ route('evento.detalle', $evento->id) }}" class="block">
                                            <h3 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors truncate">
                                                {{ $evento->name }}
                                            </h3>
                                        </a>

                                        <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed mt-1">
                                            {{ $evento->description ?? 'Actividad cultural exclusiva en el auditorio Sound Heritage.' }}
                                        </p>
                                    </div>

                                    <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-zinc-100 dark:border-zinc-800">
                                        <div class="text-left md:text-right">
                                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Entrada general:</span>
                                            <span class="text-xl font-black text-zinc-900 dark:text-white">
                                                $ {{ number_format($evento->price, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        @if(!$isAgotado)
                                            <button 
                                                type="button" 
                                                wire:click="agregarAlCarrito({{ $evento->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="agregarAlCarrito({{ $evento->id }})"
                                                class="btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm h-10 px-5 rounded-xl font-bold uppercase text-xs border-none shadow-md shadow-[#D97724]/20 flex items-center gap-1.5 cursor-pointer"
                                            >
                                                <span wire:loading.remove wire:target="agregarAlCarrito({{ $evento->id }})">
                                                    <x-mary-icon name="o-shopping-cart" class="w-4 h-4 inline" />
                                                    Añadir al carrito
                                                </span>
                                                <span wire:loading wire:target="agregarAlCarrito({{ $evento->id }})">
                                                    <span class="loading loading-spinner loading-xs"></span>
                                                    Añadiendo...
                                                </span>
                                            </button>
                                        @else
                                            <span class="text-zinc-400 uppercase text-[10px] font-black">Agotado</span>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Paginación Sound Heritage -->
                    <div class="pt-6">
                        {{ $eventos->links() }}
                    </div>
                @else
                    <!-- Estado Vacío -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-12 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-full bg-[#D97724]/10 text-[#D97724] flex items-center justify-center mx-auto mb-4">
                            <x-mary-icon name="o-calendar-days" class="w-8 h-8 stroke-2" />
                        </div>
                        <h3 class="text-lg font-black text-zinc-900 dark:text-white">
                            No se encontraron eventos para esta búsqueda o fechas
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mt-2 leading-relaxed">
                            Probá ampliando el rango de fechas, seleccionando otra categoría cultural o limpiando los filtros para ver la cartelera completa.
                        </p>
                        <div class="mt-6">
                            <button 
                                type="button" 
                                wire:click="limpiarTodo" 
                                class="btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm rounded-xl font-bold uppercase text-xs border-none px-6"
                            >
                                Ver todos los eventos
                            </button>
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </main>
</div>
