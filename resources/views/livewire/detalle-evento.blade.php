<div
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>

    <!-- ========================================================================= -->
    <!-- 2. CONTENIDO PRINCIPAL: DETALLE DEL EVENTO                                -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
            <a href="/" class="hover:text-[#D97724] transition-colors">Inicio</a>
            <span>›</span>
            <a href="{{ route('eventos') }}" class="hover:text-[#D97724] transition-colors">Eventos en Vivo</a>
            <span>›</span>
            <span class="text-zinc-800 dark:text-zinc-200 font-bold truncate max-w-xs">{{ $evento->name }}</span>
        </nav>

        <!-- Contenedor de 2 Columnas -->
        <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-10 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- COLUMNA IZQUIERDA: AFICHE / FLYER DEL SHOW (58%) -->
            <div class="lg:col-span-7 flex flex-col items-center">
                <div class="w-full aspect-video sm:aspect-[4/3] bg-gradient-to-br from-[#181715] to-[#7E2B2D]/40 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 p-8 flex flex-col items-center justify-center relative overflow-hidden text-center text-white shadow-inner">
                    <!-- Icono Decorativo Central -->
                    <div class="w-20 h-20 rounded-full bg-[#D97724]/20 border border-[#D97724]/40 flex items-center justify-center mb-4">
                        <x-mary-icon name="o-musical-note" class="w-10 h-10 text-[#D97724]" />
                    </div>
                    
                    <h3 class="text-2xl sm:text-3xl font-black max-w-md leading-tight">
                        {{ $evento->name }}
                    </h3>

                    <!-- Etiqueta Flotante de Fecha -->
                    <div class="absolute top-4 right-4 bg-[#7E2B2D] text-white text-[11px] font-black px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                        <x-mary-icon name="o-calendar" class="w-3.5 h-3.5" />
                        <span>{{ \Carbon\Carbon::parse($evento->date)->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: DATOS, PRECIO Y ENTRADAS (42%) -->
            <div class="lg:col-span-5 space-y-6">
                
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#D97724]/10 text-[#D97724] text-xs font-black uppercase tracking-wider mb-2">
                        <x-mary-icon name="o-clock" class="w-3.5 h-3.5" />
                        <span>{{ substr($evento->time, 0, 5) }} hs · {{ \Carbon\Carbon::parse($evento->date)->translatedFormat('l d \d\e F') }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white leading-tight">
                        {{ $evento->name }}
                    </h1>
                </div>

                <!-- Tarjeta Financiera de Entrada -->
                <div class="bg-zinc-50 dark:bg-[#181715] border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 space-y-3">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block mb-0.5">
                            Valor de la entrada individual
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl sm:text-4xl font-black text-[#D97724]">
                                $ {{ number_format($evento->price, 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">
                            Pase nominal para 1 persona con acceso a sala y consumición de cortesía.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 text-xs text-zinc-500 flex items-center gap-2">
                        <x-mary-icon name="o-qr-code" class="w-4 h-4 text-[#D97724] shrink-0" />
                        <span>Entrada con código QR descargable e imprimible tras la compra.</span>
                    </div>
                </div>

                <!-- Pautas del Evento -->
                <div class="space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-sparkles" class="w-4 h-4 text-[#D97724] shrink-0" />
                        <span><strong>Apertura de puertas:</strong> 45 minutos antes de la hora indicada.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-user-group" class="w-4 h-4 text-[#D97724] shrink-0" />
                        <span><strong>Ubicaciones:</strong> Asientos por orden de llegada con excelente acústica de sala.</span>
                    </div>
                </div>

                <!-- Botón CTA Añadir al Carrito -->
                @if($disponibles > 0)
                    <div class="pt-2">
                        <button 
                            type="button"
                            wire:click="agregarAlCarrito"
                            class="w-full h-12 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#D97724]/20 transition-all duration-150 cursor-pointer"
                        >
                            <x-mary-icon name="o-shopping-cart" class="w-5 h-5 shrink-0" />
                            <span>Añadir al carrito</span>
                        </button>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-center text-xs font-bold text-zinc-500">
                        Localidades agotadas.
                    </div>
                @endif

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. SECCIÓN DE INFORMACIÓN DEL SHOW (ESTILO COMPRAGAMER - CARDS)           -->
        <!-- ========================================================================= -->
        <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-10 shadow-sm space-y-6">
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
                <div class="inline-flex items-center gap-2 pb-2 border-b-2 border-[#D97724] text-xs font-black uppercase tracking-wider text-[#D97724]">
                    <x-mary-icon name="o-document-text" class="w-4 h-4" />
                    <span>Información de la Función</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Reseña del Evento -->
                <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 space-y-2">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#D97724]">Sinopsis y Formato</h4>
                    <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed font-medium">
                        {{ $evento->description ?: 'Ciclo de presentaciones musicales en vivo en el auditorio acústico de Sound Heritage. Amplificación valvular y equipamiento analógico de primera línea.' }}
                    </p>
                </div>

                <!-- Equipamiento y Sala -->
                <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 space-y-2">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#D97724]">Auditorio y Confort</h4>
                    <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed font-medium">
                        Ambiente climatizado en el Auditorio Sound Heritage, tratamiento acústico profesional y servicio de cafetería de especialidad en el foyer. Estacionamiento privado adherido en las inmediaciones.
                    </p>
                </div>
            </div>
        </div>

    </main>
</div>

