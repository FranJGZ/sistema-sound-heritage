<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus Entradas - Sound Heritage</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F3EFE6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #181715;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3EFE6; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    
                    <!-- Encabezado Carbón con Acento Ámbar -->
                    <tr>
                        <td style="background-color: #181715; padding: 32px 36px; text-align: left; border-bottom: 3px solid #D97724;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 900; letter-spacing: 2px; color: #ffffff; text-transform: uppercase;">
                                            SOUND HERITAGE
                                        </div>
                                        <div style="font-size: 11px; color: #D97724; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 4px;">
                                            Cartelera Cultural &amp; Auditorio
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: rgba(217, 119, 36, 0.15); border: 1px solid #D97724; border-radius: 8px; color: #D97724; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                            Entradas Oficiales
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding: 36px;">
                            
                            <h1 style="font-size: 20px; font-weight: 800; margin: 0 0 12px 0; color: #181715;">
                                ¡Tus entradas están listas, {{ $customer->first_name }}!
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #52525b; margin: 0 0 24px 0;">
                                Confirmamos la acreditación de tus lugares para la cartelera cultural de Sound Heritage. Adjunto a este correo encontrarás tus <strong>entradas oficiales en PDF</strong> con código de acceso para presentar al ingresar al auditorio.
                            </p>

                            <!-- Resumen de Eventos y Asistencia -->
                            <div style="background-color: #faf9f6; border: 1px solid #e4e4e7; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 11px; font-weight: 800; color: #D97724; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                                    Detalle del Evento y Ubicación
                                </div>
                                @foreach($attendances as $att)
                                    <div style="padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px dashed #e4e4e7;">
                                        <div style="font-size: 15px; font-weight: 800; color: #181715;">
                                            {{ $att->event->name }}
                                        </div>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 6px; font-size: 12px; color: #52525b;">
                                            <tr>
                                                <td style="padding: 2px 0;"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($att->event->date)->format('d/m/Y') }}</td>
                                                <td style="padding: 2px 0; text-align: right;"><strong>Hora:</strong> {{ $att->event->time ?? '20:30 hs' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0;"><strong>Salón:</strong> {{ $att->event->venue->name ?? 'Auditorio Principal' }}</td>
                                                <td style="padding: 2px 0; text-align: right;"><strong>Pase:</strong> #{{ str_pad($att->id, 6, '0', STR_PAD_LEFT) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                @endforeach
                                <div style="font-size: 12px; color: #71717a;">
                                    <strong>Lugar de Ingreso:</strong> Calle Colón 1234, Posadas, Misiones.
                                </div>
                            </div>

                            <!-- Pautas de Ingreso -->
                            <div style="background-color: #faf9f6; border-left: 4px solid #D97724; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 800; color: #181715; text-transform: uppercase; margin-bottom: 4px;">
                                    Pautas Importantes de Ingreso
                                </div>
                                <div style="font-size: 12px; color: #52525b; line-height: 1.5;">
                                    - Podés presentar la entrada descargada en tu celular o impresa.<br>
                                    - La apertura de puertas se realiza 30 minutos antes del show.<br>
                                    - Contamos con guardarropa y servicio de cafetería de especialidad en el foyer.
                                </div>
                            </div>

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 0;">
                                ¡Esperamos que disfrutes de una noche única de música en Sound Heritage!
                            </p>

                        </td>
                    </tr>

                    <!-- Pie de Página -->
                    <tr>
                        <td style="background-color: #faf9f6; padding: 24px 36px; border-top: 1px solid #e4e4e7; text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: #181715;">
                                AUDITORIO &amp; SALA ACÚSTICA SOUND HERITAGE
                            </div>
                            <div style="font-size: 10px; color: #71717a; margin-top: 4px;">
                                Calle Colón 1234, Posadas, Misiones | Reservas: soundheritage.admin@gmail.com
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

