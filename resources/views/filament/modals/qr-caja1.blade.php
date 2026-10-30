@php
    $qrPath = public_path('img/qr/caja1_qr.png');
    $qrBase64 = file_exists($qrPath) ? base64_encode(file_get_contents($qrPath)) : null;
    $v = file_exists($qrPath) ? filemtime($qrPath) : time();

    $isCompleted = false;
    $amount = null;
    $referenceId = null;

    if (isset($record)) {
        if ($record instanceof \App\Models\Invoice) {
            $isCompleted = $record->payment_status === 'completed';
            $amount = number_format((float) $record->total, 2, ',', '.');
            $referenceId = $record->mp_payment_id;
        } elseif ($record instanceof \App\Models\Reservation) {
            $isCompleted = in_array($record->payment_status, ['completed', 'Seña Acreditada', 'Total Cancelado']) || $record->status === 'Confirmada';
            $montoCobro = $record->deposit_amount > 0 ? $record->deposit_amount : $record->price;
            $amount = number_format((float) $montoCobro, 2, ',', '.');
            $referenceId = $record->mp_payment_id;
        } elseif ($record instanceof \App\Models\Attendance) {
            $isCompleted = $record->payment_status === 'completed';
            $amount = number_format((float) ($record->price ?? 0), 2, ',', '.');
            $referenceId = $record->mp_payment_id;
        }
    }
@endphp

<div wire:poll.2s="checkPendingQrOrders" class="flex flex-col items-center justify-center p-2 text-center space-y-4">
    @if($isCompleted)
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 p-6 rounded-2xl w-full max-w-md shadow-sm">
            <div class="w-16 h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/30">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-black text-emerald-800 dark:text-emerald-300">¡Pago Acreditado Exitosamente!</h3>
            <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">
                El cobro fue verificado en Mercado Pago y la operación quedó confirmada.
            </p>
            @if($referenceId)
                <div class="mt-3 text-xs bg-white/70 dark:bg-zinc-800/70 py-1.5 px-3 rounded-lg inline-block font-mono text-zinc-600 dark:text-zinc-300">
                    ID Pago MP: {{ $referenceId }}
                </div>
            @endif
        </div>
    @else
        <!-- Tarjeta de Monto a Pagar -->
        @if($amount)
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 px-5 py-3 rounded-xl w-full max-w-sm flex items-center justify-between">
                <div class="text-left">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Total a Cobrar</span>
                    <div class="text-2xl font-black text-amber-900 dark:text-amber-200">${{ $amount }}</div>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    Esperando pago
                </div>
            </div>
        @endif

        <!-- Código QR visible en pantalla (Embebido en base64 para evitar caché del navegador) -->
        <div class="bg-white p-4 rounded-2xl shadow-lg border border-zinc-200 inline-block max-w-xs">
            @if($qrBase64)
                <img 
                    src="data:image/png;base64,{{ $qrBase64 }}" 
                    alt="Código QR Caja 1 - Mercado Pago" 
                    class="w-64 h-64 object-contain rounded-xl mx-auto"
                />
            @else
                <img 
                    src="/img/qr/caja1_qr.png?v={{ $v }}" 
                    alt="Código QR Caja 1 - Mercado Pago" 
                    class="w-64 h-64 object-contain rounded-xl mx-auto"
                />
            @endif
            <div class="mt-2 text-[11px] font-bold text-zinc-600 uppercase tracking-wider">
                Escanear con App Mercado Pago
            </div>
        </div>

        <div class="space-y-1">
            <h4 class="font-black text-sm text-zinc-900 dark:text-white uppercase tracking-wider">
                Caja 1 · Sound Heritage Central
            </h4>
            <p class="text-xs text-zinc-500 max-w-md">
                El cliente abre su app de Mercado Pago, toca en <strong>"Escanear QR"</strong> y apunta a la pantalla o cartel. 
                <span class="text-[#D97724] font-medium block mt-0.5">El sistema detectará el pago automáticamente en segundos.</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
            <a 
                href="/img/qr/caja1_qr_cartel.pdf?v={{ $v }}" 
                target="_blank" 
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#D97724] hover:bg-[#c06517] text-white rounded-xl text-xs font-bold shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Imprimir Cartel A4 (PDF)
            </a>

            <a 
                href="/img/qr/caja1_qr_template.png?v={{ $v }}" 
                target="_blank" 
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded-xl text-xs font-semibold transition"
            >
                Ver Cartel Completo (PNG)
            </a>
        </div>
    @endif
</div>
