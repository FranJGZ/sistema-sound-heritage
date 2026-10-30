<div
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>

    <!-- ========================================================================= -->
    <!-- SUB-CABECERA BREADCRUMB                                                   -->
    <!-- ========================================================================= -->
    <div class="bg-white/80 dark:bg-[#181715]/90 backdrop-blur-xs border-b border-zinc-200/80 dark:border-zinc-800 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-[#D97724] font-bold text-lg flex items-center">
                    <x-mary-icon name="o-shopping-bag" class="w-5 h-5 text-[#D97724]" />
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Checkout &amp; Facturación
                </h1>
                <span class="text-xs text-zinc-400 font-medium ml-2">
                    ({{ $this->cartCount }} {{ $this->cartCount === 1 ? 'ítem seleccionado' : 'ítems seleccionados' }})
                </span>
            </div>
            <a href="{{ route('productos') }}" class="text-xs font-bold text-zinc-500 hover:text-[#D97724] flex items-center gap-1 transition-colors">
                <x-mary-icon name="o-arrow-left" class="w-3.5 h-3.5" />
                <span>Continuar comprando</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- CUERPO PRINCIPAL                                                          -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if($orderConfirmed)
            <!-- ================================================================= -->
            <!-- PANTALLA DE ÉXITO TRAS CONFIRMAR LA COMPRA                        -->
            <!-- ================================================================= -->
            <div class="max-w-2xl mx-auto bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-8 sm:p-12 text-center shadow-lg">
                <div class="w-16 h-16 rounded-full bg-[#D97724]/10 text-[#D97724] mx-auto flex items-center justify-center mb-4">
                    <x-mary-icon name="o-check-circle" class="w-10 h-10 text-[#D97724]" />
                </div>

                <span class="text-[11px] font-black uppercase tracking-widest text-[#D97724] bg-[#D97724]/10 px-3 py-1 rounded-full">
                    Compra Finalizada con Éxito
                </span>

                <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mt-3">
                    Factura #{{ str_pad($confirmedInvoice?->receipt_number ?? $confirmedInvoiceId, 8, '0', STR_PAD_LEFT) }}
                </h2>

                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto mt-3 leading-relaxed">
                    ¡Gracias por tu compra, <strong>{{ $first_name }} {{ $last_name }}</strong>! Hemos emitido tu comprobante oficial y te enviamos una copia a <strong>{{ $email }}</strong> con todos los detalles.
                </p>

                <!-- Resumen de la Orden -->
                <div class="mt-6 bg-zinc-50 dark:bg-[#201E1C] rounded-2xl p-5 border border-zinc-200/80 dark:border-zinc-800 text-left space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Comprobante Fiscal:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">Factura B 0001-{{ str_pad($confirmedInvoice?->receipt_number ?? $confirmedInvoiceId, 8, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Modalidad de Entrega:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $delivery_method }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Medio de Pago:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $payment_method }}</span>
                    </div>
                    <div class="flex justify-between py-1 pt-2 font-bold text-sm">
                        <span class="text-zinc-800 dark:text-zinc-200">Total Abonado:</span>
                        <span class="text-[#D97724] text-base font-black">$ {{ number_format($confirmedInvoice?->total ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Botones de Descarga Directa de PDFs -->
                <div class="mt-6 space-y-2">
                    <button 
                        type="button" 
                        wire:click="descargarFacturaPdf({{ $confirmedInvoiceId }})"
                        class="btn w-full bg-[#181715] hover:bg-zinc-800 text-white border-none rounded-xl text-xs font-black uppercase flex items-center justify-center gap-2 h-11"
                    >
                        <x-mary-icon name="o-document-arrow-down" class="w-4 h-4 text-[#D97724]" />
                        Descargar Factura / Ticket Fiscal (PDF)
                    </button>

                    @if(!empty($createdAttendanceIds))
                        @foreach($createdAttendanceIds as $idx => $attId)
                            <button 
                                type="button" 
                                wire:click="descargarEntradaPdf({{ $attId }})"
                                class="btn w-full bg-[#D97724] hover:bg-[#c06517] text-white border-none rounded-xl text-xs font-black uppercase flex items-center justify-center gap-2 h-11"
                            >
                                <x-mary-icon name="o-ticket" class="w-4 h-4 text-white" />
                                Descargar Entrada Oficial #{{ str_pad($attId, 6, '0', STR_PAD_LEFT) }} (PDF)
                            </button>
                        @endforeach
                    @endif
                </div>

                <div class="mt-8 flex justify-center">
                    <a 
                        href="/" 
                        class="btn btn-ghost btn-sm text-xs font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-white"
                    >
                        Volver a la Tienda Principal
                    </a>
                </div>
            </div>

        @elseif(empty($this->carrito))
            <!-- ================================================================= -->
            <!-- CARRITO VACÍO                                                     -->
            <!-- ================================================================= -->
            <div class="max-w-md mx-auto bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-8 sm:p-12 text-center shadow-md">
                <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 mx-auto flex items-center justify-center mb-4">
                    <x-mary-icon name="o-shopping-cart" class="w-8 h-8" />
                </div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Tu carrito está vacío</h2>
                <p class="text-xs text-zinc-500 mt-2 mb-6">
                    Aún no seleccionaste ningún instrumento, accesorio o entrada a nuestros eventos.
                </p>
                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <a href="{{ route('productos') }}" class="btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm rounded-xl font-bold uppercase text-xs border-none">
                        Ver Catálogo de Productos
                    </a>
                    <a href="{{ route('eventos') }}" class="btn btn-ghost btn-sm rounded-xl font-bold text-xs">
                        Ver Cartelera de Eventos
                    </a>
                </div>
            </div>

        @else
            <!-- ================================================================= -->
            <!-- CHECKOUT PRINCIPAL (2 COLUMNAS)                                   -->
            <!-- ================================================================= -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- COLUMNA IZQUIERDA: PANELES (8 COLUMNAS) -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- ========================================================= -->
                    <!-- PANEL 1 (ARRIBA A LA IZQUIERDA): PRODUCTOS / CARRITO      -->
                    <!-- ========================================================= -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-5">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                1
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Carrito de Compras / Tu Selección
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Revisá y modificá los instrumentos y entradas de tu compra antes de confirmar.
                                </p>
                            </div>
                        </div>

                        <!-- Lista de Ítems en el Carrito -->
                        <div class="space-y-3">
                            @foreach($this->carrito as $key => $item)
                                @php
                                    $isEvent = (($item['type'] ?? '') === 'event');
                                    $unitPrice = (float) ($item['price'] ?? 0);
                                    $desc = (int) ($item['discount_percentage'] ?? 0);
                                    $offerPrice = ($desc > 0) ? ($unitPrice * (1 - ($desc / 100))) : $unitPrice;
                                    $finalPrice = ($this->isCash && !$isEvent) ? round($offerPrice * 0.90, 2) : $offerPrice;
                                    $qty = (int) ($item['cantidad'] ?? 1);
                                    $itemSubtotal = $finalPrice * $qty;
                                @endphp
                                <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50/60 dark:bg-[#201E1C]/60">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-12 h-12 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center shrink-0 overflow-hidden">
                                            @if($isEvent)
                                                <x-mary-icon name="o-ticket" class="w-6 h-6 text-[#D97724]" />
                                            @elseif(!empty($item['image']))
                                                <img src="{{ str_starts_with($item['image'], 'http') ? $item['image'] : asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain p-1" />
                                            @else
                                                <x-mary-icon name="o-musical-note" class="w-6 h-6 text-zinc-400" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md {{ $isEvent ? 'bg-[#D97724]/10 text-[#D97724]' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                                                    {{ $isEvent ? 'Entrada Evento' : 'Producto' }}
                                                </span>
                                            </div>
                                            <h4 class="text-xs font-bold text-zinc-900 dark:text-white truncate mt-0.5">
                                                {{ $item['name'] }}
                                            </h4>
                                            <div class="text-[11px] text-zinc-500">
                                                $ {{ number_format($finalPrice, 0, ',', '.') }} c/u
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Controles de Cantidad y Subtotal -->
                                    <div class="flex items-center gap-4 shrink-0">
                                        <div class="flex items-center gap-1.5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-1">
                                            <button 
                                                type="button" 
                                                wire:click="decrementarCantidad('{{ $key }}')"
                                                class="w-6 h-6 flex items-center justify-center rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 text-xs font-bold"
                                            >-</button>
                                            <span class="text-xs font-black px-1 min-w-[16px] text-center">{{ $qty }}</span>
                                            <button 
                                                type="button" 
                                                wire:click="incrementarCantidad('{{ $key }}')"
                                                class="w-6 h-6 flex items-center justify-center rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 text-xs font-bold"
                                            >+</button>
                                        </div>

                                        <div class="text-right min-w-[70px]">
                                            <div class="text-xs font-black text-zinc-900 dark:text-white">
                                                $ {{ number_format($itemSubtotal, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        <button 
                                            type="button" 
                                            wire:click="eliminarItem('{{ $key }}')" 
                                            class="text-zinc-400 hover:text-rose-500 transition-colors p-1"
                                            title="Quitar ítem"
                                        >
                                            <x-mary-icon name="o-trash" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <!-- ========================================================= -->
                    <!-- PANEL 2 (MEDIO): MEDIOS DE PAGO                           -->
                    <!-- ========================================================= -->
                    <div 
                        x-data="{ activeMethod: @entangle('payment_method') }" 
                        class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-5"
                    >
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                2
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Medio de Pago
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Seleccioná la forma de pago para completar la orden.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Mercado Pago -->
                            <label 
                                @click="activeMethod = 'Mercado Pago'"
                                class="flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                                :class="activeMethod === 'Mercado Pago' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50'"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" x-model="activeMethod" value="Mercado Pago" class="radio radio-xs radio-warning" />
                                        <span class="text-xs font-black text-zinc-900 dark:text-white">Mercado Pago</span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#D97724] bg-[#D97724]/10 px-2 py-0.5 rounded-md">
                                        Instantáneo
                                    </span>
                                </div>
                                <span class="text-[11px] text-zinc-500 leading-relaxed">
                                    Tarjetas de Crédito, Débito o dinero disponible en cuenta de Mercado Pago.
                                </span>
                            </label>

                            <!-- Efectivo / Transferencia -->
                            <label 
                                @click="activeMethod = 'Efectivo / Transferencia'"
                                class="flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                                :class="activeMethod === 'Efectivo / Transferencia' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50'"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" x-model="activeMethod" value="Efectivo / Transferencia" class="radio radio-xs radio-warning" />
                                        <span class="text-xs font-black text-zinc-900 dark:text-white">Efectivo / Transferencia</span>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-500/10 px-2 py-0.5 rounded-md">
                                        10% OFF EXTRA
                                    </span>
                                </div>
                                <span class="text-[11px] text-zinc-500 leading-relaxed">
                                    Aboná en efectivo al retirar o por transferencia bancaria directa con 10% de ahorro.
                                </span>
                            </label>
                        </div>

                        <div x-show="activeMethod === 'Mercado Pago'" x-transition class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-1.5 text-xs">
                            <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <x-mary-icon name="o-credit-card" class="w-4 h-4 text-[#D97724]" />
                                <span>Acreditación Online Segura</span>
                            </div>
                            <p class="text-zinc-600 dark:text-zinc-400 text-[11px] leading-relaxed">
                                Al confirmar la orden, se emitirá tu factura y se procesará el pago a través de la pasarela oficial de Mercado Pago.
                            </p>
                        </div>

                        <div x-show="activeMethod === 'Efectivo / Transferencia'" x-transition class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-1.5 text-xs">
                            <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <x-mary-icon name="o-banknotes" class="w-4 h-4 text-emerald-600" />
                                <span>Pago en Efectivo o Transferencia Bancaria</span>
                            </div>
                            <p class="text-zinc-600 dark:text-zinc-400 text-[11px] leading-relaxed">
                                Tu orden quedará reservada durante <strong>48 horas hábiles</strong> con el <strong>10% de descuento adicional</strong> aplicado. Podés abonar en efectivo al retirar por <strong>Calle Colón 1234, Posadas, Misiones</strong> o transferir a nuestro CBU/Alias informado en el comprobante.
                            </p>
                        </div>

                    </div>

                    <!-- ========================================================= -->
                    <!-- PANEL 3 (MEDIO): DATOS DE FACTURACIÓN Y COMPRADOR         -->
                    <!-- ========================================================= -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-5">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                3
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Datos de Facturación y Contacto
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Información para emitir tu Factura B legal de AFIP.
                                </p>
                            </div>
                        </div>

                        @auth
                            <!-- Resumen Datos del Comprador con botón Modificar -->
                            <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-[#D97724]/10 text-[#D97724] flex items-center justify-center shrink-0 mt-0.5">
                                        <x-mary-icon name="o-user" class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <div class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                            {{ $first_name }} {{ $last_name }}
                                        </div>
                                        <div class="text-[11px] text-zinc-500 truncate">
                                            DNI: <span class="font-mono">{{ $document_number ?: 'Sin registrar' }}</span> · Tel: {{ $phone ?: 'Sin registrar' }}
                                        </div>
                                        <div class="text-[11px] text-zinc-400 truncate flex items-center gap-1">
                                            <x-mary-icon name="o-lock-closed" class="w-3 h-3 text-zinc-400" />
                                            <span>{{ Auth::user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a 
                                    href="{{ route('mi-cuenta', ['section' => 'datos']) }}" 
                                    class="btn btn-xs btn-outline border-zinc-300 dark:border-zinc-700 text-xs font-bold rounded-xl shrink-0"
                                >
                                    Modificar
                                </a>
                            </div>
                            @if(empty($document_number) || empty($phone))
                                <div class="text-[11px] text-amber-600 dark:text-amber-400 flex items-center gap-1.5 pt-1">
                                    <x-mary-icon name="o-exclamation-circle" class="w-3.5 h-3.5 shrink-0" />
                                    <span>Completá tu DNI y teléfono en tu perfil para emitir la Factura legal de AFIP.</span>
                                </div>
                            @endif
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nombre -->
                                <div class="space-y-1">
                                    <label for="first_name" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        Nombre
                                    </label>
                                    <input 
                                        id="first_name"
                                        type="text" 
                                        wire:model="first_name" 
                                        placeholder="Ej: Martín"
                                        class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                    />
                                    @error('first_name') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                </div>

                                <!-- Apellido -->
                                <div class="space-y-1">
                                    <label for="last_name" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        Apellido
                                    </label>
                                    <input 
                                        id="last_name"
                                        type="text" 
                                        wire:model="last_name" 
                                        placeholder="Ej: Spinetta"
                                        class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                    />
                                    @error('last_name') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                </div>

                                <!-- DNI -->
                                <div class="space-y-1">
                                    <label for="document_number" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        DNI (Facturación AFIP)
                                    </label>
                                    <input 
                                        id="document_number"
                                        type="text" 
                                        wire:model="document_number" 
                                        placeholder="Ej: 38123456"
                                        class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                    />
                                    @error('document_number') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                </div>

                                <!-- Teléfono -->
                                <div class="space-y-1">
                                    <label for="phone" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        Teléfono o WhatsApp
                                    </label>
                                    <input 
                                        id="phone"
                                        type="text" 
                                        wire:model="phone" 
                                        placeholder="Ej: 11 4444 5555"
                                        class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                    />
                                    @error('phone') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                </div>

                                <!-- Correo Electrónico para Invitado -->
                                <div class="sm:col-span-2 space-y-1">
                                    <label for="email" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        Correo Electrónico (Receptor de Factura y Entradas)
                                    </label>
                                    <input 
                                        id="email"
                                        type="email" 
                                        wire:model="email" 
                                        placeholder="ejemplo@correo.com"
                                        class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                    />
                                    @error('email') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endauth

                    </div>

                    <!-- ========================================================= -->
                    <!-- PANEL 4 (ABAJO DE TODO): FORMA DE ENTREGA                 -->
                    <!-- ========================================================= -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-5">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                    4
                                </span>
                                <div>
                                    <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                        Forma de Entrega
                                    </h2>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $this->hasPhysicalProducts ? 'Elegí cómo recibir o retirar tus productos.' : 'Modalidad de recepción de tus entradas culturales.' }}
                                    </p>
                                </div>
                            </div>

                            @if($this->hasPhysicalProducts && $delivery_method === 'Envío a Domicilio' && !$editingAddress && !empty($shipping_address))
                                @auth
                                    <a 
                                        href="{{ route('mi-cuenta', ['section' => 'domicilios']) }}"
                                        class="text-xs font-bold text-[#D97724] hover:underline flex items-center gap-1"
                                    >
                                        <x-mary-icon name="o-pencil-square" class="w-3.5 h-3.5" />
                                        <span>Cambiar dirección</span>
                                    </a>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="toggleEditingAddress"
                                        class="text-xs font-bold text-[#D97724] hover:underline flex items-center gap-1"
                                    >
                                        <x-mary-icon name="o-pencil-square" class="w-3.5 h-3.5" />
                                        <span>Cambiar dirección</span>
                                    </button>
                                @endauth
                            @endif
                        </div>

                        @if($this->hasPhysicalProducts)
                            <!-- OPCIONES DE ENTREGA FÍSICA -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Opción Retiro en Local -->
                                <label class="flex flex-col p-4 rounded-2xl border cursor-pointer transition-all {{ $delivery_method === 'Retiro en Local Sound Heritage' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50' }}">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" wire:model.live="delivery_method" value="Retiro en Local Sound Heritage" class="radio radio-xs radio-warning" />
                                            <span class="text-xs font-black text-zinc-900 dark:text-white">Retiro en Sound Heritage</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-500/10 px-2 py-0.5 rounded-md">Gratis</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">
                                        Calle Colón 1234, Posadas, Misiones. Lunes a Sábados de 10 a 20 hs.
                                    </p>
                                </label>

                                <!-- Opción Envío a Domicilio (Foto 2) -->
                                <label class="flex flex-col p-4 rounded-2xl border cursor-pointer transition-all {{ $delivery_method === 'Envío a Domicilio' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50' }}">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" wire:model.live="delivery_method" value="Envío a Domicilio" class="radio radio-xs radio-warning" />
                                            <span class="text-xs font-black text-zinc-900 dark:text-white">Envío a Domicilio</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md">Coordinar</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500">
                                        Despacho asegurado por transporte especializado en instrumentos.
                                    </p>
                                </label>
                            </div>

                            <!-- Panel de Dirección de Entrega (Foto 2) -->
                            @if($delivery_method === 'Envío a Domicilio')
                                @if(!$editingAddress && !empty($shipping_address))
                                    <!-- Tarjeta Resumen Dirección Guardada (Foto 2) -->
                                    <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-4">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-xl bg-[#D97724]/10 text-[#D97724] flex items-center justify-center shrink-0 mt-0.5">
                                                <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                                    {{ $shipping_address }}
                                                </div>
                                                <div class="text-[11px] text-zinc-500">
                                                    {{ $shipping_city }} · CP {{ $shipping_postal_code ?: '3300' }}
                                                </div>
                                            </div>
                                        </div>
                                        @auth
                                            <a 
                                                href="{{ route('mi-cuenta', ['section' => 'domicilios']) }}"
                                                class="btn btn-xs btn-outline border-zinc-300 dark:border-zinc-700 text-xs font-bold rounded-xl"
                                            >
                                                Modificar
                                            </a>
                                        @else
                                            <button 
                                                type="button" 
                                                wire:click="toggleEditingAddress"
                                                class="btn btn-xs btn-outline border-zinc-300 dark:border-zinc-700 text-xs font-bold rounded-xl"
                                            >
                                                Modificar
                                            </button>
                                        @endauth
                                    </div>
                                @else
                                    <!-- Formulario para Cargar o Modificar Dirección -->
                                    <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div class="sm:col-span-2 space-y-1">
                                                <label for="shipping_address" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">Dirección y Altura (Piso / Depto)</label>
                                                <input id="shipping_address" type="text" wire:model="shipping_address" placeholder="Ej: Calle Colón 1234, Piso 2 B" class="w-full text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 px-3 py-2" />
                                                @error('shipping_address') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="space-y-1">
                                                <label for="shipping_city" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">Ciudad / Localidad</label>
                                                <input id="shipping_city" type="text" wire:model="shipping_city" placeholder="Ej: Posadas / Oberá" class="w-full text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 px-3 py-2" />
                                                @error('shipping_city') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="space-y-1">
                                                <label for="shipping_postal_code" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">Código Postal</label>
                                                <input id="shipping_postal_code" type="text" wire:model="shipping_postal_code" placeholder="Ej: 3300" class="w-full text-xs font-medium rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 px-3 py-2" />
                                            </div>
                                        </div>

                                        @if(!empty($shipping_address))
                                            <div class="flex justify-end pt-2">
                                                <button 
                                                    type="button" 
                                                    wire:click="toggleEditingAddress"
                                                    class="btn btn-xs bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold border-none"
                                                >
                                                    Confirmar dirección
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                        @else
                            <!-- CASO 100% EVENTOS: ENTREGA DIGITAL -->
                            <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-1 text-xs">
                                <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                    <x-mary-icon name="o-envelope" class="w-4 h-4 text-[#D97724]" />
                                    <span>Entrega Digital Inmediata por Correo</span>
                                </div>
                                <p class="text-zinc-500 dark:text-zinc-400 text-[11px] leading-relaxed">
                                    Tu carrito contiene exclusivamente entradas a eventos culturales. Las entradas oficiales con código QR y pase de sala se enviarán en formato PDF a tu correo y podrás descargarlas de inmediato al finalizar.
                                </p>
                            </div>
                        @endif

                    </div>

                </div>

                <!-- ============================================================= -->
                <!-- COLUMNA DERECHA: RESUMEN CHECKOUT STICKY (COMO ESTABA ANTES) -->
                <!-- ============================================================= -->
                <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-5">
                    
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 shadow-md space-y-5">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <x-mary-icon name="o-receipt-percent" class="w-5 h-5 text-[#D97724]" />
                                <h3 class="text-base font-black text-zinc-900 dark:text-white">
                                    Resumen de Compra
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2.5 py-1 rounded-full">
                                {{ $this->cartCount }} {{ $this->cartCount === 1 ? 'ítem' : 'ítems' }}
                            </span>
                        </div>

                        <!-- Desglose Financiero -->
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-zinc-500">
                                <span>Subtotal Precio de Lista:</span>
                                <span class="{{ $this->cartSavings > 0 ? 'line-through' : 'font-bold text-zinc-900 dark:text-white' }}">
                                    $ {{ number_format($this->cartListSubtotal, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($this->cartSavings > 0)
                                <div class="flex justify-between text-[#D97724] font-bold bg-[#D97724]/10 px-3 py-1.5 rounded-xl border border-[#D97724]/20">
                                    <span>Ahorro Promocional:</span>
                                    <span>-$ {{ number_format($this->cartSavings, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex items-baseline justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <div>
                                    <span class="text-sm font-black text-zinc-900 dark:text-white block">Total a Pagar:</span>
                                    <span class="text-[10px] text-zinc-400 font-medium">IVA incluido</span>
                                </div>
                                <span class="text-2xl font-black text-[#D97724]">
                                    $ {{ number_format($this->cartSubtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Políticas -->
                        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 text-[11px] text-zinc-500 dark:text-zinc-400 leading-relaxed">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300 block mb-0.5">Emisión Fiscal Inmediata:</span>
                            Al confirmar se emite la Factura legal y las entradas con código de acceso asociadas a tu DNI.
                        </div>

                        <!-- Botón Confirmar Compra -->
                        <button 
                            type="button" 
                            wire:click="confirmarCompra" 
                            wire:loading.attr="disabled"
                            wire:target="confirmarCompra"
                            class="btn bg-[#D97724] hover:bg-[#c06517] text-white w-full rounded-2xl font-black uppercase tracking-wide text-xs h-12 shadow-lg shadow-[#D97724]/25 border-none flex items-center justify-center gap-2 cursor-pointer transition-all"
                        >
                            <span wire:loading.remove wire:target="confirmarCompra">
                                <x-mary-icon name="o-lock-closed" class="w-4 h-4 inline" />
                                Confirmar y Pagar ($ {{ number_format($this->cartSubtotal, 0, ',', '.') }})
                            </span>
                            <span wire:loading wire:target="confirmarCompra">
                                <span class="loading loading-spinner loading-xs"></span>
                                Procesando orden...
                            </span>
                        </button>

                    </div>

                </div>

            </div>
        @endif

    </main>
</div>
