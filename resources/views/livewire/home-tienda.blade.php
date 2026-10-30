<div 
    x-data="{ 
        currentHeroSlide: 0,
        destacadosSlide: 0,
        currentEventsSlide: 0,
        heroTimer: null,
        eventsTimer: null,
        initHero() {
            this.heroTimer = setInterval(() => {
                this.currentHeroSlide = (this.currentHeroSlide + 1) % 3;
            }, 5000);
        },
        stopHero() {
            if (this.heroTimer) clearInterval(this.heroTimer);
        },
        startHero() {
            this.stopHero();
            this.heroTimer = setInterval(() => {
                this.currentHeroSlide = (this.currentHeroSlide + 1) % 3;
            }, 5000);
        },
        initEvents() {
            this.eventsTimer = setInterval(() => {
                this.currentEventsSlide = (this.currentEventsSlide + 1) % 3;
            }, 5000);
        },
        stopEvents() {
            if (this.eventsTimer) clearInterval(this.eventsTimer);
        },
        startEvents() {
            this.stopEvents();
            this.eventsTimer = setInterval(() => {
                this.currentEventsSlide = (this.currentEventsSlide + 1) % 3;
            }, 5000);
        }
    }" 
    x-init="initHero(); initEvents()"
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>
    <!-- ESTILOS GLOBALES DE ANIMACIÓN Y MARQUESINA -->
    <style>
        @keyframes shMarqueeLeft {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .sh-marquee-track {
            display: flex;
            width: max-content;
            animation: shMarqueeLeft 35s linear infinite;
        }
        .sh-marquee-track:hover {
            animation-play-state: paused;
        }
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @if(request()->query('verified') == '1' || request()->query('google_logged_in') == '1')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('verified');
                    url.searchParams.delete('google_logged_in');
                    window.history.replaceState({}, document.title, url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : ''));
                }, 1500);
            });
        </script>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. CUERPO PRINCIPAL                                                       -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        <!-- ===================================================================== -->
        <!-- HERO SWIPER PANORÁMICO (DEGRADÉ OXBLOOD #5A1820 HACIA #181715)        -->
        <!-- ===================================================================== -->
        <div 
            @mouseenter="stopHero()"
            @mouseleave="startHero()"
            class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-[#5A1820] via-[#331418] to-[#181715] text-white shadow-2xl h-[360px] sm:h-[400px] group"
        >
            <!-- SLIDE 0: GUITARRAS & BAJOS DE COLECCIÓN -->
            <div 
                x-show="currentHeroSlide === 0" 
                x-transition:enter="transition-opacity duration-700 ease-in-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-700 ease-in-out"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="currentHeroSlide === 0 ? 'z-10 pointer-events-auto' : 'z-0 pointer-events-none'"
                class="absolute inset-0 w-full h-full flex flex-col md:flex-row items-center justify-between"
            >
                <div class="max-w-xl p-6 sm:p-12 space-y-3 z-10 text-left">
                    <span class="inline-block bg-[#7E2B2D] text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                        Herencia Sonora &amp; Luthería
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
                        Herencia Sonora: Colección Vintage &amp; Espacio Abierto
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed drop-shadow-sm max-w-md">
                        Instrumentos legendarios con historia, calibración artesanal y un punto de encuentro para músicos.
                    </p>
                    <div class="pt-2">
                        <button 
                            wire:click="seleccionarCategoria('cuerda')" 
                            class="btn bg-[#D97724] hover:bg-[#c06517] text-white font-extrabold uppercase text-xs rounded-full px-7 border-none shadow-lg shadow-[#D97724]/30 cursor-pointer transition-all"
                        >
                            Explorar Instrumentos →
                        </button>
                    </div>
                </div>

                <!-- Composición Fotográfica Real Cálida (Instrumentos Vintage & Maderas) -->
                <div class="w-full md:w-7/12 h-full absolute md:relative right-0 top-0 bottom-0 overflow-hidden flex items-center justify-end">
                    <img 
                        src="{{ asset('img/hero/hero_slide_0.jpg') }}" 
                        alt="Herencia Sonora Luthería Vintage" 
                        class="w-full h-full object-cover object-center transform scale-105 group-hover:scale-100 transition-transform duration-1000 ease-out"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#5A1820] via-[#5A1820]/75 to-transparent hidden md:block"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#181715] via-[#181715]/80 to-transparent md:hidden"></div>
                </div>
            </div>

            <!-- SLIDE 1: TECLADOS & PIANOS -->
            <div 
                x-show="currentHeroSlide === 1" 
                x-transition:enter="transition-opacity duration-700 ease-in-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-700 ease-in-out"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="currentHeroSlide === 1 ? 'z-10 pointer-events-auto' : 'z-0 pointer-events-none'"
                class="absolute inset-0 w-full h-full flex flex-col md:flex-row items-center justify-between"
            >
                <div class="max-w-xl p-6 sm:p-12 space-y-3 z-10 text-left">
                    <span class="inline-block bg-[#7E2B2D] text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                        Síntesis &amp; Teclas
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
                        Pianos de Escenario &amp; Sintetizadores
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed drop-shadow-sm max-w-md">
                        Pianos de 88 teclas contrapesadas, módulos analógicos y workstations listos para escenario y estudio de grabación.
                    </p>
                    <div class="pt-2">
                        <button 
                            wire:click="seleccionarCategoria('teclado')" 
                            class="btn bg-[#D97724] hover:bg-[#c06517] text-white font-extrabold uppercase text-xs rounded-full px-7 border-none shadow-lg shadow-[#D97724]/30 cursor-pointer transition-all"
                        >
                            Explorar Teclados →
                        </button>
                    </div>
                </div>

                <div class="w-full md:w-7/12 h-full absolute md:relative right-0 top-0 bottom-0 overflow-hidden flex items-center justify-end">
                    <img 
                        src="{{ asset('img/hero/hero_slide_1.jpg') }}" 
                        alt="Pianos y Sintetizadores en Estudio" 
                        class="w-full h-full object-cover object-center transform scale-105 group-hover:scale-100 transition-transform duration-1000 ease-out"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#5A1820] via-[#5A1820]/75 to-transparent hidden md:block"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#181715] via-[#181715]/80 to-transparent md:hidden"></div>
                </div>
            </div>

            <!-- SLIDE 2: AUDIO & ESTUDIO -->
            <div 
                x-show="currentHeroSlide === 2" 
                x-transition:enter="transition-opacity duration-700 ease-in-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-700 ease-in-out"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="currentHeroSlide === 2 ? 'z-10 pointer-events-auto' : 'z-0 pointer-events-none'"
                class="absolute inset-0 w-full h-full flex flex-col md:flex-row items-center justify-between"
            >
                <div class="max-w-xl p-6 sm:p-12 space-y-3 z-10 text-left">
                    <span class="inline-block bg-[#7E2B2D] text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                        Captura &amp; Monitoreo
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
                        Microfonía de Estudio &amp; Grabación
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed drop-shadow-sm max-w-md">
                        Cápsulas de condensador, válvulas de preamplificación y monitoreo acústico de alta fidelidad.
                    </p>
                    <div class="pt-2">
                        <button 
                            wire:click="seleccionarCategoria('microfono')" 
                            class="btn bg-[#D97724] hover:bg-[#c06517] text-white font-extrabold uppercase text-xs rounded-full px-7 border-none shadow-lg shadow-[#D97724]/30 cursor-pointer transition-all"
                        >
                            Ver Micrófonos →
                        </button>
                    </div>
                </div>

                <div class="w-full md:w-7/12 h-full absolute md:relative right-0 top-0 bottom-0 overflow-hidden flex items-center justify-end">
                    <img 
                        src="{{ asset('img/hero/hero_slide_2.jpg') }}" 
                        alt="Microfonía y Grabación en Estudio" 
                        class="w-full h-full object-cover object-center transform scale-105 group-hover:scale-100 transition-transform duration-1000 ease-out"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#5A1820] via-[#5A1820]/75 to-transparent hidden md:block"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#181715] via-[#181715]/80 to-transparent md:hidden"></div>
                </div>
            </div>

            <!-- Flechas Flotantes Laterales del Swiper -->
            <button 
                type="button"
                @click="stopHero(); currentHeroSlide = (currentHeroSlide === 0 ? 2 : currentHeroSlide - 1); startHero()" 
                class="absolute left-4 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-[#D97724] text-white flex items-center justify-center text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity z-20 cursor-pointer shadow-lg"
                title="Slide anterior"
            >
                ‹
            </button>
            <button 
                type="button"
                @click="stopHero(); currentHeroSlide = (currentHeroSlide + 1) % 3; startHero()" 
                class="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-[#D97724] text-white flex items-center justify-center text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity z-20 cursor-pointer shadow-lg"
                title="Siguiente slide"
            >
                ›
            </button>

            <!-- Cápsulas de Paginación Sincronizadas -->
            <div class="absolute bottom-4 left-0 right-0 flex items-center justify-center gap-2 z-20">
                <button @click="stopHero(); currentHeroSlide = 0; startHero()" :class="currentHeroSlide === 0 ? 'w-7 h-2 bg-[#D97724]' : 'w-2 h-2 bg-zinc-600 hover:bg-zinc-400'" class="rounded-full transition-all duration-300 cursor-pointer"></button>
                <button @click="stopHero(); currentHeroSlide = 1; startHero()" :class="currentHeroSlide === 1 ? 'w-7 h-2 bg-[#D97724]' : 'w-2 h-2 bg-zinc-600 hover:bg-zinc-400'" class="rounded-full transition-all duration-300 cursor-pointer"></button>
                <button @click="stopHero(); currentHeroSlide = 2; startHero()" :class="currentHeroSlide === 2 ? 'w-7 h-2 bg-[#D97724]' : 'w-2 h-2 bg-zinc-600 hover:bg-zinc-400'" class="rounded-full transition-all duration-300 cursor-pointer"></button>
            </div>
        </div>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 1: PRODUCTOS DESTACADOS (SWIPER 18 PRODS: 3 SLIDES DE 6 EN 2x3)   -->
        <!-- ========================================================================= -->
        <section id="destacados" class="space-y-4">
            @php
                $slides = $productosDestacados->chunk(6);
            @endphp

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        Conocé nuestros <span class="font-black text-[#D97724]">Productos Destacados</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Catálogo con calibración oficial y precio de lista transparente.</p>
                </div>

                <!-- Flechas funcionales del Swiper de Destacados -->
                @if($slides->count() > 1)
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            @click="destacadosSlide = (destacadosSlide === 0 ? {{ $slides->count() - 1 }} : destacadosSlide - 1)" 
                            class="w-9 h-9 rounded-xl border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer shadow-xs"
                            title="Slide anterior"
                        >
                            ‹
                        </button>
                        <button 
                            type="button"
                            @click="destacadosSlide = (destacadosSlide + 1) % {{ $slides->count() }}" 
                            class="w-9 h-9 rounded-xl border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer shadow-xs"
                            title="Siguiente slide"
                        >
                            ›
                        </button>
                    </div>
                @endif
            </div>

            <!-- Píldoras de Filtro (Solo Categorías Reales) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none whitespace-nowrap">
                @foreach($categorias as $key => $label)
                    <button 
                        type="button" 
                        wire:click="seleccionarCategoria('{{ $key }}')"
                        @click="destacadosSlide = 0"
                        class="px-5 py-2 rounded-full text-xs font-bold transition-all cursor-pointer {{ $selectedCategory === $key ? 'bg-[#D97724] text-white shadow-md' : 'bg-white dark:bg-[#201E1C] text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-300 dark:border-zinc-800' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Swiper de Slides (6 Productos por Slide en 2 Filas x 3 Columnas) -->
            <div class="relative overflow-hidden min-h-[240px]">
                @forelse($slides as $slideIndex => $chunk)
                    <div 
                        x-show="destacadosSlide === {{ $slideIndex }}"
                        x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-3"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
                    >
                        @foreach($chunk as $producto)
                            <!-- Tarjeta Apaisada / "Aplastada" Estilo CompraGamer (media_1792824871572_5d1b7753.png) -->
                            <a 
                                href="/producto/{{ $producto->id }}" 
                                class="group bg-white dark:bg-[#201E1C] rounded-2xl p-3 border border-zinc-200/90 dark:border-zinc-800 shadow-xs flex items-center gap-3.5 hover:border-[#D97724] dark:hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer relative"
                            >
                                <!-- Imagen en contenedor cuadrado contenido (~84px) -->
                                <div class="w-20 h-20 sm:w-22 sm:h-22 shrink-0 flex items-center justify-center p-2 rounded-xl bg-zinc-50 dark:bg-[#181715] border border-zinc-100 dark:border-zinc-800/80 group-hover:scale-105 transition-transform">
                                    @if(!empty($producto->image))
                                        <img src="{{ asset($producto->image) }}" alt="{{ $producto->name }}" class="max-h-full max-w-full object-contain" />
                                    @elseif(!empty($producto->image_path))
                                        <img src="{{ asset($producto->image_path) }}" alt="{{ $producto->name }}" class="max-h-full max-w-full object-contain" />
                                    @else
                                        <x-mary-icon name="o-musical-note" class="w-7 h-7 text-[#D97724]" />
                                    @endif
                                </div>

                                <!-- Datos y Precios a la Derecha (Horizontal / Aplastado) -->
                                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                    <div>
                                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block truncate">
                                            {{ $producto->brand ?? 'SOUND HERITAGE' }}
                                        </span>
                                        <h3 class="text-xs sm:text-[13px] font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-2 leading-snug mt-0.5 group-hover:text-[#D97724] transition-colors" title="{{ $producto->name }}">
                                            {{ $producto->name }}
                                        </h3>
                                    </div>

                                    <!-- Precios Legales Transparentes -->
                                    <div class="mt-2 pt-1 border-t border-zinc-100 dark:border-zinc-800/60">
                                        <div class="flex items-baseline gap-1.5 flex-wrap">
                                            <span class="text-[10px] text-zinc-400 line-through">
                                                ${{ number_format($producto->price, 0, ',', '.') }}
                                            </span>
                                            @if($producto->discount_percentage > 0)
                                                <span class="bg-[#7E2B2D] text-white text-[8px] font-black px-1.5 py-0.2 rounded">
                                                    {{ $producto->discount_percentage }}% OFF
                                                </span>
                                            @else
                                                <span class="bg-[#7E2B2D] text-white text-[8px] font-black px-1.5 py-0.2 rounded">
                                                    10% OFF
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-sm sm:text-base font-black text-[#D97724] block mt-0.5">
                                            $ {{ number_format($producto->cash_price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center bg-white dark:bg-[#201E1C] rounded-[22px] border border-zinc-200 dark:border-zinc-800 p-6">
                        <p class="text-xs font-bold text-zinc-600 dark:text-zinc-400">No encontramos productos en esta categoría.</p>
                        <button wire:click="seleccionarCategoria('cuerda')" class="btn btn-xs bg-[#D97724] text-white rounded-full mt-3 font-bold uppercase border-none">
                            Ver cuerdas
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Cápsulas de Paginación del Swiper (Sincronizadas abajo como el Hero) -->
            @if($slides->count() > 1)
                <div class="flex items-center justify-center gap-2 pt-2">
                    @for($i = 0; $i < $slides->count(); $i++)
                        <button 
                            type="button"
                            @click="destacadosSlide = {{ $i }}" 
                            :class="destacadosSlide === {{ $i }} ? 'w-7 h-2 bg-[#D97724]' : 'w-2 h-2 bg-zinc-400 dark:bg-zinc-700 hover:bg-zinc-500'" 
                            class="rounded-full transition-all duration-300 cursor-pointer"
                            title="Slide {{ $i + 1 }}"
                        ></button>
                    @endfor
                </div>
            @endif
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 2: CATEGORÍAS (BENTO GRID CON ICONOS SVG PROFESIONALES)           -->
        <!-- ========================================================================= -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">
                    Explorá nuestras <span class="font-black text-[#D97724]">Categorías</span>
                </h2>
                <div class="flex items-center gap-2">
                    <button class="w-8 h-8 rounded-lg border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">&lt;</button>
                    <button class="w-8 h-8 rounded-lg border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">&gt;</button>
                </div>
            </div>

            <!-- Bento Grid con Iconos Vectoriales Profesionales (Cero Emojis) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- Tarjeta Alta Izquierda: Instrumentos de Cuerda -->
                <a 
                    href="{{ route('productos', ['categoria' => 'cuerda']) }}" 
                    class="md:col-span-1 rounded-[22px] overflow-hidden relative shadow-md cursor-pointer group min-h-[280px] md:min-h-[320px] flex flex-col justify-end p-5 border border-[#7E2B2D]/60 hover:border-[#D97724] transition-all text-white"
                >
                    <img 
                        src="{{ asset('img/categories/cuerda.jpg') }}" 
                        alt="Instrumentos de Cuerda" 
                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" 
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/55 to-black/30 group-hover:via-black/45 transition-all"></div>

                    <div class="relative z-10">
                        <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">
                            INSTRUMENTOS DE CUERDA
                        </span>
                    </div>
                </a>

                <!-- Grilla Derecha: 8 Tarjetas -->
                <div class="md:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    
                    <!-- 1. Teclados & Pianos -->
                    <a href="{{ route('productos', ['categoria' => 'teclado']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/teclado.jpg') }}" alt="Teclados & Pianos" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">TECLADOS &amp; PIANOS</span>
                        </div>
                    </a>

                    <!-- 2. Baterías & Percusión -->
                    <a href="{{ route('productos', ['categoria' => 'percusion']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/percusion.jpg') }}" alt="Baterías & Percusión" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">BATERÍAS &amp; PERCUSIÓN</span>
                        </div>
                    </a>

                    <!-- 3. Amplificadores -->
                    <a href="{{ route('productos', ['categoria' => 'amplificacion']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/amplificacion.jpg') }}" alt="Amplificadores" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">AMPLIFICADORES</span>
                        </div>
                    </a>

                    <!-- 4. Audio & Estudio -->
                    <a href="{{ route('productos', ['categoria' => 'procesador_interfase']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/procesador_interfase.jpg') }}" alt="Audio & Estudio" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">AUDIO &amp; ESTUDIO</span>
                        </div>
                    </a>

                    <!-- 5. Microfonía -->
                    <a href="{{ route('productos', ['categoria' => 'microfono']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/microfono.jpg') }}" alt="Microfonía" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">MICROFONÍA</span>
                        </div>
                    </a>

                    <!-- 6. Vientos -->
                    <a href="{{ route('productos', ['categoria' => 'viento']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/viento.jpg') }}" alt="Vientos" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">VIENTOS</span>
                        </div>
                    </a>

                    <!-- 7. Accesorios -->
                    <a href="{{ route('productos', ['categoria' => 'accesorio']) }}" class="rounded-[20px] overflow-hidden relative p-4 border border-zinc-700/60 shadow-xs cursor-pointer group flex flex-col justify-end h-36 hover:border-[#D97724] transition-all text-white">
                        <img src="{{ asset('img/categories/accesorio.jpg') }}" alt="Accesorios" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/35 group-hover:via-black/45 transition-all"></div>
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block drop-shadow-sm">ACCESORIOS</span>
                        </div>
                    </a>

                    <!-- 8. Ver Catálogo Completo -->
                    <a href="{{ route('productos') }}" class="rounded-[20px] p-4 bg-[#D97724] text-white shadow-md cursor-pointer group flex flex-col justify-end h-36 hover:bg-[#c06517] transition-all">
                        <div class="relative z-10">
                            <span class="text-[11px] font-black uppercase tracking-wider text-white block">VER CATÁLOGO</span>
                        </div>
                    </a>

                </div>
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 3: NOVEDADES & NUEVOS INGRESOS (SIN BADGES NI BOTONES DE COMPRA)   -->
        <!-- ========================================================================= -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        Últimas <span class="font-black text-[#D97724]">Novedades</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Nuevos ingresos de instrumentos de colección y audio vintage.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ultimasNovedades as $nov)
                    <a 
                        href="/producto/{{ $nov->id }}" 
                        class="group bg-white dark:bg-[#201E1C] rounded-[22px] p-4 border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex items-center gap-4 hover:border-[#D97724] transition-all relative cursor-pointer"
                    >
                        <div class="w-20 h-20 shrink-0 flex items-center justify-center bg-zinc-50 dark:bg-[#181715] p-2 rounded-xl border border-zinc-100 dark:border-zinc-800 group-hover:scale-105 transition-transform">
                            @if(!empty($nov->image))
                                <img src="{{ asset($nov->image) }}" alt="{{ $nov->name }}" class="max-h-full max-w-full object-contain" />
                            @elseif(!empty($nov->image_path))
                                <img src="{{ asset($nov->image_path) }}" alt="{{ $nov->name }}" class="max-h-full max-w-full object-contain" />
                            @else
                                <x-mary-icon name="o-sparkles" class="w-6 h-6 text-[#D97724]" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">
                                {{ $nov->brand ?? 'SOUND HERITAGE' }}
                            </span>
                            <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mt-0.5 group-hover:text-[#D97724] transition-colors">{{ $nov->name }}</h4>
                            <div class="mt-2">
                                <span class="text-xs text-zinc-400 line-through block font-medium">${{ number_format($nov->price, 0, ',', '.') }}</span>
                                <span class="text-sm font-black text-[#D97724] block mt-0.5">$ {{ number_format($nov->price * 0.90, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 4: PRÓXIMOS EVENTOS (SWIPER 9 SHOWS: 3 SLIDES DE 3 EN 1 FILA)    -->
        <!-- ========================================================================= -->
        <section id="eventos" class="space-y-4">
            @php
                $eventSlides = $eventos->chunk(3);
            @endphp

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        Cartelera de la Casa: <span class="font-black text-[#D97724]">Bandas en Vivo</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Recitales y shows íntimos en el Live Room de Sound Heritage.</p>
                </div>

                <!-- Flechas funcionales del Swiper de Eventos -->
                @if($eventSlides->count() > 1)
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            @click="stopEvents(); currentEventsSlide = (currentEventsSlide === 0 ? {{ $eventSlides->count() - 1 }} : currentEventsSlide - 1); startEvents()" 
                            class="w-9 h-9 rounded-xl border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer shadow-xs"
                            title="Slide anterior"
                        >
                            ‹
                        </button>
                        <button 
                            type="button"
                            @click="stopEvents(); currentEventsSlide = (currentEventsSlide + 1) % {{ $eventSlides->count() }}; startEvents()" 
                            class="w-9 h-9 rounded-xl border border-[#D97724] text-[#D97724] hover:bg-[#D97724] hover:text-white flex items-center justify-center font-bold text-sm transition-colors cursor-pointer shadow-xs"
                            title="Siguiente slide"
                        >
                            ›
                        </button>
                    </div>
                @endif
            </div>

            <!-- Contenedor del Swiper con Rotación Automática en Loop -->
            <div 
                @mouseenter="stopEvents()" 
                @mouseleave="startEvents()" 
                class="relative overflow-hidden min-h-[175px]"
            >
                @forelse($eventSlides as $slideIndex => $chunk)
                    <div 
                        x-show="currentEventsSlide === {{ $slideIndex }}"
                        x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-3"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5"
                    >
                        @foreach($chunk as $ev)
                            <div class="bg-white dark:bg-[#201E1C] rounded-2xl p-4 border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between hover:border-[#D97724] transition-all group">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-bold text-zinc-500 bg-zinc-100 dark:bg-zinc-800/80 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                            <x-mary-icon name="o-calendar" class="w-3.5 h-3.5 text-[#D97724]" />
                                            {{ \Carbon\Carbon::parse($ev->date)->translatedFormat('d M, Y') }}
                                        </span>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-1">
                                            <x-mary-icon name="o-clock" class="w-3.5 h-3.5 text-[#D97724] shrink-0" />
                                            <span>{{ \Carbon\Carbon::parse($ev->time)->format('H:i') }} hs</span>
                                        </span>
                                    </div>

                                    <a href="{{ route('evento.detalle', $ev->id) }}" class="block">
                                        <h3 class="text-sm sm:text-[15px] font-bold text-zinc-900 dark:text-white mt-2 group-hover:text-[#D97724] transition-colors leading-snug line-clamp-1" title="{{ $ev->name }}">
                                            {{ $ev->name }}
                                        </h3>
                                    </a>
                                    
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1" title="{{ $ev->description }}">
                                        {{ $ev->description ?? 'Encuentro cultural en vivo con acústica pura.' }}
                                    </p>
                                </div>

                                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800/80 mt-3 flex items-center justify-between">
                                    <div>
                                        <span class="text-[9px] text-zinc-400 block font-semibold uppercase tracking-wider">Entrada</span>
                                        <span class="text-sm font-black text-zinc-900 dark:text-white">
                                            @if($ev->price > 0)
                                                $ {{ number_format($ev->price, 0, ',', '.') }}
                                            @else
                                                Entrada Libre
                                            @endif
                                        </span>
                                    </div>
                                    <a href="{{ route('evento.detalle', $ev->id) }}" class="btn btn-xs bg-[#D97724] hover:bg-[#c06517] text-white font-bold rounded-lg border-none px-3.5 shadow-xs">
                                        Ver Show
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6">
                        <p class="text-xs font-semibold text-zinc-500">Pronto anunciaremos nuevas fechas y recitales en vivo.</p>
                    </div>
                @endforelse
            </div>

            <!-- Cápsulas de Paginación del Swiper de Eventos -->
            @if($eventSlides->count() > 1)
                <div class="flex items-center justify-center gap-2 pt-1">
                    @for($i = 0; $i < $eventSlides->count(); $i++)
                        <button 
                            type="button"
                            @click="stopEvents(); currentEventsSlide = {{ $i }}; startEvents()" 
                            :class="currentEventsSlide === {{ $i }} ? 'w-7 h-2 bg-[#D97724]' : 'w-2 h-2 bg-zinc-400 dark:bg-zinc-700 hover:bg-zinc-500'" 
                            class="rounded-full transition-all duration-300 cursor-pointer"
                            title="Slide {{ $i + 1 }}"
                        ></button>
                    @endfor
                </div>
            @endif
        </section>


        <!-- ========================================================================= -->
        <!-- SECCIÓN 5: MARCAS OFICIALES (SOLO LOGO SVG - LOOP INFINITO CONTINUO)      -->
        <!-- ========================================================================= -->
        <section id="marcas" class="space-y-4 pt-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        Marcas Oficiales con las que <span class="font-black text-[#D97724]">Trabajamos</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Instrumentos con procedencia original, garantía oficial y calibración de luthier.</p>
                </div>
            </div>

            <!-- Marquesina Continua animada hacia la izquierda (Loop infinito continuo sin saltos bruscos) -->
            <div class="overflow-hidden relative w-full py-2">
                <div class="sh-marquee-track">
                    <!-- Tanda 1: SOLO EL LOGO VECTORIAL EN TAMAÑO UNIFORME NORMALIZADO -->
                    <div class="flex items-center gap-4 pr-4 shrink-0">
                        @foreach($marcas as $m)
                            <div 
                                wire:click="$set('search', '{{ $m['name'] }}')"
                                class="group px-7 py-4 rounded-2xl bg-white dark:bg-[#201E1C] border border-zinc-200/90 dark:border-zinc-800 shadow-xs flex items-center justify-center shrink-0 cursor-pointer hover:border-[#D97724] dark:hover:border-[#D97724] transition-all"
                                title="Ver instrumentos {{ $m['name'] }}"
                            >
                                <div class="h-8 w-28 max-w-[110px] flex items-center justify-center shrink-0">
                                    <div 
                                        class="w-full h-full bg-[#181715]/75 dark:bg-[#F3EFE6]/80 group-hover:bg-[#D97724] dark:group-hover:bg-[#D97724] transition-colors"
                                        style="-webkit-mask: url('{{ asset($m['logo']) }}') no-repeat center; -webkit-mask-size: contain; mask: url('{{ asset($m['logo']) }}') no-repeat center; mask-size: contain;"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Tanda 2 duplicada idéntica para loop continuo sin saltos -->
                    <div class="flex items-center gap-4 pr-4 shrink-0">
                        @foreach($marcas as $m)
                            <div 
                                wire:click="$set('search', '{{ $m['name'] }}')"
                                class="group px-7 py-4 rounded-2xl bg-white dark:bg-[#201E1C] border border-zinc-200/90 dark:border-zinc-800 shadow-xs flex items-center justify-center shrink-0 cursor-pointer hover:border-[#D97724] dark:hover:border-[#D97724] transition-all"
                                title="Ver instrumentos {{ $m['name'] }}"
                            >
                                <div class="h-8 w-28 max-w-[110px] flex items-center justify-center shrink-0">
                                    <div 
                                        class="w-full h-full bg-[#181715]/75 dark:bg-[#F3EFE6]/80 group-hover:bg-[#D97724] dark:group-hover:bg-[#D97724] transition-colors"
                                        style="-webkit-mask: url('{{ asset($m['logo']) }}') no-repeat center; -webkit-mask-size: contain; mask: url('{{ asset($m['logo']) }}') no-repeat center; mask-size: contain;"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ANCLAJE PARA CONSULTA DE SALÓN (#salon) -->
        <div id="salon" class="p-6 rounded-[22px] bg-white dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">¿Buscás fecha para tu banda o evento?</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">El auditorio Live Room de Sound Heritage cuenta con sonido multicanal y capacidad para 100 personas.</p>
            </div>
            <a 
                href="mailto:reservas@soundheritage.com?subject=Consulta%20Reserva%20Sal%C3%B3n" 
                class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-full font-bold uppercase text-xs px-6 border-none shrink-0"
            >
                Consultar Salón →
            </a>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- BOTÓN FLOTANTE VOLVER ARRIBA (CON ICONO SVG PROFESIONAL)                  -->
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
