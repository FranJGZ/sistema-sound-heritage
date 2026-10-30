<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - Sound Heritage</title>
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
                                            Comprobante Oficial
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
                                ¡Muchas gracias por tu compra, {{ $invoice->customer->first_name }}!
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #52525b; margin: 0 0 24px 0;">
                                Hemos procesado tu orden con éxito. A continuación te compartimos el resumen de tu compra. Adjunto a este correo encontrarás tu <strong>factura oficial</strong> en formato PDF, lista para descargar o imprimir.
                                @if(!empty($attendances) && count($attendances) > 0)
                                    También hemos adjuntado las <strong>entradas oficiales con código de acceso</strong> para los eventos incluidos en esta orden.
                                @endif
                            </p>

                            <!-- Ficha de la Orden -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #faf9f6; border: 1px solid #e4e4e7; border-radius: 12px; margin-bottom: 24px; padding: 16px;">
                                <tr>
                                    <td style="padding: 6px 12px; font-size: 12px; color: #71717a;">Factura / Comprobante:</td>
                                    <td style="padding: 6px 12px; font-size: 12px; font-weight: 800; color: #181715; text-align: right;">
                                        {{ $invoice->invoice_type }} {{ str_pad($invoice->point_of_sale, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($invoice->receipt_number, 8, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 12px; font-size: 12px; color: #71717a;">Fecha de Emisión:</td>
                                    <td style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: #181715; text-align: right;">
                                        {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 12px; font-size: 12px; color: #71717a;">Medio de Pago:</td>
                                    <td style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: #181715; text-align: right;">
                                        {{ $invoice->paymentDetails->pluck('paymentMethod.name')->implode(', ') ?: 'Acordado con el comercio' }}
                                    </td>
                                </tr>
                                @if(!empty($deliveryMethod))
                                <tr>
                                    <td style="padding: 6px 12px; font-size: 12px; color: #71717a;">Modalidad de Entrega:</td>
                                    <td style="padding: 6px 12px; font-size: 12px; font-weight: 700; color: #D97724; text-align: right;">
                                        {{ $deliveryMethod }}
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <!-- Detalle de Productos e Ítems -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e4e4e7;">
                                        <th align="left" style="padding: 10px 0; font-size: 11px; text-transform: uppercase; color: #71717a; font-weight: 700;">Ítem</th>
                                        <th align="center" style="padding: 10px 0; font-size: 11px; text-transform: uppercase; color: #71717a; font-weight: 700;">Cant.</th>
                                        <th align="right" style="padding: 10px 0; font-size: 11px; text-transform: uppercase; color: #71717a; font-weight: 700;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->invoiceDetails as $item)
                                        <tr style="border-bottom: 1px solid #f4f4f5;">
                                            <td style="padding: 12px 0; font-size: 13px; font-weight: 600; color: #181715;">
                                                {{ $item->product ? $item->product->name : ($item->description ?? 'Ítem de compra') }}
                                            </td>
                                            <td align="center" style="padding: 12px 0; font-size: 13px; color: #52525b;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td align="right" style="padding: 12px 0; font-size: 13px; font-weight: 700; color: #181715;">
                                                $ {{ number_format($item->subtotal, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="padding-top: 16px; font-size: 14px; font-weight: 800; color: #181715; text-align: right;">
                                            Total Abonado:
                                        </td>
                                        <td style="padding-top: 16px; font-size: 18px; font-weight: 900; color: #D97724; text-align: right;">
                                            $ {{ number_format($invoice->total, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Instrucciones Adicionales según Entrega -->
                            @if($deliveryMethod === 'Retiro en Local Sound Heritage')
                                <div style="background-color: #fff7ed; border-left: 4px solid #D97724; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px;">
                                    <div style="font-size: 12px; font-weight: 800; color: #9a3412; text-transform: uppercase; margin-bottom: 4px;">
                                        Retiro en Tienda Física
                                    </div>
                                    <div style="font-size: 12px; color: #7c2d12; line-height: 1.5;">
                                        Podés pasar a retirar tu pedido por nuestro local en <strong>Calle Colón 1234, Posadas, Misiones</strong> de Lunes a Sábados de 10:00 a 20:00 hs con tu DNI y este comprobante.
                                    </div>
                                </div>
                            @elseif($deliveryMethod === 'Envío a Domicilio')
                                <div style="background-color: #faf9f6; border-left: 4px solid #181715; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px;">
                                    <div style="font-size: 12px; font-weight: 800; color: #181715; text-transform: uppercase; margin-bottom: 4px;">
                                        Envío a Domicilio
                                    </div>
                                    <div style="font-size: 12px; color: #52525b; line-height: 1.5;">
                                        Tu pedido está siendo preparado para despacho. Te contactaremos con el número de seguimiento apenas se encuentre en camino.
                                    </div>
                                </div>
                            @endif

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 0;">
                                Si tenés alguna duda sobre tu compra, podés responder directamente a este correo o escribirnos a soundheritage.admin@gmail.com indicando tu número de factura.
                            </p>

                        </td>
                    </tr>

                    <!-- Pie de Página Institucional -->
                    <tr>
                        <td style="background-color: #faf9f6; padding: 24px 36px; border-top: 1px solid #e4e4e7; text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: #181715; letter-spacing: 0.5px;">
                                SOUND HERITAGE S.R.L.
                            </div>
                            <div style="font-size: 10px; color: #71717a; margin-top: 4px;">
                                Calle Colón 1234, Posadas, Misiones | CUIT: 30-71234567-9
                            </div>
                            <div style="font-size: 10px; color: #a1a1aa; margin-top: 4px;">
                                Este es un correo automático generado tras confirmar tu compra en la tienda oficial.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
