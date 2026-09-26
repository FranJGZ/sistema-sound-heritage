<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Fiscal {{ $invoice->invoice_type }} {{ $invoice->point_of_sale }}-{{ $invoice->receipt_number }}</title>
    <style>
        @page {
            margin: 12px 14px;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            color: #111111;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #333333;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 2px solid #111111;
            border-bottom: 1px solid #111111;
            height: 2px;
            margin: 6px 0;
        }
        .store-title {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .store-sub {
            font-size: 9px;
            color: #333333;
        }
        .letter-box {
            display: inline-block;
            border: 2px solid #111111;
            padding: 2px 10px;
            margin: 5px 0;
            text-align: center;
        }
        .letter-box .letter {
            font-size: 18px;
            font-weight: bold;
            line-height: 1;
        }
        .letter-box .code {
            font-size: 8px;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .items-table th {
            font-size: 9px;
            border-bottom: 1px dashed #333333;
            padding-bottom: 3px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 3px 0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .total-box {
            font-size: 13px;
            font-weight: bold;
            padding: 4px 0;
        }
        .status-canceled {
            border: 2px solid #000;
            padding: 3px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 6px 0;
        }
        .barcode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            letter-spacing: -1px;
            margin-top: 6px;
        }
        .footer-note {
            font-size: 8.5px;
            color: #333333;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @php
        $total = (float) $invoice->total;
        $neto = $total / 1.21;
        $iva = $total - $neto;
        $codAfip = $invoice->invoice_type === 'A' ? '001' : ($invoice->invoice_type === 'B' ? '006' : '011');
        $pagos = $invoice->paymentDetails->map(fn ($p) => $p->paymentMethod?->name)->filter()->implode(' / ') ?: 'Efectivo';
        $cae = '7439' . str_pad((string) $invoice->id, 10, '0', STR_PAD_LEFT);
    @endphp

    <!-- CABECERA DEL EMISOR -->
    <div class="text-center">
        <div class="store-title">SOUND HERITAGE</div>
        <div class="store-sub">{{ $invoice->store?->company_name ?? 'Sound Heritage Casa Central' }}</div>
        <div class="store-sub">{{ $invoice->store?->address ?? 'Av. Corrientes 1234, CABA' }}</div>
        <div class="store-sub">Tel: {{ $invoice->store?->phone ?? '011-4555-0199' }}</div>
        <div class="store-sub">CUIT: {{ $invoice->store?->tax_id ?? '30-71558942-9' }}</div>
        <div class="store-sub">IVA: {{ $invoice->store?->tax_condition ?? 'Responsable Inscripto' }}</div>
        <div class="store-sub">
            Inicio Act.: {{ $invoice->store?->start_date ? \Carbon\Carbon::parse($invoice->store->start_date)->format('d/m/Y') : '01/03/2020' }}
        </div>

        <div class="letter-box">
            <span class="letter">{{ $invoice->invoice_type }}</span>
            <span class="code">COD. {{ $codAfip }}</span>
        </div>

        <div class="bold" style="font-size: 11px;">
            TIQUE FACTURA «{{ $invoice->invoice_type }}»
        </div>
        <div class="bold" style="font-size: 11px;">
            N° {{ $invoice->point_of_sale }}-{{ $invoice->receipt_number }}
        </div>
    </div>

    @if($invoice->trashed())
        <div class="status-canceled">*** COMPROBANTE ANULADO ***</div>
    @endif

    <div class="divider"></div>

    <!-- FECHA Y VENDEDOR -->
    <table class="meta-table">
        <tr>
            <td><span class="bold">FECHA:</span> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
            <td class="text-right"><span class="bold">HORA:</span> {{ $invoice->created_at ? $invoice->created_at->format('H:i:s') : now()->format('H:i:s') }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="bold">VENCIMIENTO:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="bold">CAJERO/VEND.:</span> {{ $invoice->employee ? $invoice->employee->last_name . ', ' . $invoice->employee->first_name : '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- DATOS DEL CLIENTE -->
    <table class="meta-table">
        <tr>
            <td colspan="2">
                <span class="bold">CLIENTE:</span>
                {{ $invoice->customer ? $invoice->customer->last_name . ', ' . $invoice->customer->first_name : 'Consumidor Final' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="bold">CUIT/DNI:</span>
                {{ $invoice->customer?->tax_id ?: ($invoice->customer?->document_number ?? '-') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="bold">COND. IVA:</span>
                {{ $invoice->customer?->tax_condition ?? 'Consumidor Final' }}
            </td>
        </tr>
        @if(filled($invoice->customer?->address))
            <tr>
                <td colspan="2">
                    <span class="bold">DOMICILIO:</span> {{ $invoice->customer->address }}
                </td>
            </tr>
        @endif
    </table>

    <div class="double-divider"></div>

    <!-- DETALLE DE PRODUCTOS -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 68%;">CANT x P.UNIT / DESCRIPCIÓN</th>
                <th class="text-right" style="width: 32%;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceDetails as $detail)
                <tr>
                    <td class="text-left">
                        <div>{{ $detail->quantity }} x ${{ number_format((float) $detail->unit_price, 2, ',', '.') }}</div>
                        <div class="bold">{{ $detail->product?->name ?? 'Producto' }}</div>
                        @if(filled($detail->product?->code))
                            <div style="font-size: 8px; color: #444;">COD: {{ $detail->product->code }}</div>
                        @endif
                    </td>
                    <td class="text-right bold" style="vertical-align: bottom;">
                        ${{ number_format((float) $detail->subtotal, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="double-divider"></div>

    <!-- SUBTOTALES E IVA -->
    <table class="meta-table">
        @if($invoice->invoice_type === 'A')
            <tr>
                <td>SUBTOTAL NETO GRAVADO:</td>
                <td class="text-right">${{ number_format($neto, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>IVA INSCRIPTO (21%):</td>
                <td class="text-right">${{ number_format($iva, 2, ',', '.') }}</td>
            </tr>
        @else
            <tr>
                <td>SUBTOTAL:</td>
                <td class="text-right">${{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="total-box">
            <td style="padding-top: 4px; font-size: 13px;">TOTAL:</td>
            <td class="text-right" style="padding-top: 4px; font-size: 13px;">${{ number_format($total, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- MEDIOS DE PAGO -->
    <table class="meta-table">
        <tr>
            <td><span class="bold">MEDIO DE PAGO:</span></td>
            <td class="text-right bold">{{ $pagos }}</td>
        </tr>
    </table>

    @if($invoice->invoice_type !== 'A')
        <div class="divider"></div>
        <div style="font-size: 8px;">
            Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)<br>
            <span class="bold">IVA Contenido: ${{ number_format($iva, 2, ',', '.') }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <!-- PIE FISCAL Y CAE -->
    <div class="text-center">
        <div style="font-size: 9px;">
            <span class="bold">CAE N°:</span> {{ $cae }}<br>
            <span class="bold">Vto. CAE:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
        </div>
        <div class="barcode">||| |||| || | |||| ||| || |||| |||</div>
        <div style="font-size: 8px;">{{ $invoice->point_of_sale }}{{ $invoice->receipt_number }}{{ $cae }}</div>

        <div class="footer-note">
            ¡Gracias por tu compra en <strong>Sound Heritage</strong>!<br>
            Conserve este ticket como comprobante de garantía.
        </div>
    </div>
</body>
</html>