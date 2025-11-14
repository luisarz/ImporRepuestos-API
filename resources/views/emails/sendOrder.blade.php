<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #e12828;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .order-info {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #e12828;
            margin: 20px 0;
        }
        .order-info p {
            margin: 5px 0;
        }
        .order-info strong {
            color: #e12828;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #e12828;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #c91f1f;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .divider {
            border-top: 2px solid #e12828;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Orden de Compra</h1>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $customerName }}</strong>,</p>

        <p>Le enviamos adjunta su orden de compra con los siguientes detalles:</p>

        <div class="order-info">
            <p><strong>Número de Orden:</strong> {{ $orderNumber }}</p>
            <p><strong>Fecha:</strong> {{ $orderDate }}</p>
            <p><strong>Total:</strong> ${{ $orderTotal }}</p>
            @if(isset($orderStatus))
                <p><strong>Estado:</strong> {{ $orderStatus }}</p>
            @endif
        </div>

        <div class="divider"></div>

        <p>Encontrará adjunto el documento PDF con el detalle completo de su orden.</p>

        @if(isset($additionalMessage) && $additionalMessage)
            <p style="background-color: #fffbcc; padding: 15px; border-left: 4px solid #f0e68c; margin: 20px 0;">
                <strong>Nota:</strong> {{ $additionalMessage }}
            </p>
        @endif

        <p>Si tiene alguna pregunta o necesita información adicional, no dude en contactarnos.</p>

        <div class="divider"></div>

        <p style="color: #666; font-size: 14px;">
            <strong>Información de contacto:</strong><br>
            {{ $companyName }}<br>
            @if(isset($companyPhone))
                Teléfono: {{ $companyPhone }}<br>
            @endif
            @if(isset($companyEmail))
                Email: {{ $companyEmail }}<br>
            @endif
            @if(isset($companyAddress))
                Dirección: {{ $companyAddress }}
            @endif
        </p>
    </div>

    <div class="footer">
        <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} {{ $companyName }}. Todos los derechos reservados.</p>
    </div>
</body>
</html>
