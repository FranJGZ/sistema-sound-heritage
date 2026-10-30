<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Reserva Confirmada! - Auditorio Sound Heritage</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F3EFE6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #181715;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3EFE6; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    
                    <!-- Encabezado Carbón con Verde Éxito / Ámbar -->
                    <tr>
                        <td style="background-color: #181715; padding: 32px 36px; text-align: left; border-bottom: 3px solid #D97724;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 900; letter-spacing: 2px; color: #ffffff; text-transform: uppercase;">
                                            SOUND HERITAGE
                                        </div>
                                        <div style="font-size: 11px; color: #D97724; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 4px;">
                                            Alquiler de Sala &amp; Auditorio
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 8px; color: #10b981; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                            CONFIRMADA
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Contenido Central -->
                    <tr>
                        <td style="padding: 36px;">
                            
                            <h1 style="font-size: 20px; font-weight: 800; margin: 0 0 12px 0; color: #181715;">
                                ¡Tu reserva ha sido Confirmada, {{ $reservation->customer->first_name }}!
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #52525b; margin: 0 0 24px 0;">
                                Nos complace informarte que la seña y los requerimientos técnicos de tu reserva <strong>#{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</strong> han sido verificados y aprobados por la administración de Sound Heritage. El auditorio queda oficialmente reservado de forma exclusiva para tu uso.
                            </p>

                            <!-- Tarjeta de Confirmación -->
                            <div style="background-color: #faf9f6; border: 1px solid #e4e4e7; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 11px; font-weight: 800; color: #D97724; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                                    Resumen del Turno Confirmado
                                </div>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; color: #181715;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #71717a;">Uso / Actividad:</td>
                                        <td style="padding: 5px 0; font-weight: 800; text-align: right;">{{ $reservation->type }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #71717a;">Fecha Reservada:</td>
                                        <td style="padding: 5px 0; font-weight: 800; color: #D97724; text-align: right;">
                                            {{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #71717a;">Horario / Turno:</td>
                                        <td style="padding: 5px 0; font-weight: 700; text-align: right;">{{ $reservation->shift }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #71717a;">Capacidad / Asistentes:</td>
                                        <td style="padding: 5px 0; font-weight: 700; text-align: right;">{{ $reservation->attendees_count }} personas</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #71717a;">Lugar:</td>
                                        <td style="padding: 5px 0; font-weight: 600; text-align: right;">{{ $reservation->venue->name ?? 'Auditorio Principal' }} (Calle Colón 1234, Posadas, Misiones)</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-top: 1px solid #e4e4e7; padding-top: 10px; margin-top: 6px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #71717a;">Total del Servicio:</td>
                                        <td style="padding: 4px 0; font-weight: 700; text-align: right;">
                                            $ {{ number_format($reservation->price, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #10b981; font-weight: 700;">Seña Registrada:</td>
                                        <td style="padding: 4px 0; font-weight: 800; color: #10b981; text-align: right;">
                                            -$ {{ number_format($reservation->deposit_amount ?: ($reservation->price * 0.3), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; font-size: 14px; font-weight: 900; color: #181715;">Saldo Pendiente en Sala:</td>
                                        <td style="padding: 4px 0; font-size: 16px; font-weight: 900; color: #D97724; text-align: right;">
                                            $ {{ number_format(max(0, $reservation->price - ($reservation->deposit_amount ?: ($reservation->price * 0.3))), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Pautas de Convivencia y Acceso -->
                            <div style="background-color: #faf9f6; border-left: 4px solid #181715; padding: 16px 20px; border-radius: 6px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 800; color: #181715; text-transform: uppercase; margin-bottom: 8px;">
                                    Protocolo de Ingreso y Prueba Técnica
                                </div>
                                <div style="font-size: 12px; color: #52525b; line-height: 1.6;">
                                    - Se recomienda llegar <strong>15 minutos antes</strong> del inicio del turno para la prueba de backline y calibración de sonido.<br>
                                    - Un operador técnico de sala estará a disposición durante todo el bloque.<br>
                                    - El saldo restante se cancela en la administración antes del ingreso a la sala (Efectivo, Tarjeta o Transferencia).
                                </div>
                            </div>

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 0;">
                                ¡Los esperamos en el auditorio para una jornada de sonido de primer nivel!
                            </p>

                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="background-color: #faf9f6; padding: 24px 36px; border-top: 1px solid #e4e4e7; text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: #181715;">
                                AUDITORIO SOUND HERITAGE S.R.L.
                            </div>
                            <div style="font-size: 10px; color: #71717a; margin-top: 4px;">
                                Calle Colón 1234, Posadas, Misiones | Contacto de Sala: +54 376 412-3456
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

