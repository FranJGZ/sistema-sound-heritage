<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Entrada #{{ str_pad($record->id, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 6px 8px;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9.5px;
            color: #000000;
            line-height: 1.25;
            background: #ffffff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        
        .header-brand {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header-sub {
            font-size: 8px;
            margin-bottom: 4px;
        }
        
        .event-title {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
            line-height: 1.15;
        }
        
        .price-line {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0 5px 0;
        }

        .highlight-box {
            background: #000000;
            color: #ffffff;
            font-weight: bold;
            padding: 1px 4px;
            display: inline-block;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 9.5px;
            vertical-align: top;
        }
        
        .cut-line-container {
            margin: 8px 0;
            text-align: center;
        }
        .cut-line {
            border-top: 1px dashed #000000;
            margin-bottom: 2px;
        }
        .cut-text {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .stub-container {
            padding-top: 2px;
        }
        .stub-badge {
            border: 1px solid #000000;
            font-size: 8.5px;
            font-weight: bold;
            padding: 2px 4px;
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .barcode-box {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-align: center;
            margin: 4px 0;
            border: 1px solid #000000;
            padding: 2px;
        }
        .footer-note {
            font-size: 7px;
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $eventName = strtoupper($record->event->name ?? 'EVENTO CULTURAL');
        $eventDate = $record->event ? \Carbon\Carbon::parse($record->event->date)->format('d-m-Y') : date('d-m-Y');
        $eventTime = $record->event && $record->event->time ? \Carbon\Carbon::parse($record->event->time)->format('H:i') : '21:00';
        $venueName = strtoupper($record->event->venue->name ?? 'SALA 1');
        $customerName = strtoupper(($record->customer->last_name ?? '') . ', ' . ($record->customer->first_name ?? ''));
        $customerDni = $record->customer->document_number ?? '-';
        $ticketNum = str_pad($record->id, 8, '0', STR_PAD_LEFT);
        $rawPrice = (float) ($record->event?->price ?? 0);
        $priceFormatted = $rawPrice > 0 ? '$' . number_format($rawPrice, 2, ',', '.') : 'GRATIS';
    @endphp

    <!-- =================================================================== -->
    <!-- 1. TALÓN DEL CLIENTE (ENTRADA PRINCIPAL)                           -->
    <!-- =================================================================== -->
    <div class="text-center">
        <div class="header-brand">SOUND HERITAGE</div>
        <div class="header-sub">ESPACIO CULTURAL & AUDITORIO</div>
        
        <div class="event-title">{{ $eventName }}</div>
        
        <div class="price-line">
            ENTRADA: {{ $priceFormatted }} <span style="font-size: 8.5px; font-weight: normal;">(Gral)</span>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">Fila: <span class="highlight-box">AG</span></td>
            <td style="width: 50%;" class="text-right">Asiento: <span class="highlight-box">AG</span></td>
        </tr>
        <tr>
            <td>{{ $eventDate }}</td>
            <td class="text-right">SALA: 1</td>
        </tr>
        <tr>
            <td>{{ $eventTime }} HS</td>
            <td class="text-right">{{ $venueName }}</td>
        </tr>
    </table>

    <table class="meta-table" style="margin-top: 3px; border-top: 1px dotted #666; padding-top: 2px;">
        <tr>
            <td class="bold">Ticket No:</td>
            <td class="text-right bold">{{ $ticketNum }}</td>
        </tr>
        <tr>
            <td>Titular:</td>
            <td class="text-right">{{ str($customerName)->limit(18) }}</td>
        </tr>
        <tr>
            <td>DNI:</td>
            <td class="text-right">{{ $customerDni }}</td>
        </tr>
        <tr>
            <td>Entradas / Pases:</td>
            <td class="text-right bold">{{ $record->quantity ?? 1 }}</td>
        </tr>
        <tr>
            <td>Total:</td>
            <td class="text-right bold">{{ '$' . number_format((float) ($record->price > 0 ? $record->price : $rawPrice * ($record->quantity ?? 1)), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Emisión:</td>
            <td class="text-right">{{ date('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td>CUIT:</td>
            <td class="text-right">30-71829401-8</td>
        </tr>
    </table>

    <div class="text-center bold" style="font-size: 8px; margin-top: 3px; letter-spacing: 0.5px;">
        [ TALÓN PARA EL CLIENTE ]
    </div>

    <!-- =================================================================== -->
    <!-- 2. LÍNEA DE TROQUELADO / CORTE                                      -->
    <!-- =================================================================== -->
    <div class="cut-line-container">
        <div class="cut-line"></div>
        <div class="cut-text">- - - [ CORTE EN PUERTA ] - - -</div>
        <div class="cut-line"></div>
    </div>

    <!-- =================================================================== -->
    <!-- 3. TALÓN DEL ESTABLECIMIENTO (CONTROL EN PUERTA)                   -->
    <!-- =================================================================== -->
    <div class="stub-container">
        <div class="stub-badge">
            TALÓN ESTABLECIMIENTO / PUERTA
        </div>

        <table class="meta-table">
            <tr>
                <td class="bold">Evento:</td>
                <td class="text-right bold">{{ str($eventName)->limit(17) }}</td>
            </tr>
            <tr>
                <td>Fecha / Hora:</td>
                <td class="text-right">{{ $eventDate }} {{ $eventTime }}</td>
            </tr>
            <tr>
                <td>Asistente:</td>
                <td class="text-right">{{ str($customerName)->limit(17) }}</td>
            </tr>
            <tr>
                <td>DNI:</td>
                <td class="text-right">{{ $customerDni }}</td>
            </tr>
            <tr>
                <td>Ticket / Valor:</td>
                <td class="text-right bold">#{{ $record->id }} • {{ $priceFormatted }}</td>
            </tr>
        </table>

        <div class="barcode-box">
            *SH-{{ str_pad($record->event_id ?? 0, 3, '0', STR_PAD_LEFT) }}-{{ $ticketNum }}*
        </div>

        <div class="footer-note">
            RETENER EN BOLETERÍA AL INGRESAR A LA SALA
        </div>
    </div>
</body>
</html>
