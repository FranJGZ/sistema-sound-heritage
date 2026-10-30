<div 
    x-data="{ 
        activeImage: '{{ asset($producto->image ?? $producto->image_path ?? 'img/LogoTiendaNegro.png') }}'
    }"
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>

    <!-- ========================================================================= -->
    <!-- 2. CONTENIDO PRINCIPAL: DETALLE DEL PRODUCTO ESTILO COMPRAGAMER           -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8">
        
        <!-- Breadcrumbs Sutiles -->
        <nav class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
            <a href="/" class="hover:text-[#D97724] transition-colors">Inicio</a>
            <span>›</span>
            <a href="{{ route('productos') }}" class="hover:text-[#D97724] transition-colors">Productos</a>
            <span>›</span>
            <a href="{{ route('productos', ['categoria' => $producto->type]) }}" class="hover:text-[#D97724] transition-colors capitalize">
                {{ $producto->type }}
            </a>
            <span>›</span>
            <span class="text-zinc-800 dark:text-zinc-200 font-bold truncate max-w-xs">{{ $producto->name }}</span>
        </nav>

        <!-- Contenedor de 2 Columnas (Layout CompraGamer) -->
        <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-10 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- COLUMNA IZQUIERDA: GALERÍA DE IMÁGENES (58%) -->
            <div class="lg:col-span-7 flex flex-col items-center">
                
                <!-- Visor de Imagen Principal -->
                <div class="w-full aspect-square max-h-[500px] bg-zinc-50 dark:bg-[#181715] rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-8 flex items-center justify-center relative overflow-hidden group">
                    <img 
                        :src="activeImage" 
                        alt="{{ $producto->name }}" 
                        class="max-h-full max-w-full object-contain transition-all duration-300 group-hover:scale-105 select-none"
                    />

                    @if($producto->discount_percentage > 0)
                        <span class="absolute top-4 left-4 bg-[#7E2B2D] text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-md">
                            {{ $producto->discount_percentage }}% OFF
                        </span>
                    @endif
                </div>

                <!-- Tira de Miniaturas Interactivas -->
                @php
                    $imgSrc = asset($producto->image ?? $producto->image_path ?? 'img/LogoTiendaNegro.png');
                @endphp
                <div class="flex items-center gap-3 mt-6">
                    <button 
                        type="button" 
                        @click="activeImage = '{{ $imgSrc }}'"
                        :class="activeImage === '{{ $imgSrc }}' ? 'border-[#D97724] ring-2 ring-[#D97724]/20' : 'border-zinc-200 dark:border-zinc-800 opacity-60 hover:opacity-100'"
                        class="w-16 h-16 rounded-xl border bg-zinc-50 dark:bg-[#181715] p-2 flex items-center justify-center transition-all cursor-pointer"
                        title="Vista frontal"
                    >
                        <img src="{{ $imgSrc }}" alt="{{ $producto->name }}" class="max-h-full max-w-full object-contain" />
                    </button>
                    <!-- Miniatura Detalle / Escala -->
                    <button 
                        type="button" 
                        @click="activeImage = '{{ $imgSrc }}'"
                        class="w-16 h-16 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-[#181715] p-2 flex items-center justify-center opacity-60 hover:opacity-100 transition-all cursor-pointer"
                        title="Detalle"
                    >
                        <img src="{{ $imgSrc }}" alt="Detalle" class="max-h-full max-w-full object-contain scale-125" />
                    </button>
                </div>
            </div>

            <!-- COLUMNA DERECHA: INFORMACIÓN, PRECIOS Y CTA (42%) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Encabezado del Producto -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#D97724] mb-1.5">
                        <span>{{ $producto->brand }}</span>
                        <span>·</span>
                        <span>{{ $producto->model ?? 'Gama Profesional' }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white leading-tight">
                        {{ $producto->name }}
                    </h1>

                    <div class="flex items-center gap-3 mt-2 text-xs text-zinc-400">
                        <span class="bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 rounded-md font-mono">
                            SKU: SH-{{ str_pad($producto->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>ID: {{ $producto->id }}</span>
                    </div>
                </div>

                <!-- Tarjeta Financiera de Precios Estilo Sound Heritage -->
                @php
                    $hasOffer = ($producto->discount_percentage && $producto->discount_percentage > 0);
                    $listPrice = (float) $producto->price;
                    $offerPrice = (float) $producto->offer_price;
                    $cashPrice = (float) $producto->cash_price;
                    $totalSavings = (float) $producto->total_cash_savings;
                @endphp
                <div class="bg-zinc-50 dark:bg-[#181715] border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 space-y-3">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block mb-0.5">
                            Precio especial efectivo / transferencia
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl sm:text-4xl font-black text-[#D97724]">
                                $ {{ number_format($cashPrice, 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-1.5 flex items-center gap-1.5">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 shrink-0" />
                            @if($hasOffer)
                                <span>10% extra en Efectivo/Transferencia sobre la Oferta (Ahorro total: ${{ number_format($totalSavings, 0, ',', '.') }})</span>
                            @else
                                <span>10% OFF pagando en Efectivo o Transferencia Bancaria</span>
                            @endif
                        </p>
                    </div>

                    @if($hasOffer)
                        <div class="pt-2.5 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-zinc-500">Precio con Oferta (Mercado Pago / Tarjeta):</span>
                                <span class="font-extrabold text-zinc-900 dark:text-zinc-100">$ {{ number_format($offerPrice, 0, ',', '.') }}</span>
                            </div>
                            <span class="bg-[#7E2B2D] text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                {{ $producto->discount_percentage }}% OFF
                            </span>
                        </div>
                    @endif

                    <div class="pt-2.5 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500">
                        <div>
                            <span>Precio de lista en cuotas:</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200 ml-1 line-through">$ {{ number_format($listPrice, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-[#D97724] font-bold">Hasta 6 cuotas fijas</span>
                    </div>
                </div>

                <!-- Disponibilidad y Stock -->
                <div class="space-y-2">
                    @if($producto->stock > 0)
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Stock disponible ({{ $producto->stock }} {{ $producto->stock == 1 ? 'unidad' : 'unidades' }} en depósito)</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-500">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Sin stock por el momento</span>
                        </div>
                    @endif

                    <!-- Beneficios de Garantía y Entrega -->
                    <div class="space-y-2 text-xs text-zinc-600 dark:text-zinc-400 pt-2 border-t border-zinc-100 dark:border-zinc-800/60">
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-shield-check" class="w-4 h-4 text-[#D97724] shrink-0" />
                            <span><strong>Garantía oficial Sound Heritage:</strong> 6 meses de respaldo directo.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-truck" class="w-4 h-4 text-[#D97724] shrink-0" />
                            <span><strong>Envíos a todo el país</strong> o retiro sin cargo en nuestro local de Calle Colón 1234, Posadas.</span>
                        </div>
                    </div>
                </div>

                <!-- Selector de Cantidad y Botón CTA Ámbar -->
                @if($producto->stock > 0)
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-4">
                            <!-- Selector Cantidad [-] Q [+] -->
                            <div 
                                x-data="{ 
                                    qty: @entangle('cantidad').live,
                                    maxStock: {{ max(1, (int) $producto->stock) }},
                                    inc() { if (this.qty < this.maxStock) { this.qty++; } },
                                    dec() { if (this.qty > 1) { this.qty--; } }
                                }"
                                class="flex items-center border border-zinc-300 dark:border-zinc-700 rounded-xl overflow-hidden h-12 bg-zinc-50 dark:bg-zinc-900 w-32 shrink-0"
                            >
                                <button 
                                    type="button"
                                    @click="dec()"
                                    class="w-10 h-full flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-800 text-sm font-bold text-zinc-700 dark:text-zinc-300 transition-colors"
                                >
                                    -
                                </button>
                                <span class="flex-1 text-center font-bold text-sm text-zinc-900 dark:text-white" x-text="qty">
                                    {{ $cantidad }}
                                </span>
                                <button 
                                    type="button"
                                    @click="inc()"
                                    class="w-10 h-full flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-800 text-sm font-bold text-zinc-700 dark:text-zinc-300 transition-colors"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Botón Sumar al Carrito -->
                            <button 
                                type="button"
                                wire:click="agregarAlCarrito"
                                class="flex-1 h-12 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#D97724]/20 transition-all duration-150 cursor-pointer"
                            >
                                <x-mary-icon name="o-shopping-bag" class="w-5 h-5 shrink-0" />
                                <span>Sumar al Carrito</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-center text-xs font-bold text-zinc-500">
                        Producto temporalmente agotado.
                    </div>
                @endif

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. SECCIÓN DE ESPECIFICACIONES TÉCNICAS (ESTILO COMPRAGAMER - CARDS)        -->
        <!-- ========================================================================= -->
        <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-10 shadow-sm space-y-6">
            
            <!-- Pestaña Activa -->
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
                <div class="inline-flex items-center gap-2 pb-2 border-b-2 border-[#D97724] text-xs font-black uppercase tracking-wider text-[#D97724]">
                    <x-mary-icon name="o-adjustments-horizontal" class="w-4 h-4" />
                    <span>Especificaciones Técnicas</span>
                </div>
            </div>

            <!-- Grilla de Especificaciones -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Tarjeta Marca -->
                <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#D97724]/10 text-[#D97724] flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-tag" class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Marca</span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ $producto->brand }}</span>
                    </div>
                </div>

                <!-- Tarjeta Modelo -->
                <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#D97724]/10 text-[#D97724] flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-musical-note" class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Modelo</span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ $producto->model ?? 'Standard' }}</span>
                    </div>
                </div>

                <!-- Tarjeta Categoría -->
                <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#D97724]/10 text-[#D97724] flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-folder" class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Categoría</span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 capitalize">{{ $producto->type }}</span>
                    </div>
                </div>

                <!-- Especificaciones Dinámicas parsed from JSON/Array -->
                @if(!empty($producto->specs) && is_array($producto->specs))
                    @foreach($producto->specs as $key => $val)
                        @if(!empty($val))
                            <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-[#7E2B2D]/10 text-[#7E2B2D] dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <x-mary-icon name="o-check" class="w-5 h-5" />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">
                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                    </span>
                                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ is_array($val) ? implode(', ', $val) : $val }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

    </main>
</div>

