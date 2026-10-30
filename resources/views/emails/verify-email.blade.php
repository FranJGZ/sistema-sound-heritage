<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificá tu correo - Sound Heritage</title>
    <style>
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            color: #334155;
        }
        .wrapper {
            width: 100%;
            background-color: #0f172a;
            padding: 40px 15px;
        }
        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 35px 20px;
            text-align: center;
            border-bottom: 3px solid #f59e0b;
        }
        .header-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-subtitle {
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 6px 0 0 0;
        }
        .content {
            padding: 40px 35px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 18px;
        }
        .text {
            font-size: 15px;
            line-height: 1.65;
            color: #475569;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            background-color: #0f172a;
            color: #ffffff !important;
            padding: 14px 34px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
            border: 1px solid #334155;
            transition: all 0.2s ease;
        }
        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 25px;
        }
        .info-text {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }
        .fallback-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            word-break: break-all;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .fallback-link {
            color: #d97706;
            text-decoration: underline;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 24px 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            font-size: 12px;
            color: #64748b;
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="email-container">
            <!-- Encabezado con identidad de la marca -->
            <div class="header">
                <h1 class="header-title">Sound Heritage</h1>
                <p class="header-subtitle">Instrumentos Musicales &amp; Audio Profesional</p>
            </div>

            <!-- Cuerpo del mensaje -->
            <div class="content">
                <h2 class="greeting">¡Hola, {{ $user->name ?? 'Usuario' }}!</h2>
                <p class="text">
                    Gracias por crear tu cuenta en <strong>Sound Heritage</strong>. Para proteger la seguridad de tu información y permitirte realizar compras, consultas y reservas en nuestra plataforma, por favor confirmá tu dirección de correo electrónico.
                </p>

                <div class="btn-container">
                    <a href="{{ $url }}" class="btn" target="_blank">
                        Verificar mi correo electrónico
                    </a>
                </div>

                <div class="info-box">
                    <p class="info-text">
                        <strong>Seguridad:</strong> Este enlace es único y válido durante los próximos 60 minutos. Si no te registraste en nuestro sitio, podés desestimar este mensaje; tu cuenta permanecerá inactiva.
                    </p>
                </div>

                <div class="fallback-text">
                    Si tenés problemas para hacer clic en el botón superior, copiá y pegá el siguiente enlace en tu navegador web:<br>
                    <a href="{{ $url }}" class="fallback-link">{{ $url }}</a>
                </div>
            </div>

            <!-- Pie de página institucional -->
            <div class="footer">
                <p class="footer-text"><strong>Sound Heritage</strong> &bull; Tienda Oficial</p>
                <p class="footer-text">Este es un mensaje automático de verificación de cuenta, por favor no respondas a este correo.</p>
                <p class="footer-text">&copy; {{ date('Y') }} Sound Heritage. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
