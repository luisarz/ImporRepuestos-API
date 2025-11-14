<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ str_pad($order->order_number, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: Verdana, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
            text-align: center;
        }

        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container img {
            max-width: 80px;
            height: auto;
        }

        .empresa-info {
            text-align: center;
            margin-bottom: 10px;
        }

        .empresa-info h4 {
            margin: 5px 0;
            font-size: 12px;
        }

        .empresa-info p {
            margin: 3px 0;
            line-height: 1.3;
        }

        .documento-info {
            text-align: left;
            margin: 10px 0;
        }

        .documento-info h4 {
            text-align: center;
            margin: 8px 0;
            font-size: 11px;
        }

        .documento-info h5 {
            text-align: center;
            margin: 5px 0;
            font-size: 10px;
        }

        .documento-info p {
            margin: 4px 0;
            word-wrap: break-word;
        }

        .cliente-info {
            text-align: left;
            margin: 10px 0;
        }

        .cliente-info p {
            margin: 3px 0;
            line-height: 1.4;
        }

        .tabla-productos {
            width: 100%;
            margin: 10px 0;
            text-align: left;
        }

        .tabla-productos .item-row {
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }

        .tabla-productos .item-descripcion {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .tabla-productos .item-detalle {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            padding-left: 10px;
        }

        .footer {
            text-align: left;
            margin-top: 10px;
            font-size: 9px;
        }

        .footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer td {
            padding: 2px 0;
        }

        .footer .total-final {
            font-weight: bold;
            font-size: 11px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            margin: 5px 0;
        }

        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<!-- Logo Empresa -->
<div class="logo-container">
    @if(!empty($logo))
        <img src="{{ $logo }}" alt="Logo de la empresa">
    @endif
</div>

<!-- Información Empresa -->
<div class="empresa-info">
    <h4>{{ $empresa->name ?? 'IMPORREPUESTOS' }}</h4>
    <p>
        @if($empresa->nit ?? null)
            NIT: @php
                $nit = $empresa->nit;
                echo strlen($nit) === 14
                    ? substr($nit, 0, 4) . '-' . substr($nit, 4, 6) . '-' . substr($nit, 10, 3) . '-' . substr($nit, 13, 1)
                    : $nit;
            @endphp<br>
        @endif
        @if($empresa->nrc ?? null)
            NRC: {{ $empresa->nrc }}<br>
        @endif
        {{ $empresa->business_activity ?? 'Venta de repuestos automotrices' }}<br>
        @if($empresa->address ?? null)
            {{ $empresa->address }}<br>
        @endif
        @if($empresa->phone ?? null)
            Teléfono: @php
                $telefono = $empresa->phone;
                $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

                // Si el teléfono tiene 11 dígitos y empieza con 503
                if (strlen($telefonoLimpio) == 11 && substr($telefonoLimpio, 0, 3) == '503') {
                    $telefono = '(' . substr($telefonoLimpio, 0, 3) . ') ' . substr($telefonoLimpio, 3, 4) . '-' . substr($telefonoLimpio, 7, 4);
                }
                // Si el teléfono tiene 8 dígitos
                elseif (strlen($telefonoLimpio) == 8) {
                    $telefono = '(' . substr($telefonoLimpio, 0, 4) . ') ' . substr($telefonoLimpio, 4, 4);
                }
                echo $telefono;
            @endphp
        @endif
    </p>
</div>

@if($order->seller ?? null)
<p style="text-align: left; margin: 8px 0;">
    <strong>Vendedor:</strong> {{ $order->seller->name ?? '' }} {{ $order->seller->last_name ?? '' }}
</p>
@endif

<div class="separator"></div>

<!-- Información del Documento -->
<div class="documento-info">
    <h4>DOCUMENTO INTERNO</h4>
    <h5>ORDEN DE COMPRA</h5>

    <p>
        <strong>Número de orden:</strong><br>
        #{{ str_pad($order->order_number, 5, '0', STR_PAD_LEFT) }}
    </p>

    <p class="text-center">
        <strong>Estado:</strong><br>
        @if($order->sale_status == 1)
            <span class="status-badge status-in-progress">EN PROGRESO</span>
        @elseif($order->sale_status == 2)
            <span class="status-badge status-completed">COMPLETADA</span>
        @elseif($order->sale_status == 3)
            <span class="status-badge status-cancelled">ANULADA</span>
        @endif
    </p>

    <p>
        <strong>Fecha y hora de emisión:</strong><br>
        {{ \Carbon\Carbon::parse($order->sale_date)->format('d/m/Y') }}
        {{ \Carbon\Carbon::parse($order->sale_date)->format('H:i:s') }}
    </p>

    @if($order->warehouse ?? null)
    <p>
        <strong>Sucursal:</strong><br>
        {{ $order->warehouse->name }}
    </p>
    @endif
</div>

<div class="separator"></div>

<!-- Información del Cliente -->
<div class="cliente-info">
    <p><strong>CLIENTE</strong></p>
    <p>
        @if($order->customer)
            <strong>Nombre:</strong> {{ $order->customer->name }} {{ $order->customer->last_name ?? '' }}<br>
            @if($order->customer->document_number ?? null)
                <strong>Documento:</strong> {{ $order->customer->document_number }}<br>
            @endif
            @if($order->customer->phone ?? null)
                <strong>Teléfono:</strong> {{ $order->customer->phone }}<br>
            @endif
            @if($order->customer->email ?? null)
                <strong>Correo:</strong> {{ $order->customer->email }}<br>
            @endif
            @if($order->customer->address ?? null)
                <strong>Dirección:</strong> {{ $order->customer->address }}
            @endif
        @else
            Cliente General
        @endif
    </p>
</div>

<div class="separator"></div>

<!-- Productos -->
<div class="tabla-productos">
    <p><strong>DETALLE DE PRODUCTOS</strong></p>

    @php
        $subtotal = 0;
        $totalDescuentos = 0;
    @endphp

    @foreach($order->items as $item)
        @php
            $product = $item->inventory->product ?? null;
            $itemSubtotal = $item->quantity * $item->price;
            $itemDescuento = $item->discount ?? 0;
            $itemTotal = $itemSubtotal - $itemDescuento;
            $subtotal += $itemSubtotal;
            $totalDescuentos += $itemDescuento;
        @endphp
        <div class="item-row">
            <div class="item-descripcion">
                {{ number_format($item->quantity, 0) }} x {{ $product->description ?? 'Producto sin descripción' }}
            </div>
            <div class="item-detalle">
                <span>P.Unit: ${{ number_format($item->price, 2) }}</span>
                @if($itemDescuento > 0)
                    <span>Desc: ${{ number_format($itemDescuento, 2) }}</span>
                @endif
                <span><strong>${{ number_format($itemTotal, 2) }}</strong></span>
            </div>
        </div>
    @endforeach
</div>

<div class="separator"></div>

<!-- Resumen/Footer -->
<div class="footer">
    <p><strong>RESUMEN</strong></p>
    <table>
        @if($order->operation_condition_id ?? null)
        <tr>
            <td><strong>Condición de Operación:</strong></td>
            <td class="text-right">
                @if($order->operation_condition_id == 1)
                    Contado
                @elseif($order->operation_condition_id == 2)
                    Crédito a {{ $order->credit_days ?? '0' }} días
                @else
                    {{ $order->operationCondition->description ?? 'N/A' }}
                @endif
            </td>
        </tr>
        @endif
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">${{ number_format($subtotal, 2) }}</td>
        </tr>
        @if($totalDescuentos > 0)
        <tr>
            <td>Descuentos:</td>
            <td class="text-right">-${{ number_format($totalDescuentos, 2) }}</td>
        </tr>
        @endif
        @php
            $total = $order->sale_total ?? ($subtotal - $totalDescuentos);
            $neto = $total / 1.13;
            $iva = $neto * 0.13;
        @endphp
        <tr>
            <td>Neto:</td>
            <td class="text-right">${{ number_format($neto, 2) }}</td>
        </tr>
        <tr>
            <td>IVA (13%):</td>
            <td class="text-right">${{ number_format($iva, 2) }}</td>
        </tr>
    </table>

    <div class="total-final text-center">
        TOTAL A PAGAR: ${{ number_format($total, 2) }}
    </div>
</div>

@if($order->notes ?? null)
<div class="separator"></div>

<div style="text-align: left; margin: 10px 0; font-size: 9px;">
    <p><strong>Observaciones:</strong></p>
    <p style="margin: 5px 0; line-height: 1.4;">{{ $order->notes }}</p>
</div>
@endif

<div class="separator"></div>

<p style="font-size: 8px; text-align: center; margin-top: 10px;">
    Gracias por su compra<br>
    Documento interno generado el {{ now()->format('d/m/Y H:i:s') }}<br>
    {{ $empresa->name ?? 'IMPORREPUESTOS' }}
</p>
</body>
</html>
