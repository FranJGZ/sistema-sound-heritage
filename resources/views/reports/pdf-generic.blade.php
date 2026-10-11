<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Reporte' }}</title>
    <style>
        /* Reserva 155px arriba para que entren el encabezado y las cajas de resumen sin pisar la tabla */
        @page { margin: 155px 30px 45px 30px; }

        body { font-family: sans-serif; font-size: 10px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #0b1031; color: white; text-align: left; padding: 6px; font-size: 9px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .caja-resumen { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0b1031; padding: 5px 8px; }

        /* Fija todo el bloque superior (logo, datos, filtros y cajas) en la parte superior de cada página */
        header { position: fixed; top: -140px; left: 0; right: 0; }
        footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 9px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 4px; }

        .numero-pagina:after { content: counter(page); }
    </style>
</head>
<body>

    <!-- BLOQUE SUPERIOR COMPLETO: SE REPITE EN TODAS LAS HOJAS -->
    <header>
        <!-- 1. Encabezado institucional -->
        <table style="border-bottom: 2.5px solid #b48d56; margin-bottom: 6px;">
            <tr>
                <td style="border: none; padding: 0 0 4px 0;">
                    <h2 style="color: #0b1031; margin: 0; font-size: 15px;">SOUND HERITAGE</h2>
                    <small style="font-size: 8.5px; color: #475569;">
                        CUIT: 30-11223344-5 | Responsable Inscripto | Dir: Calle Colón 1234 | Tel: 3764123456
                    </small>
                </td>
                <td style="border: none; padding: 0 0 4px 0; text-align: right;">
                    <h3 style="color: #b48d56; margin: 0; font-size: 13px;">{{ $title ?? 'Reporte del Sistema' }}</h3>
                    <small style="font-size: 8.5px;">
                        <strong>Fecha:</strong> {{ now()->timezone(config('app.timezone', 'America/Argentina/Buenos_Aires'))->format('d/m/Y H:i') }} hs |
                        <strong>Usuario:</strong> {{ auth()->user()->name ?? 'Administrador' }} |
                        <strong>Registros:</strong> {{ count($rows ?? []) }}
                    </small>
                </td>
            </tr>
        </table>

        <!-- 2. Filtros aplicados -->
        @if(!empty($filtros_aplicados))
            <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; margin-bottom: 6px; font-size: 8.5px; color: #334155;">
                <strong style="color: #0b1031;">Filtros aplicados:</strong> {{ $filtros_aplicados }}
            </div>
        @endif

        <!-- 3. Cajas de resumen -->
        @if(!empty($summary))
            <table style="margin-bottom: 0;">
                <tr>
                    @foreach($summary as $label => $value)
                        <td class="caja-resumen" style="border-bottom: none;">
                            <small style="font-size: 8px; color: #64748b; text-transform: uppercase;">{{ $label }}</small><br>
                            <strong style="font-size: 12px; color: #0b1031;">{{ $value }}</strong>
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif
    </header>

    <!-- PIE DE PÁGINA FIJO -->
    <footer>
        Sound Heritage — Documento de uso interno confidencial | Página <span class="numero-pagina"></span>
    </footer>

    <!-- TABLA DE DATOS -->
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(count($rows) > 0)
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 12px;">
                        No se encontraron registros para los filtros seleccionados.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>