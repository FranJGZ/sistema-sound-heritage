<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Analítico del Dashboard - Sound Heritage</title>
    <style>
        @page { margin: 125px 28px 45px 28px; }
        body { font-family: sans-serif; font-size: 9px; color: #1e293b; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background: #0b1031; color: white; text-align: left; padding: 5px 6px; font-size: 8.5px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; }
        header { position: fixed; top: -110px; left: 0; right: 0; }
        footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 8px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 4px; }
        .numero-pagina:after { content: counter(page); }

        .kpi-box { background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #b48d56; padding: 6px 8px; }
        .section-title { font-size: 11px; font-weight: bold; color: #0b1031; border-bottom: 1.5px solid #b48d56; padding-bottom: 3px; margin-top: 10px; margin-bottom: 6px; }
        
        .bar-container { background: #e2e8f0; border-radius: 3px; height: 10px; width: 100%; overflow: hidden; }
        .bar-fill { height: 10px; border-radius: 3px; }
        .badge { display: inline-block; padding: 2px 5px; font-size: 7.5px; border-radius: 3px; font-weight: bold; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

    <!-- ENCABEZADO INSTITUCIONAL FIJO EN TODAS LAS HOJAS -->
    <header>
        <table style="border-bottom: 2.5px solid #b48d56; margin-bottom: 6px;">
            <tr>
                <td style="border: none; padding: 0 0 4px 0;">
                    <h2 style="color: #0b1031; margin: 0; font-size: 14px;">SOUND HERITAGE</h2>
                    <small style="font-size: 8px; color: #475569;">
                        CUIT: 30-11223344-5 | Responsable Inscripto | Dir: Calle Colón 1234 | Tel: 3764123456
                    </small>
                </td>
                <td style="border: none; padding: 0 0 4px 0; text-align: right;">
                    <h3 style="color: #b48d56; margin: 0; font-size: 12px;">Reporte Ejecutivo del Dashboard</h3>
                    <small style="font-size: 8px;">
                        <strong>Fecha:</strong> {{ now()->timezone(config('app.timezone', 'America/Argentina/Buenos_Aires'))->format('d/m/Y H:i') }} hs |
                        <strong>Generado por:</strong> {{ auth()->user()->name ?? 'Administrador' }}
                    </small>
                </td>
            </tr>
        </table>
    </header>

    <!-- PIE DE PÁGINA FIJO -->
    <footer>
        Sound Heritage &copy; {{ date('Y') }} — Reporte Analítico y Métricas de Tienda | Página <span class="numero-pagina"></span>
    </footer>

    <!-- 1. BLOQUE DE TARJETAS KPI -->
    <table>
        <tr>
            <td class="kpi-box" style="width: 25%; border-bottom: none;">
                <small style="color: #64748b; font-size: 7.5px; text-transform: uppercase;">Clientes Activos</small><br>
                <strong style="font-size: 14px; color: #10b981;">{{ number_format($conversionRate, 1) }}%</strong><br>
                <span style="font-size: 7.5px; color: #64748b;">{{ $activeBuyerCount }} de {{ $totalCustomers }} con compras</span>
            </td>
            <td style="width: 2%; border: none;"></td>
            <td class="kpi-box" style="width: 25%; border-bottom: none; border-left-color: #0284c7;">
                <small style="color: #64748b; font-size: 7.5px; text-transform: uppercase;">Ítems por Venta</small><br>
                <strong style="font-size: 14px; color: #0284c7;">{{ number_format($avgItemsPerOrder, 1) }}</strong><br>
                <span style="font-size: 7.5px; color: #64748b;">{{ $totalUnitsSold }} u. en {{ $activeInvoices }} ventas</span>
            </td>
            <td style="width: 2%; border: none;"></td>
            <td class="kpi-box" style="width: 25%; border-bottom: none; border-left-color: #ef4444;">
                <small style="color: #64748b; font-size: 7.5px; text-transform: uppercase;">Tasa de Anulación</small><br>
                <strong style="font-size: 14px; color: #ef4444;">{{ number_format($cancelRate, 1) }}%</strong><br>
                <span style="font-size: 7.5px; color: #64748b;">{{ $trashedInvoices }} anuladas</span>
            </td>
            <td style="width: 2%; border: none;"></td>
            <td class="kpi-box" style="width: 25%; border-bottom: none; border-left-color: #10b981;">
                <small style="color: #64748b; font-size: 7.5px; text-transform: uppercase;">Ticket Promedio</small><br>
                <strong style="font-size: 14px; color: #0b1031;">${{ number_format($avgOrderValue, 2, ',', '.') }}</strong><br>
                <span style="font-size: 7.5px; color: #64748b;">Total: ${{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <!-- 2. TOP 10 PRODUCTOS POR FACTURACIÓN -->
    <div class="section-title">1. Top 10 Productos con Mayor Facturación</div>
    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Producto</th>
                <th style="width: 20%; text-align: right;">Facturación Total</th>
                <th style="width: 45%;">Distribución Relativa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top10 as $item)
                @php
                    $pct = $maxRev > 0 ? round(($item['revenue'] / $maxRev) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $item['name'] }}</strong></td>
                    <td style="text-align: right;">${{ number_format($item['revenue'], 2, ',', '.') }}</td>
                    <td>
                        <table style="width: 100%; margin: 0; border: none;">
                            <tr>
                                <td style="border: none; padding: 0 4px 0 0; width: 80%;">
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: {{ $pct }}%; background: #3b82f6;"></div>
                                    </div>
                                </td>
                                <td style="border: none; padding: 0; width: 20%; text-align: right; font-size: 7.5px; color: #475569;">
                                    {{ $pct }}%
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. SEGMENTACIÓN DE CLIENTES Y DISTRIBUCIÓN DE TICKETS -->
    <table style="margin-top: 6px;">
        <tr>
            <!-- SEGMENTACIÓN DE CLIENTES -->
            <td style="width: 48%; vertical-align: top; border: none; padding: 0 6px 0 0;">
                <div class="section-title" style="margin-top: 0;">2. Segmentación de Clientes</div>
                <table>
                    <thead>
                        <tr>
                            <th>Segmento</th>
                            <th style="text-align: right;">Cant.</th>
                            <th style="width: 40%;">Participación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $segments = [
                                ['label' => 'Única compra (1)', 'cant' => $segOneTime, 'color' => '#9ca3af'],
                                ['label' => 'Ocasional (2-3)', 'cant' => $segOccasional, 'color' => '#3b82f6'],
                                ['label' => 'Frecuente (4-9)', 'cant' => $segRegular, 'color' => '#22c55e'],
                                ['label' => 'VIP (10+)', 'cant' => $segVip, 'color' => '#f59e0b'],
                            ];
                        @endphp
                        @foreach($segments as $s)
                            @php
                                $sPct = round(($s['cant'] / $totalSeg) * 100, 1);
                            @endphp
                            <tr>
                                <td>{{ $s['label'] }}</td>
                                <td style="text-align: right;"><strong>{{ $s['cant'] }}</strong></td>
                                <td>
                                    <table style="width: 100%; margin: 0; border: none;">
                                        <tr>
                                            <td style="border: none; padding: 0 4px 0 0; width: 75%;">
                                                <div class="bar-container">
                                                    <div class="bar-fill" style="width: {{ $sPct }}%; background: {{ $s['color'] }};"></div>
                                                </div>
                                            </td>
                                            <td style="border: none; padding: 0; width: 25%; text-align: right; font-size: 7.5px;">
                                                {{ $sPct }}%
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>

            <!-- DISTRIBUCIÓN POR MONTO DE FACTURA -->
            <td style="width: 48%; vertical-align: top; border: none; padding: 0 0 0 6px;">
                <div class="section-title" style="margin-top: 0;">3. Distribución por Rango de Monto</div>
                <table>
                    <thead>
                        <tr>
                            <th>Rango de Ticket</th>
                            <th style="text-align: right;">Ventas</th>
                            <th style="width: 40%;">Frecuencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rangos = [
                                ['rango' => '$0 - $25k', 'cant' => $r1, 'color' => '#6366f1'],
                                ['rango' => '$25k - $75k', 'cant' => $r2, 'color' => '#8b5cf6'],
                                ['rango' => '$75k - $150k', 'cant' => $r3, 'color' => '#a855f7'],
                                ['rango' => '$150k - $300k', 'cant' => $r4, 'color' => '#f59e0b'],
                                ['rango' => '$300k - $750k', 'cant' => $r5, 'color' => '#ef4444'],
                                ['rango' => '$750k+', 'cant' => $r6, 'color' => '#ec4899'],
                            ];
                            $maxTicketRango = max(array_column($rangos, 'cant')) ?: 1;
                        @endphp
                        @foreach($rangos as $rg)
                            @php
                                $rPct = round(($rg['cant'] / $maxTicketRango) * 100, 1);
                            @endphp
                            <tr>
                                <td>{{ $rg['rango'] }}</td>
                                <td style="text-align: right;"><strong>{{ $rg['cant'] }}</strong></td>
                                <td>
                                    <table style="width: 100%; margin: 0; border: none;">
                                        <tr>
                                            <td style="border: none; padding: 0 4px 0 0; width: 75%;">
                                                <div class="bar-container">
                                                    <div class="bar-fill" style="width: {{ $rPct }}%; background: {{ $rg['color'] }};"></div>
                                                </div>
                                            </td>
                                            <td style="border: none; padding: 0; width: 25%; text-align: right; font-size: 7.5px;">
                                                {{ $rg['cant'] }} vtas
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 4. VENTAS INTERANUALES (ÚLTIMOS 12 MESES) -->
    <div class="section-title">4. Evolución de Ventas (Últimos 12 Meses vs Período Anterior)</div>
    <table>
        <thead>
            <tr>
                @foreach($ventasMensuales as $vm)
                    <th style="text-align: center; font-size: 7.5px;">{{ $vm['mes'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($ventasMensuales as $vm)
                    <td style="text-align: center; font-size: 8px;">
                        <strong style="color: #3b82f6;">{{ $vm['actual'] }}</strong><br>
                        <small style="color: #94a3b8; font-size: 7px;">Ant: {{ $vm['anterior'] }}</small>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <!-- 5. PRODUCTOS EN SEGUIMIENTO Y ALERTA DE STOCK -->
    @if(isset($alertProducts) && $alertProducts->count() > 0)
        <div class="section-title">5. Alertas de Reposición y Stock Crítico (Top 10 urgencias)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Código</th>
                    <th style="width: 45%;">Producto</th>
                    <th style="width: 18%; text-align: right;">Precio Unit.</th>
                    <th style="width: 10%; text-align: center;">Stock</th>
                    <th style="width: 15%; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alertProducts as $prod)
                    <tr>
                        <td><strong>SH-{{ str_pad((string) $prod->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                        <td>{{ $prod->name }}</td>
                        <td style="text-align: right;">${{ number_format((float) $prod->price, 2, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: bold; color: {{ $prod->stock <= 0 ? '#ef4444' : '#f59e0b' }};">
                            {{ $prod->stock }} u.
                        </td>
                        <td style="text-align: center;">
                            @if($prod->stock <= 0)
                                <span class="badge badge-danger">Agotado</span>
                            @else
                                <span class="badge badge-warning">Crítico</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
