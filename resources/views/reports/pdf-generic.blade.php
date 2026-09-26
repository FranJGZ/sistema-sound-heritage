<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Reporte Gerencial' }} - Sound Heritage</title>
    <style>
        * { box-sizing: border-box; font-family: 'Helvetica', 'Arial', sans-serif; }
        body { font-size: 11px; color: #1e293b; margin: 0; padding: 15px; }
        .header {
            border-bottom: 3px solid #b48d56;
            padding-bottom: 12px;
            margin-bottom: 15px;
            width: 100%;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0b1031;
            letter-spacing: 1px;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }
        .report-box {
            text-align: right;
        }
        .report-title {
            font-size: 15px;
            font-weight: bold;
            color: #b48d56;
            text-transform: uppercase;
            margin: 0;
        }
        .report-meta {
            font-size: 9.5px;
            color: #475569;
            margin-top: 4px;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 15px -8px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0b1031;
            padding: 8px 12px;
            border-radius: 4px;
        }
        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #0b1031;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th {
            background-color: #0b1031;
            color: #ffffff;
            text-align: left;
            padding: 7px 8px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #0b1031;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            color: #334155;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8.5px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $store = \App\Models\Store::first();
        $seq = str_pad((string) \Illuminate\Support\Facades\Cache::increment('sh_pdf_report_seq'), 4, '0', STR_PAD_LEFT);
        $reportCode = $reportCode ?? ('REP-' . $seq . '-' . now()->format('dmY-His'));
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <p class="brand-title">{{ $store->company_name ?? 'SOUND HERITAGE' }}</p>
                    <div class="brand-subtitle">
                        CUIT: {{ $store->tax_id ?? '30-11223344-5' }} | {{ $store->tax_condition ?? 'Responsable Inscripto' }}<br>
                        Dirección: {{ $store->address ?? 'Calle Colón 1234' }} | Tel: {{ $store->phone ?? '3764123456' }}
                    </div>
                </td>
                <td class="report-box" style="width: 45%;">
                    <p class="report-title">{{ $title ?? 'Reporte del Sistema' }}</p>
                    <div class="report-meta">
                        <strong>Reporte N°:</strong> {{ $reportCode }}<br>
                        <strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y H:i:s') }} hs<br>
                        <strong>Emitido por:</strong> {{ auth()->user()->name ?? 'Administrador' }}<br>
                        <strong>Registros incluidos:</strong> {{ count($rows ?? []) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($summary))
        <table class="summary-table">
            <tr>
                @foreach($summary as $label => $value)
                    <td class="summary-card">
                        <div class="summary-label">{{ $label }}</div>
                        <div class="summary-value">{{ $value }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 15px;">
                        No se encontraron registros con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento oficial de uso interno generado por el sistema ERP Sound Heritage — {{ now()->format('Y') }}
    </div>
</body>
</html>