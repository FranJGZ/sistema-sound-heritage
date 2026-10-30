<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Sound Heritage</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F3EFE6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #181715;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F3EFE6; padding: 30px 10px;">
        <tr>
            <td align="center">
                <!-- Tarjeta Principal -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    
                    <!-- Encabezado Institucional Carbón -->
                    <tr>
                        <td style="background-color: #181715; padding: 32px 36px; text-align: left; border-bottom: 3px solid #D97724;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 900; letter-spacing: 2px; color: #ffffff; text-transform: uppercase;">
                                            SOUND HERITAGE
                                        </div>
                                        <div style="font-size: 11px; color: #D97724; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 4px;">
                                            Espacio Cultural &amp; Tienda de Instrumentos
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: rgba(217, 119, 36, 0.15); border: 1px solid #D97724; border-radius: 8px; color: #D97724; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                            Cuenta Activada
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
                                ¡Hola, {{ $user->name }}! Te damos la bienvenida a Sound Heritage.
                            </h1>

                            <p style="font-size: 14px; line-height: 1.6; color: #52525b; margin: 0 0 24px 0;">
                                @if($origin === 'presencial')
                                    Tu cuenta de cliente fue dada de alta en nuestra sucursal. Ahora podés ingresar a nuestra tienda web para consultar tus comprobantes fiscales, gestionar tus reservas de sala, descargar entradas a eventos y realizar compras online de manera rápida y segura.
                                @else
                                    Tu cuenta de usuario ha sido registrada con éxito en nuestra tienda web. Ya podés disfrutar de todos los beneficios de nuestra comunidad cultural, catálogo de instrumentos y reservas.
                                @endif
                            </p>

                            <!-- Ficha de Credenciales de Acceso -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #faf9f6; border: 1px solid #e4e4e7; border-radius: 12px; margin-bottom: 24px; padding: 16px;">
                                <tr>
                                    <td colspan="2" style="padding: 6px 12px 12px 12px; font-size: 13px; font-weight: 800; color: #181715; border-bottom: 1px solid #e4e4e7; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Datos de tu Cuenta
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 12px; font-size: 13px; color: #71717a;">Correo de acceso:</td>
                                    <td style="padding: 10px 12px; font-size: 13px; font-weight: 800; color: #181715; text-align: right;">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                                @if(!empty($temporaryPassword))
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px; color: #71717a;">Contraseña inicial:</td>
                                        <td style="padding: 10px 12px; font-size: 13px; font-weight: 900; color: #D97724; font-family: monospace; text-align: right;">
                                            {{ $temporaryPassword }}
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            @if(!empty($temporaryPassword))
                                <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px;">
                                    <p style="margin: 0; font-size: 12px; color: #92400e; line-height: 1.5;">
                                        💡 <strong>Consejo de seguridad:</strong> Tu contraseña inicial es tu número de documento. Te recomendamos cambiarla por una personalizada desde la sección <em>Mi Cuenta &gt; Seguridad</em> al iniciar sesión por primera vez.
                                    </p>
                                </div>
                            @endif

                            <!-- Botón de Acción Principal -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $loginUrl }}" style="display: inline-block; background-color: #D97724; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; padding: 14px 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(217, 119, 36, 0.35);">
                                            Ingresar a la Tienda Web
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #a1a1aa; line-height: 1.5; margin: 0; text-align: center;">
                                Si no reconocés este registro, por favor comunicate con nuestro equipo respondiendo a este mensaje.
                            </p>

                        </td>
                    </tr>

                    <!-- Pie Institucional -->
                    <tr>
                        <td style="background-color: #faf9f6; padding: 24px 36px; border-top: 1px solid #e4e4e7; text-align: center;">
                            <div style="font-size: 12px; font-weight: 700; color: #181715;">
                                Sound Heritage · Espacio Cultural &amp; Tienda
                            </div>
                            <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                                Calle Colón 1234, Posadas, Misiones, Argentina · WhatsApp: +54 (376) 412-3456
                            </div>
                            <div style="font-size: 10px; color: #a1a1aa; margin-top: 8px;">
                                &copy; {{ date('Y') }} Sound Heritage. Todos los derechos reservados.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

