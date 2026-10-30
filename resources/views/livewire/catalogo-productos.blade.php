<div 
    x-data="{ 
        catOpen: true,
        filtrosOpen: true
    }"
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>
    @php
        $rootLabel = $soloOfertas ? 'Ofertas' : 'Destacados';
    @endphp

    <!-- ========================================================================= -->
    <!-- 2. BARRA SUPERIOR DE SUB-CABECERA (BREADCRUMB + ORDENAR POR + VISTAS)     -->
    <!-- ========================================================================= -->
    <div class="bg-white/80 dark:bg-[#181715]/90 backdrop-blur-xs border-b border-zinc-200/80 dark:border-zinc-800 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            
            <!-- Breadcrumb Ámbar Sound Heritage (Ofertas o Destacados) -->
            <div class="flex items-center gap-2 flex-wrap">
                @if(!$categoria)
                    <div class="flex items-center gap-2">
                        <span class="text-[#D97724] font-bold text-lg flex items-center">
                            <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3]" />
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                            {{ $rootLabel }}
                        </h1>
                    </div>
                @elseif($subcategoria)
                    <div class="flex items-center gap-2 flex-wrap">
                        <button 
                            type="button" 
                            wire:click="limpiarTodo" 
                            class="text-[#D97724] font-bold text-lg flex items-center hover:opacity-80 transition-opacity cursor-pointer group"
                            title="Volver a {{ $rootLabel }}"
                        >
                            <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3] group-hover:-translate-x-0.5 transition-transform" />
                            <span class="text-zinc-500 dark:text-zinc-400 font-semibold text-sm group-hover:underline">{{ $rootLabel }}</span>
                        </button>
                        <span class="text-zinc-400">/</span>
                        <button 
                            type="button"
                            wire:click="seleccionarCategoria('{{ $categoria }}')"
                            class="text-zinc-600 dark:text-zinc-400 hover:text-[#D97724] font-semibold text-sm cursor-pointer hover:underline"
                        >
                            {{ $categorias[$categoria] ?? $categoria }}
                        </button>
                        <span class="text-zinc-400">/</span>
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight capitalize">
                            {{ $subcategoriasTaxonomia[$categoria][$subcategoria]['name'] ?? $subcategoria }}
                        </h1>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="limpiarTodo" 
                            class="text-[#D97724] font-bold text-lg flex items-center hover:opacity-80 transition-opacity cursor-pointer group"
                            title="Volver a {{ $rootLabel }}"
                        >
                            <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3] group-hover:-translate-x-0.5 transition-transform" />
                            <span class="text-zinc-500 dark:text-zinc-400 font-semibold text-sm group-hover:underline">{{ $rootLabel }}</span>
                        </button>
                        <span class="text-zinc-400">/</span>
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight capitalize">
                            {{ $categorias[$categoria] ?? $categoria }}
                        </h1>
                    </div>
                @endif
                <span class="text-xs text-zinc-400 font-medium ml-2">
                    ({{ $productos->total() }} {{ $productos->total() === 1 ? 'producto' : 'productos' }})
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
                        <option value="destacados">Destacados</option>
                        <option value="precio_desc">Mayor precio</option>
                        <option value="precio_asc">Menor precio</option>
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
            <!-- COLUMNA IZQUIERDA: CATEGORÍAS CON PALOMITAS DE EXPANSIÓN Y SUBPRODUCTOS -->
            <!-- ===================================================================== -->
            <aside class="lg:col-span-3 space-y-5">
                
                <!-- ACCORDION: CATEGORÍAS -->
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
                                wire:click.stop="limpiarTodo" 
                                class="text-[11px] text-zinc-400 hover:text-[#D97724] font-semibold cursor-pointer"
                                title="Mostrar todas las categorías"
                            >
                                Ver todas
                            </span>
                        @endif
                    </button>

                    <div x-show="catOpen" x-collapse class="px-3 pb-3 pt-1 space-y-1 border-t border-zinc-100 dark:border-zinc-800">
                        @foreach($categorias as $key => $name)
                            @php
                                $isCatActive = ($categoria === $key);
                                $isExpanded = in_array($key, $expandedCategories);
                                $totalCount = $categoryCounts[$key] ?? 0;
                            @endphp
                            <div class="rounded-xl overflow-hidden transition-colors">
                                
                                <!-- Fila de Categoría con palomita a la izquierda -->
                                <div class="flex items-center justify-between px-2 py-1.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors {{ ($isCatActive && empty($subcategoria)) ? 'bg-[#D97724]/10 text-[#D97724] font-bold dark:bg-[#D97724]/20' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    
                                    <!-- Palomita ámbar a la izquierda que expande/colapsa -->
                                    <button 
                                        type="button" 
                                        wire:click="toggleExpandCategory('{{ $key }}')" 
                                        class="p-1 hover:text-[#D97724] text-[#D97724] font-black transition-colors cursor-pointer shrink-0"
                                        title="{{ $isExpanded ? 'Contraer subcategorías' : 'Expandir subcategorías' }}"
                                    >
                                        <span class="inline-block transition-transform duration-200 text-xs {{ $isExpanded ? 'rotate-90' : '' }}">
                                            ›
                                        </span>
                                    </button>

                                    <!-- Nombre de la Categoría -->
                                    <button 
                                        type="button" 
                                        wire:click="seleccionarCategoria('{{ $key }}')" 
                                        class="flex-1 text-left px-1.5 py-0.5 text-xs font-semibold truncate hover:text-[#D97724] cursor-pointer"
                                    >
                                        {{ $name }}
                                    </button>

                                    <!-- Conteo total de la categoría -->
                                    <span class="text-[10px] text-zinc-400 px-1.5 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                        {{ $totalCount }}
                                    </span>
                                </div>

                                <!-- Subproductos / Subcategorías desplegables -->
                                @if($isExpanded)
                                    <div class="pl-6 pr-2 py-1 space-y-0.5 border-l-2 border-[#D97724]/30 dark:border-[#D97724]/40 ml-3.5 my-1 animate-fadeIn">
                                        @foreach($subcategoriasTaxonomia[$key] ?? [] as $subKey => $subDef)
                                            @php
                                                $isSubActive = ($isCatActive && $subcategoria === $subKey);
                                                $subCount = $subcatCounts[$key][$subKey] ?? 0;
                                            @endphp
                                            <button 
                                                type="button" 
                                                wire:click="seleccionarSubcategoria('{{ $key }}', '{{ $subKey }}')" 
                                                class="w-full text-left px-2 py-1 rounded-lg text-xs flex items-center justify-between transition-all cursor-pointer {{ $isSubActive ? 'bg-[#D97724] text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40' }}"
                                            >
                                                <span class="truncate">{{ $subDef['name'] }}</span>
                                                <span class="text-[10px] ml-1 {{ $isSubActive ? 'text-white/90' : 'text-zinc-400' }}">({{ $subCount }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ================================================================= -->
                <!-- ACCORDION: FILTROS TÉCNICOS EXCLUSIVOS DEL ERP                     -->
                <!-- * SÓLO SE ACTIVA CUANDO SE SELECCIONA UNA CATEGORÍA / SUBCATEGORÍA * -->
                <!-- ================================================================= -->
                @if($categoria)
                    <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 shadow-xs overflow-hidden animate-fadeIn">
                        
                        <!-- Encabezado Filtros -->
                        <div class="px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30">
                            <span class="flex items-center gap-2">
                                <span class="text-[#D97724] font-black text-xs">▼</span>
                                <span>Filtros Técnicos</span>
                            </span>
                            @if(!empty($selectedBrands) || !empty($selectedSpecs))
                                <button 
                                    type="button" 
                                    wire:click="limpiarFiltros" 
                                    class="text-[11px] text-[#D97724] hover:underline font-bold cursor-pointer"
                                >
                                    Limpiar
                                </button>
                            @endif
                        </div>

                        <div class="p-4 space-y-5">
                            
                            <!-- 1. FILTRO DE MARCAS DISPONIBLES EN ESTA CATEGORÍA -->
                            @if($availableBrands->isNotEmpty())
                                <div class="space-y-2">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Marca
                                    </h4>
                                    <div class="max-h-48 overflow-y-auto space-y-1 pr-1 scrollbar-thin">
                                        @foreach($availableBrands as $b)
                                            <label class="flex items-center justify-between text-xs text-zinc-700 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white cursor-pointer py-1 px-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800/60">
                                                <div class="flex items-center gap-2 truncate">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:click="toggleBrand('{{ $b->brand }}')"
                                                        @checked(in_array($b->brand, $selectedBrands))
                                                        class="checkbox checkbox-xs rounded border-zinc-400 text-[#D97724] focus:ring-[#D97724]" 
                                                    />
                                                    <span class="truncate">{{ $b->brand }}</span>
                                                </div>
                                                <span class="text-[10px] text-zinc-400 ml-1">({{ $b->count }})</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- 2. FILTROS EXCLUSIVOS SEGÚN TIPO EN ERP (ESPECIFICACIONES PRODUCTRESOURCE) -->
                            @if(!empty($this->specsSchema))
                                <div class="divider my-2 border-zinc-100 dark:border-zinc-800"></div>

                                @foreach($this->specsSchema as $specKey => $specConfig)
                                    <div class="space-y-1.5">
                                        <label for="spec-{{ $specKey }}" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                            {{ $specConfig['label'] }}
                                        </label>
                                        <select 
                                            id="spec-{{ $specKey }}"
                                            wire:change="setSpecFilter('{{ $specKey }}', $event.target.value)"
                                            class="select select-xs w-full rounded-xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200 dark:border-zinc-700 text-xs font-medium focus:ring-1 focus:ring-[#D97724] h-8"
                                        >
                                            <option value="">Todas las opciones</option>
                                            @foreach($specConfig['options'] as $val => $text)
                                                <option value="{{ $val }}" @selected(($selectedSpecs[$specKey] ?? null) === $val)>
                                                    {{ $text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Botón Restablecer si hay filtros aplicados -->
                            @if(!empty($selectedBrands) || !empty($selectedSpecs))
                                <div class="pt-2">
                                    <button 
                                        type="button" 
                                        wire:click="limpiarFiltros" 
                                        class="btn btn-sm btn-ghost w-full text-xs text-[#D97724] hover:bg-[#D97724]/10 font-bold rounded-xl"
                                    >
                                        <x-mary-icon name="o-trash" class="w-3.5 h-3.5" />
                                        Restablecer filtros
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>
                @else
                    <!-- Mensaje indicativo cuando está en vista general -->
                    <div class="bg-amber-50 dark:bg-[#201E1C] border border-amber-200/80 dark:border-zinc-800 rounded-2xl p-4 text-xs text-amber-900 dark:text-amber-200/80 leading-relaxed">
                        <p class="font-bold flex items-center gap-1.5 text-amber-800 dark:text-amber-400">
                            <x-mary-icon name="o-funnel" class="w-4 h-4" />
                            Filtros específicos
                        </p>
                        <p class="mt-1 text-[11px] text-zinc-600 dark:text-zinc-400">
                            Seleccioná una categoría o subproducto arriba para desbloquear los filtros técnicos de cada tipo de instrumento o accesorio.
                        </p>
                    </div>
                @endif

            </aside>

            <!-- ===================================================================== -->
            <!-- COLUMNA DERECHA: GRILLA DE PRODUCTOS ESTILO COMPRAGAMER               -->
            <!-- ===================================================================== -->
            <section class="lg:col-span-9 space-y-6">
                
                <!-- Chips de Filtros Activos Removibles -->
                @if(!empty($search) || !empty($selectedBrands) || !empty($selectedSpecs) || $categoria || $subcategoria)
                    <div class="flex flex-wrap items-center gap-2 p-3 bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-400 font-bold text-[11px] mr-1">Filtros:</span>
                        
                        @if($categoria)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-bold text-[11px]">
                                Categoría: {{ $categorias[$categoria] ?? $categoria }}
                                <button type="button" wire:click="seleccionarCategoria('{{ $categoria }}')" class="hover:text-rose-500 font-black ml-1">×</button>
                            </span>
                        @endif

                        @if($subcategoria)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#D97724]/10 text-[#D97724] font-bold text-[11px]">
                                Subproducto: {{ $subcategoriasTaxonomia[$categoria][$subcategoria]['name'] ?? $subcategoria }}
                                <button type="button" wire:click="$set('subcategoria', null)" class="hover:text-rose-700 font-black ml-1">×</button>
                            </span>
                        @endif

                        @if(!empty($search))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-bold text-[11px]">
                                Búsqueda: "{{ $search }}"
                                <button type="button" wire:click="$set('search', '')" class="hover:text-rose-500 font-black ml-1">×</button>
                            </span>
                        @endif

                        @foreach($selectedBrands as $b)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#D97724]/10 text-[#D97724] font-bold text-[11px]">
                                {{ $b }}
                                <button type="button" wire:click="toggleBrand('{{ $b }}')" class="hover:text-rose-700 font-black ml-1">×</button>
                            </span>
                        @endforeach

                        @foreach($selectedSpecs as $k => $v)
                            @if(!empty($v))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#D97724]/10 text-[#D97724] font-bold text-[11px]">
                                    {{ $v }}
                                    <button type="button" wire:click="setSpecFilter('{{ $k }}', null)" class="hover:text-amber-700 font-black ml-1">×</button>
                                </span>
                            @endif
                        @endforeach

                        <button 
                            type="button" 
                            wire:click="limpiarTodo" 
                            class="text-[11px] text-[#D97724] font-bold hover:underline ml-auto cursor-pointer"
                        >
                            Limpiar todo
                        </button>
                    </div>
                @endif

                <!-- PRODUCTOS: VISTA CUADRÍCULA (GRID VERTICAL COMPRAGAMER 1:1) -->
                @if($vista === 'grid')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @forelse($productos as $p)
                            @php
                                $precioLista = (float) $p->price;
                                $hasOffer = ($p->discount_percentage && $p->discount_percentage > 0);
                                $precioOferta = (float) $p->offer_price;
                                $precioEfectivo = (float) $p->cash_price;
                                $ahorroTotal = (float) $p->total_cash_savings;
                                $imgSrc = !empty($p->image) ? asset($p->image) : (!empty($p->image_path) ? asset($p->image_path) : null);
                            @endphp
                            
                            <!-- TARJETA ESTILO SOUND HERITAGE -->
                            <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between group h-full">
                                
                                <div>
                                    <!-- Contenedor de Imagen Cuadrado y Centrado -->
                                    <a href="{{ route('producto.detalle', $p->id) }}" class="block">
                                        <div class="w-full h-52 flex items-center justify-center p-3 relative bg-white dark:bg-[#141414] rounded-xl overflow-hidden cursor-pointer">
                                            
                                            <!-- CARTELITO ARRIBA A LA IZQUIERDA DENTRO DE LA FOTO DEL PRODUCTO (BORDÓ COLECCIONISTA) -->
                                            @if($hasOffer)
                                                <div class="absolute top-2.5 left-2.5 z-10 bg-[#7E2B2D] text-white text-[10px] font-black px-2 py-0.5 rounded shadow-md tracking-tight flex items-center gap-1 pointer-events-none">
                                                    <span>{{ $p->discount_percentage }}% OFF</span>
                                                    <span class="opacity-60">•</span>
                                                    <span>Ahorrás ${{ number_format($p->offer_savings, 0, ',', '.') }}</span>
                                                </div>
                                            @endif

                                            @if($imgSrc)
                                                <img 
                                                    src="{{ $imgSrc }}" 
                                                    alt="{{ $p->name }}" 
                                                    class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300" 
                                                    loading="lazy"
                                                />
                                            @else
                                                <x-mary-icon name="o-musical-note" class="w-16 h-16 text-zinc-300 dark:text-zinc-700" />
                                            @endif
                                        </div>
                                    </a>

                                    <!-- Fila Marca y Badge Superior -->
                                    <div class="flex items-center justify-between gap-2 mt-3">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 truncate">
                                            {{ $p->brand ?? 'Sound Heritage' }}
                                        </span>
                                        @if($hasOffer)
                                            <span class="text-[10px] font-bold text-[#7E2B2D] bg-[#7E2B2D]/10 border border-[#7E2B2D]/30 rounded px-1.5 py-0.2 shrink-0">
                                                Oferta {{ $p->discount_percentage }}% OFF
                                            </span>
                                        @elseif($p->condition === 'new' || $p->condition === 'Nuevo' || !$p->condition)
                                            <span class="text-[10px] font-bold text-zinc-500 border border-zinc-200 dark:border-zinc-700 rounded px-1.5 py-0.2 shrink-0">
                                                Nuevo
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-[#D97724] bg-[#D97724]/10 border border-[#D97724]/30 rounded px-1.5 py-0.2 shrink-0">
                                                Vintage
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Título del Producto (2 líneas máx) -->
                                    <a href="{{ route('producto.detalle', $p->id) }}" class="block group-hover:text-[#D97724] transition-colors">
                                        <h3 class="font-bold text-xs sm:text-sm text-zinc-900 dark:text-zinc-100 hover:text-[#D97724] dark:hover:text-[#D97724] line-clamp-2 mt-1.5 leading-snug min-h-[2.5rem]" title="{{ $p->name }}">
                                            {{ $p->name }}
                                        </h3>
                                    </a>
                                </div>

                                <!-- Bloque Inferior: Precios y Botón Sumar al Carrito -->
                                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800/80 mt-3">
                                    
                                    <!-- Precio de Lista Tachado + Badge de Ahorro Total -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-zinc-400 line-through">
                                            $ {{ number_format($precioLista, 0, ',', '.') }}
                                        </span>
                                        <span class="bg-[#D97724]/15 text-[#D97724] dark:bg-[#D97724]/25 font-black text-[10px] px-1.5 py-0.5 rounded-sm shrink-0">
                                            -$ {{ number_format($ahorroTotal, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Precio Grande Especial Contado (Efectivo / Transferencia) -->
                                    <div class="mt-1">
                                        <p class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                                            $ {{ number_format($precioEfectivo, 0, ',', '.') }}
                                        </p>
                                        <p class="text-[10px] text-zinc-500 font-medium">
                                            @if($hasOffer)
                                                <span>Efectivo/Transf. (MP: ${{ number_format($precioOferta, 0, ',', '.') }})</span>
                                            @else
                                                <span>Efectivo o Transferencia (10% OFF)</span>
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Botón Ámbar Sound Heritage "Sumar al carrito" -->
                                    <button 
                                        type="button" 
                                        wire:click="agregarAlCarrito({{ $p->id }})"
                                        class="btn w-full bg-[#D97724] hover:bg-[#c06517] text-white border-none font-bold text-xs uppercase tracking-wide rounded-xl flex items-center justify-center gap-2 mt-3 shadow-md hover:shadow-lg transition-all cursor-pointer h-10"
                                    >
                                        <x-mary-icon name="o-shopping-cart" class="w-4 h-4 text-white" />
                                        <span>Sumar al carrito</span>
                                    </button>

                                </div>

                            </div>
                        @empty
                            <div class="col-span-full py-16 text-center bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-8 space-y-4">
                                <x-mary-icon name="o-musical-note" class="w-16 h-16 text-zinc-300 dark:text-zinc-700 mx-auto" />
                                <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $soloOfertas ? 'No hay productos en oferta actualmente' : 'No se encontraron productos' }}
                                </h3>
                                <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                                    {{ $soloOfertas ? 'Pronto publicaremos nuevas promociones. Podés explorar todo el catálogo general.' : 'No hay instrumentos disponibles que coincidan con la búsqueda o filtros seleccionados.' }}
                                </p>
                                @if($soloOfertas)
                                    <a 
                                        href="{{ route('productos') }}" 
                                        class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none cursor-pointer"
                                    >
                                        Ver Catálogo General
                                    </a>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="limpiarTodo" 
                                        class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none cursor-pointer"
                                    >
                                        Restablecer filtros
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>

                <!-- PRODUCTOS: VISTA LISTA HORIZONTAL -->
                @else
                    <div class="space-y-4">
                        @forelse($productos as $p)
                            @php
                                $precioLista = (float) $p->price;
                                $hasOffer = ($p->discount_percentage && $p->discount_percentage > 0);
                                $precioOferta = (float) $p->offer_price;
                                $precioEfectivo = (float) $p->cash_price;
                                $ahorroTotal = (float) $p->total_cash_savings;
                                $imgSrc = !empty($p->image) ? asset($p->image) : (!empty($p->image_path) ? asset($p->image_path) : null);
                            @endphp

                            <div class="bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-xs hover:shadow-md transition-all flex flex-col sm:flex-row items-center justify-between gap-5 group">
                                
                                <div class="flex items-center gap-5 w-full sm:w-auto">
                                    <!-- Imagen con cartelito arriba a la izquierda -->
                                    <a href="{{ route('producto.detalle', $p->id) }}" class="block shrink-0">
                                        <div class="w-28 h-28 bg-white dark:bg-[#141414] rounded-xl flex items-center justify-center p-2 border border-zinc-100 dark:border-zinc-800 relative overflow-hidden cursor-pointer">
                                            @if($hasOffer)
                                                <div class="absolute top-1 left-1 z-10 bg-[#7E2B2D] text-white text-[9px] font-black px-1.5 py-0.2 rounded shadow-xs pointer-events-none">
                                                    -{{ $p->discount_percentage }}%
                                                </div>
                                            @endif
                                            @if($imgSrc)
                                                <img src="{{ $imgSrc }}" alt="{{ $p->name }}" class="max-h-full max-w-full object-contain" />
                                            @else
                                                <x-mary-icon name="o-musical-note" class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                            @endif
                                        </div>
                                    </a>

                                    <!-- Datos -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-bold uppercase text-zinc-400">{{ $p->brand ?? 'Sound Heritage' }}</span>
                                            <span class="text-[10px] text-zinc-400">| {{ $categorias[$p->type] ?? $p->type }}</span>
                                            @if($hasOffer)
                                                <span class="text-[9px] font-bold text-[#7E2B2D] bg-[#7E2B2D]/10 px-1.5 py-0.2 rounded border border-[#7E2B2D]/30">
                                                    Oferta {{ $p->discount_percentage }}% OFF
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ route('producto.detalle', $p->id) }}" class="block">
                                            <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100 hover:text-[#D97724] dark:hover:text-[#D97724] transition-colors mt-0.5 line-clamp-2">
                                                {{ $p->name }}
                                            </h3>
                                        </a>
                                        <p class="text-[11px] text-zinc-500 line-clamp-1 mt-1">
                                            Modelo: {{ $p->model ?? 'Estándar' }} &bull; Stock: {{ $p->stock }} u.
                                        </p>
                                    </div>
                                </div>

                                <!-- Precios y Botón a la Derecha -->
                                <div class="w-full sm:w-56 shrink-0 sm:text-right flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-3 sm:pt-0 border-zinc-100 dark:border-zinc-800">
                                    <div>
                                        <div class="flex items-center sm:justify-end gap-1.5">
                                            <span class="text-xs text-zinc-400 line-through">$ {{ number_format($precioLista, 0, ',', '.') }}</span>
                                            <span class="bg-[#D97724]/15 text-[#D97724] dark:bg-[#D97724]/25 font-black text-[10px] px-1.5 py-0.5 rounded-sm">-$ {{ number_format($ahorroTotal, 0, ',', '.') }}</span>
                                        </div>
                                        <p class="text-xl font-black text-zinc-950 dark:text-white mt-0.5">
                                            $ {{ number_format($precioEfectivo, 0, ',', '.') }}
                                        </p>
                                        <p class="text-[10px] text-zinc-500 font-medium">
                                            @if($hasOffer)
                                                <span>Efectivo/Transf. (MP: ${{ number_format($precioOferta, 0, ',', '.') }})</span>
                                            @else
                                                <span>Efectivo o Transferencia</span>
                                            @endif
                                        </p>
                                    </div>

                                    <button 
                                        type="button" 
                                        wire:click="agregarAlCarrito({{ $p->id }})"
                                        class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white border-none font-bold text-xs uppercase tracking-wide rounded-xl flex items-center justify-center gap-2 sm:mt-2 shadow-md cursor-pointer"
                                    >
                                        <x-mary-icon name="o-shopping-cart" class="w-4 h-4 text-white" />
                                        <span>Sumar</span>
                                    </button>
                                </div>

                            </div>
                        @empty
                            <div class="py-16 text-center bg-white dark:bg-[#181715] rounded-2xl border border-zinc-200/90 dark:border-zinc-800 p-8 space-y-4">
                                <x-mary-icon name="o-musical-note" class="w-16 h-16 text-zinc-300 dark:text-zinc-700 mx-auto" />
                                <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $soloOfertas ? 'No hay productos en oferta actualmente' : 'No se encontraron productos' }}
                                </h3>
                                @if($soloOfertas)
                                    <a href="{{ route('productos') }}" class="btn btn-sm bg-[#D97724] text-white rounded-xl font-bold uppercase text-xs border-none cursor-pointer">
                                        Ver Catálogo General
                                    </a>
                                @else
                                    <button type="button" wire:click="limpiarTodo" class="btn btn-sm bg-[#D97724] text-white rounded-xl font-bold uppercase text-xs border-none cursor-pointer">
                                        Restablecer filtros
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                @endif

                <!-- Paginación con Livewire -->
                <div class="pt-6">
                    {{ $productos->links() }}
                </div>

            </section>

        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- BOTÓN FLOTANTE VOLVER ARRIBA (ÁMBAR SOUND HERITAGE CON FLECHA SVG)        -->
    <!-- ========================================================================= -->
    <button 
        type="button" 
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })" 
        class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-[#D97724] hover:bg-[#c06517] text-white shadow-2xl flex items-center justify-center hover:scale-110 transition-all cursor-pointer border-none"
        title="Volver arriba"
    >
        <x-mary-icon name="o-arrow-up" class="w-5 h-5 text-white" />
    </button>
</div>
