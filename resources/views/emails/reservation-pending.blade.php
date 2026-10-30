<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Reserva Registrada - Sound Heritage</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F3EFE6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #181715;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3EFE6; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    
                    <!-- Encabezado Carbón -->
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
                                        <span style="display: inline-block; padding: 6px 12px; background-color: rgba(217, 119, 36, 0.15); border: 1px solid #D97724; border-radius: 8px; color: #D97724; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                            Estado: Pendiente
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
                                Solicitud de Reserva #{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #52525b; margin: 0 0 24px 0;">
                                Hola <strong>{{ $reservation->customer->first_name }}</strong>, recibimos tu solicitud para el uso de nuestro auditorio. La fecha ha quedado pre-bloqueada a la espera de la confirmación de la seña.
                            </p>

                            <!-- Ficha de la Reserva -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #faf9f6; border: 1px solid #e4e4e7; border-radius: 12px; margin-bottom: 24px; padding: 18px;">
                                <tr>
                                    <td style="padding: 6px 0; font-size: 12px; color: #71717a;">Motivo / Tipo de Uso:</td>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 800; color: #181715; text-align: right;">{{ $reservation->type }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 12px; color: #71717a;">Fecha Solicitada:</td>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #181715; text-align: right;">
                                        {{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 12px; color: #71717a;">Turno Horario:</td>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #181715; text-align: right;">{{ $reservation->shift }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 12px; color: #71717a;">Cantidad de Asistentes:</td>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #181715; text-align: right;">{{ $reservation->attendees_count }} personas</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 12px; color: #71717a;">Salón:</td>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #181715; text-align: right;">{{ $reservation->venue->name ?? 'Auditorio Principal' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border-top: 1px solid #e4e4e7; padding-top: 10px; margin-top: 6px;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; font-size: 12px; color: #71717a;">Total del Alquiler:</td>
                                    <td style="padding: 4px 0; font-size: 14px; font-weight: 800; color: #181715; text-align: right;">
                                        $ {{ number_format($reservation->price, 2, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; font-size: 13px; font-weight: 800; color: #D97724;">Seña Solicitada (30%):</td>
                                    <td style="padding: 4px 0; font-size: 16px; font-weight: 900; color: #D97724; text-align: right;">
                                        $ {{ number_format($reservation->deposit_amount ?: ($reservation->price * 0.3), 2, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; font-size: 12px; color: #71717a;">Saldo a cancelar en sala:</td>
                                    <td style="padding: 4px 0; font-size: 13px; font-weight: 700; color: #52525b; text-align: right;">
                                        $ {{ number_format($reservation->price - ($reservation->deposit_amount ?: ($reservation->price * 0.3)), 2, ',', '.') }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Instrucciones de Pago de la Seña -->
                            <div style="background-color: #faf9f6; border-left: 4px solid #D97724; padding: 16px 20px; border-radius: 6px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 800; color: #181715; text-transform: uppercase; margin-bottom: 8px;">
                                    Datos para Abonar la Seña
                                </div>
                                @if($reservation->payment_method === 'Transferencia Bancaria' || empty($reservation->payment_method))
                                    <div style="font-size: 12px; color: #52525b; line-height: 1.6;">
                                        Por favor realizá la transferencia por el monto de la seña dentro de las <strong>48 horas hábiles</strong>:<br>
                                        <strong>Banco:</strong> Banco Galicia<br>
                                        <strong>Titular:</strong> Sound Heritage S.R.L.<br>
                                        <strong>CUIT:</strong> 30-71234567-9<br>
                                        <strong>Alias:</strong> <span style="font-family: monospace; font-weight: 700; color: #D97724;">SOUND.HERITAGE.SALA</span><br>
                                        <strong>CBU:</strong> <span style="font-family: monospace; font-weight: 700;">0000003100012345678901</span><br><br>
                                        Una vez realizada la transferencia, respondé a este correo enviando el comprobante o número de operación.
                                    </div>
                                @elseif($reservation->payment_method === 'Efectivo en Mostrador')
                                    <div style="font-size: 12px; color: #52525b; line-height: 1.6;">
                                        Tenés un plazo de <strong>48 horas hábiles</strong> para acercarte a nuestro local en <strong>Calle Colón 1234, Posadas, Misiones</strong> a abonar la seña en efectivo y asegurar la fecha solicitada. Pasado ese plazo, el turno quedará nuevamente disponible en el calendario.
                                    </div>
                                @else
                                    <div style="font-size: 12px; color: #52525b; line-height: 1.6;">
                                        Tu pago electrónico se encuentra en proceso de validación. Apenas se acredite, recibirás el correo de confirmación definitiva.
                                    </div>
                                @endif
                            </div>

                            @if(!empty($reservation->notes))
                                <div style="font-size: 12px; color: #71717a; margin-bottom: 20px;">
                                    <strong>Rider / Requerimientos informados:</strong><br>
                                    <em>{{ $reservation->notes }}</em>
                                </div>
                            @endif

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 0;">
                                El equipo técnico de Sound Heritage revisará tu solicitud y se pondrá en contacto para coordinar los detalles de backline y sonido.
                            </p>

                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="background-color: #faf9f6; padding: 24px 36px; border-top: 1px solid #e4e4e7; text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: #181715;">
                                AUDITORIO SOUND HERITAGE
                            </div>
                            <div style="font-size: 10px; color: #71717a; margin-top: 4px;">
                                Calle Colón 1234, Posadas, Misiones | Tel: +54 376 412-3456
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

