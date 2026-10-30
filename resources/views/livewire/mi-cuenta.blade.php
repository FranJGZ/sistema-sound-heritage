<div
    class="min-h-screen bg-[#F3EFE6] dark:bg-[#181715] text-[#181715] dark:text-[#F3EFE6] font-sans antialiased relative transition-colors duration-200 flex flex-col justify-between"
>
    <!-- ========================================================================= -->
    <!-- CONTENIDO PRINCIPAL: DASHBOARD Y SUBSECCIONES                             -->
    <!-- ========================================================================= -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 flex-1 w-full space-y-6">
        
        <!-- BARRA SUPERIOR DE NAVEGACIÓN DE SECCIÓN -->
        <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
            @if($activeSection === 'dashboard')
                <div class="flex items-center gap-2">
                    <a href="/" class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300 hover:text-[#D97724] font-bold text-sm cursor-pointer transition-colors">
                        <x-mary-icon name="o-chevron-left" class="w-5 h-5 inline" />
                        <span>Volver a la Tienda</span>
                    </a>
                </div>
                <div class="text-xs text-zinc-400 font-semibold hidden sm:block">
                    Mi cuenta / Mi Perfil
                </div>
            @else
                <button 
                    type="button" 
                    wire:click="setSection('dashboard')" 
                    class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300 hover:text-[#D97724] font-bold text-sm cursor-pointer transition-colors"
                >
                    <x-mary-icon name="o-chevron-left" class="w-5 h-5" />
                    <span>Volver a Mi perfil</span>
                </button>
            @endif
        </div>

        <!-- ALERTA DE ÉXITO -->
        @if($successMessage)
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2 shadow-xs">
                <x-mary-icon name="o-check-circle" class="w-5 h-5 shrink-0" />
                <span>{{ $successMessage }}</span>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA A: MI PERFIL (DASHBOARD GRID - FOTO 3)                          -->
        <!-- ===================================================================== -->
        @if($activeSection === 'dashboard')
            @php
                $words = preg_split('/\s+/', trim(Auth::user()->name));
                $initials = '';
                foreach (array_slice($words, 0, 2) as $w) {
                    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
                }
                $initials = $initials ?: 'SH';
                $isEmailVerified = Auth::user()->hasVerifiedEmail();
                $hasIncompleteProfile = empty($phone) || !$isEmailVerified;
            @endphp

            <div class="space-y-6">
                <!-- HEADER DE PERFIL (FOTO 3) -->
                <div class="flex items-center gap-4 py-2">
                    <div class="w-16 h-16 rounded-full bg-zinc-200 dark:bg-zinc-800 border-2 border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-xl font-black text-zinc-800 dark:text-zinc-100 shadow-xs shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight truncate">
                            {{ Auth::user()->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 truncate">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>

                <!-- ALERTA EMAIL NO VERIFICADO (FOTO 3) -->
                @if(!$isEmailVerified)
                    <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-white dark:bg-[#201E1C] border border-zinc-200 dark:border-zinc-800 border-l-4 border-l-[#D97724] flex items-center justify-between shadow-xs transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                            <span class="text-xs sm:text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                Validá tu e-mail para mantener tu cuenta segura
                            </span>
                            <button 
                                type="button" 
                                wire:click="reenviarVerificacionEmail" 
                                class="text-xs font-bold text-[#D97724] hover:underline cursor-pointer flex items-center gap-1"
                            >
                                <span wire:loading.remove wire:target="reenviarVerificacionEmail">Reenviar correo de verificación</span>
                                <span wire:loading wire:target="reenviarVerificacionEmail">Enviando enlace...</span>
                            </button>
                        </div>
                        <button type="button" @click="show = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm font-bold p-1">
                            ✕
                        </button>
                    </div>
                @endif

                <!-- GRILLA DE 6 TARJETAS (FOTO 3) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    
                    <!-- CARD 1: INFORMACIÓN DE TU PERFIL -->
                    <div 
                        wire:click="setSection('datos')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-identification" class="w-5 h-5" />
                            </div>
                            @if($hasIncompleteProfile)
                                <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-black flex items-center justify-center">
                                    !
                                </span>
                            @endif
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Información de tu perfil
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Mis cuentas / Datos personales y de tu cuenta.
                            </p>
                        </div>
                    </div>

                    <!-- CARD 2: SEGURIDAD -->
                    <div 
                        wire:click="setSection('seguridad')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-lock-closed" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Seguridad
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Seguridad de la cuenta y contraseñas.
                            </p>
                        </div>
                    </div>

                    <!-- CARD 3: DIRECCIONES -->
                    <div 
                        wire:click="setSection('domicilios')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-map-pin" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Direcciones
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                                Mis domicilios guardados{{ !empty($address) ? ": $address" : ' en tu cuenta.' }}
                            </p>
                        </div>
                    </div>

                    <!-- CARD 4: MIS FACTURAS -->
                    <div 
                        wire:click="setSection('facturas')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-document-chart-bar" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Mis Facturas
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Comprobantes fiscales y tickets PDF.
                            </p>
                        </div>
                    </div>

                    <!-- CARD 5: MIS RESERVAS -->
                    <div 
                        wire:click="setSection('reservas')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-calendar-days" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Mis reservas
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Historial y estado de fechas en el auditorio.
                            </p>
                        </div>
                    </div>

                    <!-- CARD 6: MIS ENTRADAS -->
                    <div 
                        wire:click="setSection('entradas')"
                        class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs hover:border-[#D97724] hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative"
                    >
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 group-hover:text-[#D97724] transition-colors">
                                <x-mary-icon name="o-ticket" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-[#D97724] transition-colors">
                                Mis Entradas
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Pases de eventos y recitales con QR.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA B: INFORMACIÓN DE TU PERFIL (3 BLOQUES - FOTO 1)                -->
        <!-- ===================================================================== -->
        @if($activeSection === 'datos')
            @php
                $isEmailVerified = Auth::user()->hasVerifiedEmail();
                $slugUsername = strtoupper(Str::slug(Auth::user()->name, '')) ?: 'USUARIO';
            @endphp

            <div class="space-y-6">
                <!-- TÍTULO Y SUBTÍTULO (FOTO 1) -->
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">
                        Información de tu perfil
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        Podés agregar, modificar o corregir tu información personal y los datos de la cuenta.
                    </p>
                </div>

                <!-- BLOQUE 1: INFORMACIÓN PERSONAL (FOTO 1) -->
                <div class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">
                        Información personal
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        <!-- Nombre y apellido -->
                        <div 
                            wire:click="openEditModal"
                            class="py-3.5 flex items-center justify-between cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 -mx-2 px-2 rounded-xl transition-colors group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-mary-icon name="o-identification" class="w-5 h-5 text-zinc-400 group-hover:text-[#D97724] transition-colors shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ Auth::user()->name }}
                                    </p>
                                    <p class="text-[11px] text-zinc-400">Nombre y apellido.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <x-mary-icon name="o-check" class="w-3 h-3 stroke-[3]" />
                                </span>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition-colors" />
                            </div>
                        </div>

                        <!-- Número de DNI -->
                        <div 
                            wire:click="openEditModal"
                            class="py-3.5 flex items-center justify-between cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 -mx-2 px-2 rounded-xl transition-colors group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-mary-icon name="o-identification" class="w-5 h-5 text-zinc-400 group-hover:text-[#D97724] transition-colors shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ $customer->document_number ?? 'No registrado' }}
                                    </p>
                                    <p class="text-[11px] text-zinc-400">Número de DNI.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <x-mary-icon name="o-check" class="w-3 h-3 stroke-[3]" />
                                </span>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition-colors" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 2: DATOS FISCALES (FOTO 1 - SOLO BLOQUEADO) -->
                <div class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">
                        Datos fiscales
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        <!-- Condición fiscal -->
                        <div class="py-3.5 flex items-center justify-between -mx-2 px-2 rounded-xl">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-mary-icon name="o-document-text" class="w-5 h-5 text-zinc-400 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ $tax_condition ?: 'Consumidor final' }}
                                    </p>
                                    <p class="text-[11px] text-zinc-400">Condición fiscal (Protegido por AFIP).</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <x-mary-icon name="o-check" class="w-3 h-3 stroke-[3]" />
                                </span>
                                <x-mary-icon name="o-lock-closed" class="w-4 h-4 text-zinc-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 3: DATOS DE LA CUENTA (FOTO 1) -->
                <div class="bg-white dark:bg-[#201E1C] rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">
                        Datos de la cuenta
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        <!-- E-mail -->
                        <div class="py-3.5 flex items-center justify-between -mx-2 px-2 rounded-xl">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-mary-icon name="o-envelope" class="w-5 h-5 text-zinc-400 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ Auth::user()->email }}
                                    </p>
                                    <p class="text-[11px] text-zinc-400">
                                        {{ $isEmailVerified ? 'E-mail verificado.' : 'E-mail pendiente de verificación.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($isEmailVerified)
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                        <x-mary-icon name="o-check" class="w-3 h-3 stroke-[3]" />
                                    </span>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="reenviarVerificacionEmail"
                                        class="text-xs font-bold text-[#D97724] hover:underline flex items-center gap-1"
                                    >
                                        Validar
                                    </button>
                                    <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-black flex items-center justify-center">
                                        !
                                    </span>
                                @endif
                                <button type="button" wire:click="openEditModal" class="p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                                    <x-mary-icon name="o-chevron-right" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Teléfono / WhatsApp -->
                        <div 
                            wire:click="openEditModal"
                            class="py-3.5 flex items-center justify-between cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 -mx-2 px-2 rounded-xl transition-colors group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-mary-icon name="o-phone" class="w-5 h-5 text-zinc-400 group-hover:text-[#D97724] transition-colors shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ $customer->phone ?: 'No registrado' }}
                                    </p>
                                    <p class="text-[11px] text-zinc-400">Número donde recibís códigos de verificación y comunicaciones.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if(!empty($customer->phone))
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                        <x-mary-icon name="o-check" class="w-3 h-3 stroke-[3]" />
                                    </span>
                                @endif
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition-colors" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL DE EDICIÓN DE DATOS -->
                @if($showEditModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                        <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 max-w-lg w-full space-y-5 shadow-2xl relative">
                            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                                <h3 class="text-base font-black text-zinc-900 dark:text-white">
                                    Modificar Información de Perfil
                                </h3>
                                <button type="button" wire:click="closeEditModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 font-bold text-sm">
                                    ✕
                                </button>
                            </div>

                            <form wire:submit="actualizarDatos" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nombre</label>
                                        <input 
                                            type="text" 
                                            wire:model="first_name" 
                                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                                        />
                                        <x-input-error :messages="$errors->get('first_name')" class="mt-1 text-xs text-rose-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Apellido</label>
                                        <input 
                                            type="text" 
                                            wire:model="last_name" 
                                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                                        />
                                        <x-input-error :messages="$errors->get('last_name')" class="mt-1 text-xs text-rose-500" />
                                    </div>
                                </div>

                                <!-- DNI (Incorporado al modal para poder editarlo) -->
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Número de DNI</label>
                                    <input 
                                        type="text" 
                                        wire:model="document_number" 
                                        placeholder="Ej: 38123456"
                                        class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                                    />
                                    <x-input-error :messages="$errors->get('document_number')" class="mt-1 text-xs text-rose-500" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Correo Electrónico</label>
                                    <input 
                                        type="email" 
                                        wire:model="email" 
                                        class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-500" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Teléfono o WhatsApp</label>
                                    <input 
                                        type="tel" 
                                        wire:model="phone" 
                                        placeholder="Ej: +54 376 4123456"
                                        class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                                    />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-1 text-xs text-rose-500" />
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-3">
                                    <button 
                                        type="button" 
                                        wire:click="closeEditModal" 
                                        class="btn btn-ghost btn-sm rounded-xl font-bold text-xs"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit" 
                                        class="btn bg-[#D97724] hover:bg-[#c06517] text-white btn-sm rounded-xl font-bold uppercase text-xs border-none px-5"
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA C: MIS RESERVAS                                                 -->
        <!-- ===================================================================== -->
        @if($activeSection === 'reservas')
            <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white">Tus Reservas de Salón</h2>
                    <p class="text-xs text-zinc-500">Historial y estado de tus fechas reservadas en el auditorio.</p>
                </div>

                <div class="space-y-4">
                    @forelse($reservas as $res)
                        <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase text-[#D97724]">{{ $res->type }}</span>
                                    <span class="text-zinc-400">•</span>
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        {{ \Carbon\Carbon::parse($res->date)->translatedFormat('l d \d\e F \d\e Y') }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500">
                                    Turno: <strong class="text-zinc-800 dark:text-zinc-200">{{ $res->shift }}</strong> · Asistentes: <strong class="text-zinc-800 dark:text-zinc-200">{{ $res->attendees_count }}</strong>
                                </p>
                                @if(!empty($res->notes))
                                    <p class="text-xs text-zinc-400 italic">"{{ $res->notes }}"</p>
                                @endif
                            </div>

                            <div class="sm:text-right space-y-1">
                                <div class="text-base font-black text-zinc-900 dark:text-white">
                                    $ {{ number_format($res->price, 0, ',', '.') }}
                                </div>
                                @if($res->deposit_amount > 0)
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block">
                                        Seña abonada: ${{ number_format($res->deposit_amount, 0, ',', '.') }}
                                    </span>
                                @endif
                                <span class="inline-block text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ $res->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ $res->status === 'confirmed' ? 'Confirmada' : 'Pendiente' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-zinc-400 space-y-3">
                            <x-mary-icon name="o-calendar-days" class="w-12 h-12 mx-auto stroke-1 text-zinc-300 dark:text-zinc-700" />
                            <p class="text-xs font-semibold">Aún no registrás reservas de sala.</p>
                            <a href="{{ route('reservar-salon') }}" class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none">
                                Reservar Salón Ahora
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA D: MIS FACTURAS (EXACTO FOTO 1 USUARIO)                         -->
        <!-- ===================================================================== -->
        @if($activeSection === 'facturas')
            <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white">Tus Facturas y Comprobantes</h2>
                    <p class="text-xs text-zinc-500">Descargá tus tickets fiscales electrónicos en formato PDF.</p>
                </div>

                <div class="space-y-4">
                    @forelse($facturas as $fc)
                        <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase text-[#D97724]">
                                        FACTURA {{ $fc->invoice_type }}
                                    </span>
                                    <span class="text-xs font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                        Nº {{ str_pad((string)$fc->point_of_sale, 4, '0', STR_PAD_LEFT) }}-{{ str_pad((string)$fc->receipt_number, 8, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-400">
                                    Fecha de emisión: {{ \Carbon\Carbon::parse($fc->issue_date)->format('d/m/Y') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-[10px] text-zinc-400 block uppercase font-bold">TOTAL</span>
                                    <span class="text-base font-black text-zinc-900 dark:text-white">
                                        $ {{ number_format($fc->total, 0, ',', '.') }}
                                    </span>
                                </div>

                                <button 
                                    type="button" 
                                    wire:click="descargarFacturaPdf({{ $fc->id }})"
                                    class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none flex items-center gap-1.5 shadow-sm"
                                >
                                    <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
                                    <span>TICKET PDF</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-zinc-400 space-y-3">
                            <x-mary-icon name="o-document-chart-bar" class="w-12 h-12 mx-auto stroke-1 text-zinc-300 dark:text-zinc-700" />
                            <p class="text-xs font-semibold">No se encontraron facturas asociadas a tu cuenta.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA E: MIS ENTRADAS                                                 -->
        <!-- ===================================================================== -->
        @if($activeSection === 'entradas')
            <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white">Tus Entradas para Shows</h2>
                    <p class="text-xs text-zinc-500">Pases adquiridos para los recitales en vivo en el Auditorio Sound Heritage.</p>
                </div>

                <div class="space-y-4">
                    @forelse($entradas as $ent)
                        <div class="p-5 rounded-2xl bg-zinc-50 dark:bg-[#181715] border border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <span class="text-xs font-black uppercase text-[#D97724]">
                                    Pase Individual · Ticket #{{ str_pad((string)$ent->id, 6, '0', STR_PAD_LEFT) }}
                                </span>
                                <h4 class="text-sm font-bold text-zinc-900 dark:text-white">
                                    {{ $ent->event->name ?? 'Recital en Vivo' }}
                                </h4>
                                <p class="text-xs text-zinc-400 flex items-center gap-2">
                                    <span>{{ \Carbon\Carbon::parse($ent->event->date)->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span>{{ substr($ent->event->time, 0, 5) }} hs</span>
                                    <span>•</span>
                                    <span>{{ $ent->event->venue->name ?? 'Auditorio Sound Heritage' }}</span>
                                </p>
                            </div>

                            <button 
                                type="button" 
                                wire:click="descargarEntradaPdf({{ $ent->id }})"
                                class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none flex items-center gap-1.5 shadow-sm"
                            >
                                <x-mary-icon name="o-qr-code" class="w-4 h-4" />
                                <span>Descargar Entrada QR</span>
                            </button>
                        </div>
                    @empty
                        <div class="py-12 text-center text-zinc-400 space-y-3">
                            <x-mary-icon name="o-ticket" class="w-12 h-12 mx-auto stroke-1 text-zinc-300 dark:text-zinc-700" />
                            <p class="text-xs font-semibold">Aún no adquiriste entradas para los recitales.</p>
                            <a href="{{ route('eventos') }}" class="btn btn-sm bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none">
                                Ver Cartelera en Vivo
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA F: DIRECCIONES (MIS DOMICILIOS)                                 -->
        <!-- ===================================================================== -->
        @if($activeSection === 'domicilios')
            <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white">Tus Direcciones de Entrega</h2>
                    <p class="text-xs text-zinc-500">Configurá el domicilio para despachos de instrumentos y equipos.</p>
                </div>

                <form wire:submit="actualizarDomicilio" class="space-y-4 max-w-xl">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                            Dirección de Entrega (Calle, Altura, Piso / Dpto)
                        </label>
                        <input 
                            type="text" 
                            wire:model="address" 
                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                            placeholder="Ej: Av. San Martín 1420, 3° B" 
                        />
                        <x-input-error :messages="$errors->get('address')" class="mt-1 text-xs text-rose-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                            Ciudad / Localidad
                        </label>
                        <select 
                            wire:model="city_id" 
                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]"
                        >
                            <option value="">Seleccionar ciudad...</option>
                            @foreach($ciudades as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('city_id')" class="mt-1 text-xs text-rose-500" />
                    </div>

                    <button 
                        type="submit" 
                        class="btn bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none px-6"
                    >
                        Guardar Domicilio
                    </button>
                </form>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- VISTA G: SEGURIDAD Y CAMBIO DE CONTRASEÑA                             -->
        <!-- ===================================================================== -->
        @if($activeSection === 'seguridad')
            <div class="bg-white dark:bg-[#201E1C] rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white">Seguridad de la cuenta</h2>
                    <p class="text-xs text-zinc-500">Actualizá tu contraseña de acceso para mantener tu cuenta protegida.</p>
                </div>

                <form wire:submit="actualizarContrasena" class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Contraseña Actual</label>
                        <input 
                            type="password" 
                            wire:model="current_password" 
                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                            placeholder="••••••••" 
                        />
                        <x-input-error :messages="$errors->get('current_password')" class="mt-1 text-xs text-rose-500 font-semibold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Nueva Contraseña</label>
                        <input 
                            type="password" 
                            wire:model="new_password" 
                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                            placeholder="••••••••" 
                        />
                        <x-input-error :messages="$errors->get('new_password')" class="mt-1 text-xs text-rose-500 font-semibold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Confirmar Nueva Contraseña</label>
                        <input 
                            type="password" 
                            wire:model="new_password_confirmation" 
                            class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-[#D97724]" 
                            placeholder="••••••••" 
                        />
                    </div>

                    <button 
                        type="submit" 
                        class="btn bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl font-bold uppercase text-xs border-none px-6 mt-2"
                    >
                        Actualizar Contraseña
                    </button>
                </form>
            </div>
        @endif

    </main>
</div>
