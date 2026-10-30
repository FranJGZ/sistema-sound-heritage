<!-- ========================================================================= -->
<!-- ENCABEZADO CANÓNICO SOUND HERITAGE (Fila 1 + Fila 2 Unificadas)           -->
<!-- ========================================================================= -->
<header 
    x-data="{ 
        scrolled: false,
        handleScroll() {
            this.scrolled = window.pageYOffset > 40;
        }
    }"
    @scroll.window="handleScroll()"
    class="sticky top-0 z-40 w-full transition-all duration-300 text-white"
    :class="scrolled ? 'bg-[#181715]/90 backdrop-blur-md shadow-xl' : 'bg-[#181715] shadow-none'"
>
    <!-- FILA 1: SUPERIOR ESPACIOSA (h-20, ~80px) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4 sm:gap-6">
        
        <!-- Logo Blanco Sound Heritage -->
        <a href="/" class="flex items-center gap-3 hover:opacity-90 transition-opacity shrink-0">
            <div class="h-12 w-12 flex items-center justify-center shrink-0">
                <img 
                    src="{{ asset('img/LogoTiendaBlanco.png') }}" 
                    alt="Sound Heritage Icono" 
                    style="height: 96px; width: auto; max-width: none; object-fit: contain; transform: translate(1px, 12px); display: block; pointer-events: none;" 
                />
            </div>
            <img 
                src="{{ asset('img/LetrasTiendaBlanco.png') }}" 
                alt="Sound Heritage" 
                class="h-6 w-auto object-contain block shrink-0" 
            />
        </a>

        <!-- Buscador Central Predictivo Aislado -->
        <livewire:search-bar />

        <!-- Accesos Rápidos: Modo Claro/Oscuro, Usuario y Carrito -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Alternador de Tema Día / Noche -->
            <button 
                type="button" 
                @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme)" 
                class="btn btn-ghost btn-circle btn-sm text-zinc-300 hover:text-white hover:bg-white/10 transition-colors cursor-pointer" 
                title="Alternar Modo Día / Noche"
            >
                <span x-show="theme === 'dark'">
                    <x-mary-icon name="o-sun" class="w-4 h-4 text-[#D97724]" />
                </span>
                <span x-show="theme !== 'dark'">
                    <x-mary-icon name="o-moon" class="w-4 h-4 text-zinc-300" />
                </span>
            </button>

            <!-- Acceso a Cuenta / Autenticación -->
            @guest
                <a 
                    href="{{ route('login') }}" 
                    class="btn btn-sm btn-ghost text-xs font-bold text-white hover:bg-white/10 rounded-full px-3.5"
                >
                    Ingresar
                </a>
            @endguest

            @auth
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm text-xs font-bold gap-1 rounded-full px-3 text-white hover:bg-white/10 cursor-pointer">
                        <x-mary-icon name="o-user" class="w-4 h-4 text-[#D97724]" />
                        <span class="max-w-[100px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                        <x-mary-icon name="o-chevron-down" class="w-3 h-3 text-zinc-400" />
                    </label>
                    <ul tabindex="0" class="dropdown-content z-50 menu p-2 shadow-2xl bg-[#181715] text-white border border-[#7E2B2D]/40 rounded-2xl w-56 text-xs gap-1 mt-2">
                        <li class="menu-title text-[10px] text-zinc-400 uppercase">Mi Cuenta</li>
                        <li><a href="{{ route('mi-cuenta') }}" class="hover:bg-white/10 flex items-center gap-2"><x-mary-icon name="o-user" class="w-3.5 h-3.5 text-[#D97724]" /> Mi Perfil</a></li>

                        @if(Auth::user()->hasRole('Administrador'))
                            <div class="divider my-1 border-white/10"></div>
                            <li class="menu-title text-[10px] text-zinc-400 uppercase">Administración</li>
                            <li><a href="/admin" class="hover:bg-white/10 flex items-center gap-2 text-[#D97724] font-bold"><x-mary-icon name="o-shield-check" class="w-3.5 h-3.5 text-[#D97724]" /> Panel de Administrador</a></li>
                        @elseif(Auth::user()->hasRole('Vendedor'))
                            <div class="divider my-1 border-white/10"></div>
                            <li class="menu-title text-[10px] text-zinc-400 uppercase">Punto de Venta</li>
                            <li><a href="/vendedor" class="hover:bg-white/10 flex items-center gap-2 text-[#D97724] font-bold"><x-mary-icon name="o-shopping-bag" class="w-3.5 h-3.5 text-[#D97724]" /> Panel de Vendedor</a></li>
                        @elseif(Auth::user()->hasRole('Encargado de Stock'))
                            <div class="divider my-1 border-white/10"></div>
                            <li class="menu-title text-[10px] text-zinc-400 uppercase">Depósito y Stock</li>
                            <li><a href="/stock" class="hover:bg-white/10 flex items-center gap-2 text-[#D97724] font-bold"><x-mary-icon name="o-archive-box" class="w-3.5 h-3.5 text-[#D97724]" /> Panel de Stock</a></li>
                        @endif

                        <div class="divider my-1 border-white/10"></div>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="text-rose-400 w-full flex items-center gap-2 hover:bg-rose-500/10 cursor-pointer">
                                    <x-mary-icon name="o-arrow-left-on-rectangle" class="w-3.5 h-3.5 text-rose-400" />
                                    Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            <!-- Acceso Directo al Carrito / Checkout con Badge Reactivo Instantáneo -->
            <div 
                x-data="{ cartCount: {{ (int) array_sum(array_column(session('carrito', []), 'cantidad')) }} }"
                @cart-updated.window="cartCount = $event.detail.count !== undefined ? $event.detail.count : cartCount"
                class="relative"
            >
                <a 
                    href="{{ route('checkout') }}"
                    class="btn btn-ghost btn-circle btn-sm relative text-white hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center" 
                    title="Ver Carrito y Finalizar Compra"
                >
                    <x-mary-icon name="o-shopping-cart" class="w-5 h-5 text-white" />
                    <span 
                        x-show="cartCount > 0" 
                        x-text="cartCount" 
                        class="absolute -top-1 -right-1 bg-[#D97724] text-white text-[10px] font-black min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1 shadow-md"
                        style="display: none;"
                    ></span>
                </a>
            </div>
        </div>
    </div>

    <!-- FILA 2: NAVEGACIÓN INFERIOR DE SECCIONES (Se colapsa al scrollear hacia abajo) -->
    <div 
        x-show="!scrolled" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
        x-transition:enter-end="opacity-100 translate-y-0 max-h-12"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 max-h-12"
        x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
        class="bg-[#181715] overflow-hidden"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center justify-start text-xs font-semibold">
            <nav class="flex items-center gap-7 tracking-wide text-zinc-300">
                <a 
                    href="{{ route('productos') }}" 
                    class="transition-colors hover:text-[#D97724] {{ request()->routeIs('productos') && !request()->routeIs('ofertas') ? 'text-[#D97724] font-bold' : '' }}"
                >
                    Productos
                </a>
                <a 
                    href="{{ route('ofertas') }}" 
                    class="transition-colors hover:text-[#D97724] {{ request()->routeIs('ofertas') ? 'text-[#D97724] font-bold' : '' }}"
                >
                    Ofertas
                </a>
                <a 
                    href="{{ route('eventos') }}" 
                    class="transition-colors hover:text-[#D97724] {{ request()->routeIs('eventos*') ? 'text-[#D97724] font-bold' : '' }}"
                >
                    Eventos
                </a>
                <a 
                    href="{{ route('reservar-salon') }}" 
                    class="transition-colors hover:text-[#D97724] {{ request()->routeIs('reservar-salon') ? 'text-[#D97724] font-bold' : '' }}"
                >
                    Reservar Salón
                </a>
            </nav>
        </div>
    </div>
</header>
