<div
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200"
>

    <!-- ========================================================================= -->
    <!-- 2. SUB-CABECERA BREADCRUMB                                               -->
    <!-- ========================================================================= -->
    <div class="bg-white/80 dark:bg-[#181715]/90 backdrop-blur-xs border-b border-zinc-200/80 dark:border-zinc-800 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="/" class="text-[#D97724] font-bold text-lg flex items-center hover:opacity-80 transition-opacity">
                    <x-mary-icon name="o-chevron-left" class="w-5 h-5 text-[#D97724] stroke-[3]" />
                </a>
                <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Solicitud de Reserva de Salón
                </h1>
                <span class="text-xs text-zinc-400 font-medium ml-1">
                    &bull; Auditorio Principal Sound Heritage
                </span>
            </div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
                Capacidad máxima de sala: <span class="text-[#D97724] font-bold">150 personas</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. CUERPO PRINCIPAL (DISEÑO CHECKOUT: 2 COLUMNAS)                         -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        
        @if($reservationConfirmed)
            <!-- PANTALLA DE CONFIRMACIÓN EXITOSA -->
            <div class="max-w-2xl mx-auto bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-8 sm:p-12 text-center shadow-lg animate-fadeIn">
                <div class="w-20 h-20 rounded-full bg-[#D97724]/10 text-[#D97724] flex items-center justify-center mx-auto mb-6">
                    <x-mary-icon name="o-check-badge" class="w-10 h-10 stroke-2" />
                </div>

                <span class="text-[11px] uppercase tracking-wider font-bold text-[#D97724] bg-[#D97724]/10 px-3 py-1 rounded-full">
                    Solicitud Registrada
                </span>

                <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mt-3">
                    ¡Reserva #{{ str_pad($createdReservationId, 5, '0', STR_PAD_LEFT) }} en proceso!
                </h2>

                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto mt-3 leading-relaxed">
                    @if($payment_mode === 'total')
                        Hemos registrado tu pedido para <strong>{{ $first_name }} {{ $last_name }}</strong> bajo la modalidad <span class="font-bold text-[#D97724]">Pago Total (100%)</span>. Te enviamos un correo electrónico a <strong>{{ $email }}</strong> con el detalle y los pasos a seguir mediante <strong>{{ $payment_method }}</strong>.
                    @else
                        Hemos registrado tu pedido para <strong>{{ $first_name }} {{ $last_name }}</strong> en estado <span class="font-bold text-[#D97724]">Pendiente de Seña (30%)</span>. Te enviamos un correo electrónico a <strong>{{ $email }}</strong> con el detalle y los datos para completar la seña mediante <strong>{{ $payment_method }}</strong>.
                    @endif
                </p>

                <!-- Resumen de la Solicitud -->
                <div class="mt-6 bg-zinc-50 dark:bg-[#201E1C] rounded-2xl p-5 border border-zinc-200/80 dark:border-zinc-800 text-left space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Motivo / Tipo de Uso:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $type }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Fecha Reservada:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Turno Solicitado:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $shift }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Personas / Asistentes:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $attendees_count }} personas</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Modalidad Elegida:</span>
                        <span class="font-bold text-[#D97724]">{{ $payment_mode === 'total' ? 'Pago Total (100%)' : 'Seña Inicial (30%)' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Medio de Pago Seleccionado:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ $payment_method }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-zinc-500">Costo Total del Alquiler:</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">$ {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                    </div>
                    @if($payment_mode === 'total')
                        <div class="flex justify-between py-1 pt-2 font-bold text-sm">
                            <span class="text-[#D97724]">Total Abonado / A Cancelar (100%):</span>
                            <span class="text-[#D97724] text-base font-black">$ {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span>Saldo Restante en Sala:</span>
                            <span>$ 0 (Totalmente cubierto)</span>
                        </div>
                    @else
                        <div class="flex justify-between py-1 pt-2 font-bold text-sm">
                            <span class="text-[#D97724]">Seña Solicitada (30%):</span>
                            <span class="text-[#D97724] text-base font-black">$ {{ number_format($this->depositAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-zinc-400">
                            <span>Saldo en Sala el día del turno (70%):</span>
                            <span>$ {{ number_format($this->balanceDue, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button 
                        type="button" 
                        wire:click="reiniciarFormulario" 
                        class="btn btn-ghost btn-sm text-xs font-bold text-zinc-600 dark:text-zinc-400 cursor-pointer"
                    >
                        Realizar otra solicitud
                    </button>
                    <a 
                        href="/" 
                        class="btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm rounded-xl font-bold uppercase text-xs border-none px-6"
                    >
                        Volver a la Tienda
                    </a>
                </div>
            </div>

        @else
            <!-- FORMULARIO EN 2 COLUMNAS (CHECKOUT STYLE) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO PASO A PASO (8 COLUMNAS) -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- PASO 1: MOTIVO DE USO Y SELECCIÓN DE PERSONAS -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-6">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                1
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Motivo del Alquiler y Convocatoria
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Seleccioná la actividad a desarrollar y la cantidad de integrantes o asistentes.
                                </p>
                            </div>
                        </div>

                        <!-- Select de Motivo de Uso -->
                        <div class="space-y-2">
                            <label for="select-motivo" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Motivo / Tipo de Uso del Salón
                            </label>
                            <select 
                                id="select-motivo"
                                wire:model.live="type"
                                class="select w-full rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-300 dark:border-zinc-700 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#D97724] h-11"
                            >
                                @foreach($tarifas as $k => $tarifa)
                                    <option value="{{ $k }}">{{ $tarifa['name'] }} &bull; Base $ {{ number_format($tarifa['price'], 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            
                            <!-- Descripción del uso elegido -->
                            <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 block mb-0.5">Equipamiento incluido:</span>
                                {{ $tarifas[$type]['description'] ?? '' }}
                            </div>
                        </div>

                        <!-- Selector de Cantidad de Personas con Escala de Descuento por Bloques de 5 -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        Cantidad de Integrantes / Asistentes
                                    </label>
                                    <span class="text-[11px] text-zinc-400">
                                        A partir de 5 personas accedés a descuentos grupales en tramos de 5.
                                    </span>
                                </div>

                                <!-- Selector [-] Qty [+] con Optimistic UI (0 ms Lag) -->
                                <div 
                                    x-data="{ 
                                        count: @entangle('attendees_count').live,
                                        inc() { if (this.count < 150) { this.count++; } },
                                        dec() { if (this.count > 1) { this.count--; } }
                                    }"
                                    class="flex items-center border border-zinc-300 dark:border-zinc-700 rounded-2xl overflow-hidden h-10 bg-zinc-50 dark:bg-[#201E1C] shrink-0"
                                >
                                    <button 
                                        type="button" 
                                        @click="dec()" 
                                        class="w-10 h-full flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 font-black text-sm text-zinc-700 dark:text-zinc-200 transition-colors cursor-pointer select-none"
                                        title="Quitar persona"
                                    >
                                        -
                                    </button>
                                    <input 
                                        type="number" 
                                        x-model.number="count" 
                                        min="1" 
                                        max="150"
                                        class="w-14 h-full text-center text-xs font-black bg-transparent border-none text-zinc-900 dark:text-white focus:outline-none"
                                    />
                                    <button 
                                        type="button" 
                                        @click="inc()" 
                                        class="w-10 h-full flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 font-black text-sm text-zinc-700 dark:text-zinc-200 transition-colors cursor-pointer select-none"
                                        title="Agregar persona"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- Badge de Descuento Grupal Activo -->
                            @if($this->discountPercent > 0)
                                <div class="p-3 rounded-2xl bg-[#D97724]/10 border border-[#D97724]/30 text-xs text-[#D97724] font-bold flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <x-mary-icon name="o-check-circle" class="w-4 h-4 text-[#D97724] shrink-0" />
                                        Beneficio Grupal Activo ({{ $attendees_count }} asistentes): {{ $this->discountPercent }}% OFF en el alquiler
                                    </span>
                                    <span class="text-xs font-black">
                                        Ahorrás $ {{ number_format($this->discountAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Mensaje Informativo Sobrio cuando falta 1 persona para el siguiente bloque (SIN EMOJIS) -->
                            @if($this->nextTierNotice)
                                <div class="p-3 rounded-2xl bg-amber-500/10 border border-[#D97724]/30 text-xs text-[#D97724] font-medium flex items-center gap-2">
                                    <x-mary-icon name="o-information-circle" class="w-4 h-4 shrink-0 text-[#D97724]" />
                                    <span>{{ $this->nextTierNotice }}</span>
                                </div>
                            @endif

                        </div>

                    </div>

                    <!-- PASO 2: FECHA Y TURNO SOLICITADO -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-6">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                2
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Fecha y Turno de Reserva
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Validación de disponibilidad en tiempo real con la agenda del auditorio.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Fecha Solicitada -->
                            <div class="space-y-1.5">
                                <label for="input-date" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Fecha Solicitada
                                </label>
                                <input 
                                    id="input-date"
                                    type="date" 
                                    wire:model.live="date" 
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full text-xs font-semibold rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                />
                                @error('date')
                                    <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Turno Solicitado -->
                            <div class="space-y-1.5">
                                <label for="select-shift" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Turno Horario
                                </label>
                                <select 
                                    id="select-shift"
                                    wire:model.live="shift"
                                    class="select w-full rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-300 dark:border-zinc-700 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#D97724] h-11"
                                >
                                    <option value="Mañana (10:00 a 14:00 hs)">Mañana (10:00 a 14:00 hs)</option>
                                    <option value="Tarde (15:00 a 19:00 hs)">Tarde (15:00 a 19:00 hs)</option>
                                    <option value="Noche (20:00 a 24:00 hs)">Noche (20:00 a 24:00 hs)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Alerta de Conflicto de Disponibilidad (Sobria, sin emojis) -->
                        @if(!empty($dateConflictError))
                            <div class="p-4 rounded-2xl bg-[#7E2B2D]/15 border border-[#7E2B2D]/40 text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-2.5">
                                <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 shrink-0 text-[#7E2B2D]" />
                                <span>{{ $dateConflictError }}</span>
                            </div>
                        @else
                            <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500 flex items-center gap-2">
                                <x-mary-icon name="o-check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                                <span>Fecha y turno pre-disponibles para solicitud.</span>
                            </div>
                        @endif

                    </div>

                    <!-- PASO 3: DATOS DEL TITULAR Y RIDER TÉCNICO -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-6">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                3
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Datos del Solicitante y Especificaciones
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Información de contacto para la coordinación técnica y la seña de confirmación.
                                </p>
                            </div>
                        </div>

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
                                    placeholder="Ej: Silva"
                                    class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                />
                                @error('last_name') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- DNI / CUIT -->
                            <div class="space-y-1">
                                <label for="document_number" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    DNI o CUIT
                                </label>
                                <input 
                                    id="document_number"
                                    type="text" 
                                    wire:model="document_number" 
                                    placeholder="Sin puntos ni guiones"
                                    class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                />
                                @error('document_number') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Teléfono / WhatsApp -->
                            <div class="space-y-1">
                                <label for="phone" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Teléfono / WhatsApp de Contacto
                                </label>
                                <input 
                                    id="phone"
                                    type="text" 
                                    wire:model="phone" 
                                    placeholder="Ej: 11 4567 8901"
                                    class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                />
                                @error('phone') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div class="sm:col-span-2 space-y-1">
                                <label for="email" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Correo Electrónico
                                </label>
                                <input 
                                    id="email"
                                    type="email" 
                                    wire:model="email" 
                                    placeholder="tunombre@ejemplo.com"
                                    class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none h-11"
                                />
                                @error('email') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Notas / Rider Técnico -->
                            <div class="sm:col-span-2 space-y-1">
                                <label for="notes" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Rider Técnico y Observaciones (Opcional)
                                </label>
                                <textarea 
                                    id="notes"
                                    wire:model="notes" 
                                    rows="3" 
                                    placeholder="Detallá qué instrumentos traen, si necesitan amplificación de bajo específica, micrófonos adicionales o soporte de grabación."
                                    class="w-full text-xs font-medium rounded-2xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-[#201E1C] text-zinc-900 dark:text-zinc-100 p-3.5 focus:ring-2 focus:ring-[#D97724] focus:outline-none"
                                ></textarea>
                                @error('notes') <p class="text-rose-500 text-[11px] font-semibold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                    </div>

                    <!-- PASO 4: MODALIDAD Y MEDIO DE PAGO -->
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 sm:p-8 shadow-xs space-y-6">
                        
                        <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-7 h-7 rounded-full bg-[#D97724]/10 text-[#D97724] font-black text-xs flex items-center justify-center shrink-0">
                                4
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white">
                                    Modalidad y Medio de Pago
                                </h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Elegí si deseás abonar la seña para asegurar la fecha o cancelar el total por adelantado.
                                </p>
                            </div>
                        </div>

                        <!-- 1. MODALIDAD DE PAGO (SEÑA 30% VS TOTAL 100%) -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Modalidad de Pago
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Opción Seña 30% -->
                                <label 
                                    class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all {{ $payment_mode === 'seña' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50' }}"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="radio" 
                                                wire:model.live="payment_mode" 
                                                value="seña" 
                                                class="radio radio-xs radio-warning"
                                            />
                                            <span class="text-xs font-black text-zinc-900 dark:text-white">Abonar Seña (30%)</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#D97724] bg-[#D97724]/10 px-2 py-0.5 rounded-md">
                                            Recomendado
                                        </span>
                                    </div>
                                    <div class="text-lg font-black text-[#D97724] mb-1">
                                        $ {{ number_format($this->depositAmount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        Bloqueá la fecha de inmediato. El saldo de $ {{ number_format($this->balanceDue, 0, ',', '.') }} se cancela en sala el día del turno.
                                    </div>
                                </label>

                                <!-- Opción Pago Total 100% -->
                                <label 
                                    class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all {{ $payment_mode === 'total' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50' }}"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="radio" 
                                                wire:model.live="payment_mode" 
                                                value="total" 
                                                class="radio radio-xs radio-warning"
                                            />
                                            <span class="text-xs font-black text-zinc-900 dark:text-white">Pago Total (100%)</span>
                                        </div>
                                    </div>
                                    <div class="text-lg font-black text-zinc-900 dark:text-white mb-1">
                                        $ {{ number_format($this->totalPrice, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        Aboná el importe completo y desentendete de pagos al momento de ingresar al auditorio.
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 2. MEDIO DE PAGO (0ms con Alpine.js) -->
                        <div x-data="{ activeMethod: @entangle('payment_method') }" class="space-y-3 pt-2">
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Selección del Medio de Pago
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Mercado Pago / Tarjetas -->
                                <label 
                                    @click="activeMethod = 'Mercado Pago'"
                                    class="flex flex-col p-3.5 rounded-2xl border cursor-pointer transition-all"
                                    :class="activeMethod === 'Mercado Pago' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50'"
                                >
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" x-model="activeMethod" value="Mercado Pago" class="radio radio-xs radio-warning" />
                                            <span class="text-xs font-bold text-zinc-900 dark:text-white">Mercado Pago</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#D97724] bg-[#D97724]/10 px-2 py-0.5 rounded-md">
                                            Online
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-zinc-500">Crédito, Débito o dinero en cuenta</span>
                                </label>

                                <!-- Efectivo en Mostrador -->
                                <label 
                                    @click="activeMethod = 'Efectivo en Mostrador'"
                                    class="flex flex-col p-3.5 rounded-2xl border cursor-pointer transition-all"
                                    :class="activeMethod === 'Efectivo en Mostrador' ? 'border-[#D97724] bg-[#D97724]/5 ring-1 ring-[#D97724]' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-[#201E1C]/50'"
                                >
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" x-model="activeMethod" value="Efectivo en Mostrador" class="radio radio-xs radio-warning" />
                                            <span class="text-xs font-bold text-zinc-900 dark:text-white">Efectivo en Local</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md">
                                            48 hs
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-zinc-500">Plazo de 48 hs en mostrador</span>
                                </label>
                            </div>

                            <!-- Detalle Dinámico según Medio (0ms en cliente) -->
                            <div x-show="activeMethod === 'Mercado Pago'" x-transition class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-1.5 text-xs">
                                <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                    <x-mary-icon name="o-credit-card" class="w-4 h-4 text-[#D97724]" />
                                    <span>Acreditación Online</span>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-400 text-[11px] leading-relaxed">
                                    Al confirmar la solicitud, el sistema generará el registro de reserva y te habilitará el link de acreditación online inmediata para tarjeta de crédito, débito o dinero en cuenta.
                                </p>
                            </div>

                            <div x-show="activeMethod === 'Efectivo en Mostrador'" x-transition class="p-4 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 space-y-1.5 text-xs">
                                <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                    <x-mary-icon name="o-building-storefront" class="w-4 h-4 text-[#D97724]" />
                                    <span>Pago en Mostrador Sound Heritage</span>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-400 text-[11px] leading-relaxed">
                                    Tenés un plazo de <strong>48 horas hábiles</strong> para acercarte a nuestro local en <strong>Calle Colón 1234, Posadas, Misiones</strong> a abonar el importe y efectivizar la reserva. Pasado dicho plazo, la fecha se liberará nuevamente.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- COLUMNA DERECHA: RESUMEN CHECKOUT STICKY (4 COLUMNAS) -->
                <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-5">
                    
                    <div class="bg-white dark:bg-[#181715] rounded-3xl border border-zinc-200/90 dark:border-zinc-800 p-6 shadow-md space-y-5">
                        
                        <!-- Encabezado Resumen -->
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <x-mary-icon name="o-building-office-2" class="w-5 h-5 text-[#D97724]" />
                                <h3 class="text-base font-black text-zinc-900 dark:text-white">
                                    Resumen de Alquiler
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2.5 py-1 rounded-full">
                                Auditorio
                            </span>
                        </div>

                        <!-- Ficha del Espacio -->
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Espacio:</span>
                                <span class="font-bold text-zinc-900 dark:text-white">{{ $venue->name }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Ubicación:</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $venue->location }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Uso:</span>
                                <span class="font-bold text-zinc-900 dark:text-white">{{ $type }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Fecha:</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ !empty($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-' }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Turno:</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $shift }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Asistentes:</span>
                                <span class="font-black text-zinc-900 dark:text-white">{{ $attendees_count }} personas</span>
                            </div>
                        </div>

                        <div class="divider my-1 border-zinc-100 dark:border-zinc-800"></div>

                        <!-- Desglose Financiero -->
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-zinc-500">
                                <span>Tarifa Base del Salón:</span>
                                <span class="{{ $this->discountPercent > 0 ? 'line-through' : 'font-bold text-zinc-900 dark:text-white' }}">
                                    $ {{ number_format($this->basePrice, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($this->discountPercent > 0)
                                <div class="flex justify-between text-[#D97724] font-bold bg-[#D97724]/10 px-3 py-1.5 rounded-xl border border-[#D97724]/20">
                                    <span>Beneficio Grupal ({{ $this->discountPercent }}% OFF):</span>
                                    <span>-$ {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex items-baseline justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                <div>
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 block">Total Alquiler Salón:</span>
                                    <span class="text-[10px] text-zinc-400 font-medium">IVA incluido</span>
                                </div>
                                <span class="text-base font-black text-zinc-900 dark:text-white">
                                    $ {{ number_format($this->totalPrice, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Monto a Abonar Ahora vs Saldo en Sala -->
                            <div class="pt-2 border-t border-dashed border-zinc-200 dark:border-zinc-800 space-y-1.5">
                                <div class="flex items-baseline justify-between">
                                    <div>
                                        <span class="text-xs font-black text-[#D97724] block">
                                            Monto a Abonar Ahora ({{ $payment_mode === 'total' ? 'Total 100%' : 'Seña 30%' }}):
                                        </span>
                                    </div>
                                    <span class="text-2xl font-black text-[#D97724]">
                                        $ {{ number_format($this->amountToPayNow, 0, ',', '.') }}
                                    </span>
                                </div>

                                @if($payment_mode === 'seña')
                                    <div class="flex justify-between text-[11px] text-zinc-500 font-medium">
                                        <span>Saldo a abonar en Sala:</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">
                                            $ {{ number_format($this->balanceDue, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Nota de Política de Aprobación -->
                        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-[#201E1C] border border-zinc-200/80 dark:border-zinc-800 text-[11px] text-zinc-500 dark:text-zinc-400 leading-relaxed">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300 block mb-0.5">Políticas de Confirmación:</span>
                            La reserva se registra en estado <strong>Pendiente</strong>. La seña acordada (30%) o el pago total confirman la reserva una vez verificado el pago.
                        </div>

                        <!-- Botón Confirmar Reserva Dinámico -->
                        <button 
                            type="button" 
                            wire:click="confirmarReserva" 
                            wire:loading.attr="disabled"
                            wire:target="confirmarReserva"
                            class="btn bg-[#D97724] hover:bg-[#c06517] text-white w-full rounded-2xl font-black uppercase tracking-wide text-xs h-12 shadow-lg shadow-[#D97724]/25 border-none flex items-center justify-center gap-2 cursor-pointer transition-all"
                        >
                            <span wire:loading.remove wire:target="confirmarReserva">
                                <x-mary-icon name="o-paper-airplane" class="w-4 h-4 inline" />
                                {{ $payment_mode === 'total' ? 'Confirmar y Abonar Total ($ ' . number_format($this->totalPrice, 0, ',', '.') . ')' : 'Confirmar y Abonar Seña ($ ' . number_format($this->depositAmount, 0, ',', '.') . ')' }}
                            </span>
                            <span wire:loading wire:target="confirmarReserva">
                                <span class="loading loading-spinner loading-xs"></span>
                                Registrando solicitud...
                            </span>
                        </button>

                    </div>

                </div>

            </div>
        @endif

    </main>
</div>

