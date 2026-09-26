<div>
    <!-- ========================================================================= -->
    <!-- 1. HEADER SUPERIOR STICKY (ESTILO COMPRA GAMER)                           -->
    <!-- ========================================================================= -->
    <header class="sticky top-0 z-50 w-full bg-base-100/90 backdrop-blur-md border-b border-base-300 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- PARTE A: LOGO Y MARCA -->
            <a href="/" class="flex items-center gap-2 text-primary font-black text-xl tracking-wider hover:opacity-80 transition-opacity">
                <x-mary-icon name="o-musical-note" class="w-8 h-8 text-primary" />
                <span class="text-base-content font-extrabold uppercase">
                    SOUND<span class="text-primary">HERITAGE</span>
                </span>
            </a>

            <!-- PARTE B: BARRA DE BÚSQUEDA CENTRAL -->
            <div class="flex-1 max-w-lg hidden md:block">
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Buscar instrumentos, accesorios..." 
                        class="input input-bordered input-sm w-full pl-10 bg-base-200 focus:bg-base-100 rounded-full text-sm"
                    />
                    <x-mary-icon name="o-magnifying-glass" class="w-4 h-4 text-base-content/50 absolute left-3.5 top-2.5" />
                </div>
            </div>

            <!-- PARTE C: BOTONES DE ACCIÓN (TEMA + CARRITO) -->
            <div class="flex items-center gap-2">
                
                <!-- BOTÓN TOGGLE MODO OSCURO / BLANCO -->
                <button 
                    @click="theme = (theme === 'light' ? 'dark' : 'light'); localStorage.setItem('theme', theme)"
                    class="btn btn-ghost btn-circle btn-sm"
                    title="Cambiar tema"
                    type="button"
                >
                    <!-- Icono Luna (se muestra solo si estamos en modo claro) -->
                    <x-mary-icon x-show="theme === 'light'" name="o-moon" class="w-5 h-5 text-base-content" />
                    <!-- Icono Sol (se muestra solo si estamos en modo oscuro) -->
                    <x-mary-icon x-show="theme === 'dark'" name="o-sun" class="w-5 h-5 text-warning" />
                </button>
                @guest
                    <div class="hidden sm:flex items-center gap-3 text-xs font-semibold px-2">
                        <a href="{{ route('register') }}" class="text-base-content hover:text-primary transition-colors">
                            Registrate
                        </a>
                        <span class="text-base-content/30">|</span>
                        <a href="{{ route('login') }}" class="text-base-content hover:text-primary transition-colors">
                            Iniciá sesión
                        </a>
                    </div>
                @endguest
                @auth
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm text-xs font-bold gap-1 rounded-full px-3">
                            <x-mary-icon name="o-user" class="w-4 h-4 text-primary" />
                            <span class="max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                            <x-mary-icon name="o-chevron-down" class="w-3 h-3 text-base-content/50" />
                        </label>
                        <ul tabindex="0" class="dropdown-content z-50 menu p-2 shadow-lg bg-base-100 border border-base-200 rounded-2xl w-52 text-xs gap-1 mt-2">
                            <li class="menu-title text-[10px] text-base-content/50 uppercase">Mi Cuenta</li>
                            
                                                       @if(auth()->user()?->hasRole('Administrador'))
                                <li>
                                    <a href="/admin" class="text-primary font-bold hover:bg-primary/10">
                                        <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4" />
                                        Panel de Administración
                                    </a>
                                </li>
                            @elseif(auth()->user()?->hasRole('Encargado de Stock'))
                                <li>
                                    <a href="/stock" class="text-warning font-bold hover:bg-warning/10">
                                        <x-mary-icon name="o-archive-box" class="w-4 h-4" />
                                        Panel de Stock
                                    </a>
                                </li>
                            @elseif(auth()->user()?->hasRole('Vendedor'))
                                <li>
                                    <a href="/vendedor" class="text-info font-bold hover:bg-info/10">
                                        <x-mary-icon name="o-shopping-bag" class="w-4 h-4" />
                                        Panel de Ventas
                                    </a>
                                </li>
                            @endif
                                <a href="{{ route('profile.edit') }}">
                                    <x-mary-icon name="o-user" class="w-4 h-4" />
                                    Mi Perfil
                                </a>
                            </li>
                            <div class="divider my-1"></div>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="text-error w-full flex items-center gap-2 hover:bg-error/10">
                                        <x-mary-icon name="o-arrow-left-on-rectangle" class="w-4 h-4" />
                                        Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
                <!-- BOTÓN DEL CARRITO CON CONTADOR -->
                <button class="btn btn-primary btn-sm rounded-full gap-2 px-4 shadow-sm" type="button">
                    <x-mary-icon name="o-shopping-cart" class="w-4 h-4" />
                    <span class="hidden sm:inline font-bold text-xs uppercase">Carrito</span>
                    <!-- Badge numérico de artículos -->
                    <span class="badge badge-sm badge-neutral font-black">
                        {{ count(session('carrito', [])) }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. CONTENIDO PRINCIPAL: CATÁLOGO DE PRODUCTOS                            -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-extrabold mb-6 text-base-content">Nuestro Catálogo</h1>

        <!-- Grilla de productos: 1 columna en celular, 2 en tablet, 4 en PC -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($productos as $producto)
                <div class="card bg-base-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 border border-base-300 rounded-2xl overflow-hidden flex flex-col justify-between">
                    
                    <!-- FOTO Y BADGE SUPERIOR -->
                    <div class="relative bg-white p-4 flex items-center justify-center aspect-square">
                        <span class="badge badge-neutral text-xs absolute top-3 left-3">
                            {{ $producto->type ?? 'Artículo' }}
                        </span>
                        
                        @if(!empty($producto->image_path))
                            <img src="{{ asset($producto->image_path) }}" alt="{{ $producto->name }}" class="object-contain max-h-full max-w-full" />
                        @else
                            <x-mary-icon name="o-musical-note" class="w-16 h-16 text-base-300" />
                        @endif
                    </div>

                    <!-- CUERPO DE LA TARJETA -->
                    <div class="p-4 flex flex-col flex-grow justify-between gap-3 bg-base-100">
                        <div>
                            <h2 class="text-sm font-semibold text-base-content line-clamp-2 h-10 leading-snug" title="{{ $producto->name }}">
                                {{ $producto->name }}
                            </h2>
                        </div>

                        <!-- PRECIOS ESTILO COMPRA GAMER -->
                        <div class="pt-2 border-t border-base-200">
                            <p class="text-[11px] text-base-content/60">Precio de lista / cuotas:</p>
                            <p class="text-xs text-base-content/70 line-through font-medium">
                                ${{ number_format($producto->price * 1.15, 2) }}
                            </p>

                            <p class="text-2xl font-black text-primary leading-tight mt-0.5">
                                ${{ number_format($producto->price, 2) }}
                            </p>
                            <p class="text-[10px] text-success font-bold uppercase tracking-wide">
                                Efectivo / Transferencia
                            </p>
                        </div>

                        <!-- BOTÓN DE COMPRA CON FEEDBACK DE CARGA -->
                        <button 
                            wire:click="agregarAlCarrito({{ $producto->id }})"
                            wire:loading.attr="disabled"
                            wire:target="agregarAlCarrito({{ $producto->id }})"
                            class="btn btn-primary btn-sm w-full gap-2 mt-2 font-bold uppercase text-xs"
                        >
                            <span wire:loading.remove wire:target="agregarAlCarrito({{ $producto->id }})">
                                <x-mary-icon name="o-shopping-cart" class="w-4 h-4 inline" />
                                Añadir al Carrito
                            </span>
                            <span wire:loading wire:target="agregarAlCarrito({{ $producto->id }})">
                                <span class="loading loading-spinner loading-xs"></span>
                                Agregando...
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
</div>