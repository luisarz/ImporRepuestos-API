<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ str_pad($order->order_number, 5, '0', STR_PAD_LEFT) }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            line-height: 1.3;
            color: #222;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }

        .header strong {
            font-weight: bold;
            color: #57595B;
            font-size: 9.5px;
        }

        .footer {
            left: 0;
            width: 100%;
            border: 2px solid #e12828;
            border-radius: 10px;
            text-align: right;
            font-size: 9px;
            line-height: 1.3;
        }

        .footer strong {
            font-weight: bold;
            color: #57595B;
        }

        .content {
            flex: 1;
            padding-bottom: 100px;
        }

        .content strong,
        .content b {
            font-weight: bold;
            color: #57595B;
            font-size: 9.5px;
        }

        /* TABLA DE PRODUCTOS */
        .tabla-productos {
            border-collapse: collapse;
            width: 100%;
        }

        .tabla-productos th {
            background-color: #e12828 !important;
            color: #ffffff !important;
            font-weight: bold;
            padding: 8px 5px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #e12828;
            line-height: 1.2;
        }

        .tabla-productos td {
            padding: 6px 5px;
            border: 1px solid #d0d0d0;
            font-size: 9px;
            line-height: 1.3;
        }

        .tabla-productos tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .tabla-productos tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Alineación */
        .tabla-productos td:nth-child(1),
        .tabla-productos td:nth-child(2) {
            text-align: center;
            font-weight: normal;
        }

        .tabla-productos td:nth-child(4),
        .tabla-productos td:nth-child(5),
        .tabla-productos td:nth-child(6) {
            text-align: right;
            font-weight: normal;
        }

        /* Cliente info */
        .cliente-info {
            line-height: 1.4;
        }

        .cliente-info tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .cliente-info td {
            padding: 5px 6px;
            font-size: 9.5px;
        }

        .cliente-info b {
            color: #57595B;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
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
<!-- Header Empresa -->
<div class="header" style="border: 0px solid #ccc; border-radius: 10px; padding: 0px;">

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            {{-- IZQUIERDA: LOGO Y EMPRESA --}}
            <td style="width: 40%; vertical-align: top; border: none; border-left: 2px solid #e12828; padding-left: 8px; padding-right: 10px;">
                <table style="width: 100%; font-size: 10px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; width: 90px; vertical-align: middle;">
                            @if(isset($logo))
                                <img src="{{ $logo }}" alt="Logo Empresa" style="max-height: 75px; max-width: 85px;">
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 4px 0 4px 10px;">
                            <div style="font-weight: bold; font-size: 12px; padding: 0 0 3px 0; color: #e12828; line-height: 1.2;">
                                {{ $empresa->name ?? 'IMPORREPUESTOS' }}
                            </div>
                            <div style="padding: 0; font-size: 10px; color: #333; line-height: 1.3;">
                                {{ $empresa->business_activity ?? 'Venta de repuestos automotrices' }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 6px 0 0 0;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="background-color: #f5f5f5;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; width: 35%; line-height: 1.3; vertical-align: top;">
                                        NIT:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        @php
                                            $nit = $empresa->nit ?? '';
                                            echo strlen($nit) === 14
                                                ? substr($nit, 0, 4) . '-' . substr($nit, 4, 6) . '-' . substr($nit, 10, 3) . '-' . substr($nit, 13, 1)
                                                : $nit;
                                        @endphp
                                    </td>
                                </tr>
                                <tr style="background-color: #ffffff;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; line-height: 1.3; vertical-align: top;">
                                        NRC:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        {{ $empresa->nrc ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="background-color: #f5f5f5;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; line-height: 1.3; vertical-align: top;">
                                        DIRECCIÓN:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        {{ $empresa->address ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="background-color: #ffffff;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; line-height: 1.3; vertical-align: top;">
                                        TELÉFONO:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        @php
                                            $telefono = $empresa->phone ?? '';
                                            $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

                                            // Si el teléfono tiene 11 dígitos y empieza con 503
                                            if (strlen($telefonoLimpio) == 11 && substr($telefonoLimpio, 0, 3) == '503') {
                                                $telefono = '(' . substr($telefonoLimpio, 0, 3) . ') ' . substr($telefonoLimpio, 3, 4) . '-' . substr($telefonoLimpio, 7, 4);
                                            }
                                            // Si el teléfono tiene 8 dígitos
                                            elseif (strlen($telefonoLimpio) == 8) {
                                                $telefono = '(' . substr($telefonoLimpio, 0, 4) . ') ' . substr($telefonoLimpio, 4, 4);
                                            }
                                        @endphp
                                        {{ $telefono }}
                                    </td>
                                </tr>
                                <tr style="background-color: #f5f5f5;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; line-height: 1.3; vertical-align: top;">
                                        CORREO:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        {{ $empresa->email ?? 'N/A' }}
                                    </td>
                                </tr>
                                @if($empresa->web ?? null)
                                <tr style="background-color: #ffffff;">
                                    <td style="text-align: left; padding: 5px 6px; font-weight: bold; color: #57595B; font-size: 10px; line-height: 1.3; vertical-align: top;">
                                        SITIO WEB:
                                    </td>
                                    <td style="text-align: left; padding: 5px 6px; color: #333; font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">
                                        {{ $empresa->web }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- DERECHA: ORDEN INFO --}}
            <td style="width: 60%; vertical-align: top; border: none;">
                <table style="width: 100%; border-collapse: collapse; font-size: 10px; border: none;">
                    <thead>
                    <th style="width: 110px !important; border: none;"></th>
                    <th style="border: none;"></th>
                    </thead>
                    <tr style="background-color: #e12828; color: white;">
                        <td colspan="2" style="text-align: center; font-size: 10px; font-weight: bold; padding: 4px; line-height: 1.1; border: none;">
                            DOCUMENTO INTERNO
                        </td>
                    </tr>
                    <tr style="background-color: #e12828; color: white;">
                        <td colspan="2" style="text-align: center; font-size: 13px; font-weight: bold; padding: 5px; line-height: 1.1; border: none;">
                            ORDEN DE COMPRA
                        </td>
                    </tr>
                    <tr style="background-color: #f5f5f5;">
                        <td style="font-weight: bold; color: #57595B; padding: 5px 6px; font-size: 10px; line-height: 1.3; vertical-align: top; border: none;">
                            Número de orden:
                        </td>
                        <td style="color: #333; padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            #{{ str_pad($order->order_number, 5, '0', STR_PAD_LEFT) }}
                        </td>
                    </tr>
                    <tr style="background-color: #ffffff;">
                        <td style="font-weight: bold; color: #57595B; padding: 5px 6px; font-size: 10px; line-height: 1.3; vertical-align: top; border: none;">
                            Estado:
                        </td>
                        <td style="padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            @if($order->sale_status == 1)
                                <span class="status-badge status-in-progress">EN PROGRESO</span>
                            @elseif($order->sale_status == 2)
                                <span class="status-badge status-completed">COMPLETADA</span>
                            @elseif($order->sale_status == 3)
                                <span class="status-badge status-cancelled">ANULADA</span>
                            @endif
                        </td>
                    </tr>
                    <tr style="background-color: #f5f5f5;">
                        <td style="font-weight: bold; color: #57595B; padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            Fecha emisión:
                        </td>
                        <td style="color: #333; padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            {{ \Carbon\Carbon::parse($order->sale_date)->format('d-m-Y') }}
                        </td>
                    </tr>
                    <tr style="background-color: #ffffff;">
                        <td style="font-weight: bold; color: #57595B; padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            Hora emisión:
                        </td>
                        <td style="color: #333; padding: 5px 6px; font-size: 10px; line-height: 1.3; border: none;">
                            {{ \Carbon\Carbon::parse($order->sale_date)->format('H:i:s') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 8px 4px; border: none;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 9px; border: none;">
                                <thead>
                                <tr>
                                    <td colspan="2" style="text-align: center; font-weight: bold; padding: 5px; background-color: #57595B; color: white; font-size: 9px; line-height: 1.2;">
                                        DETALLES ADICIONALES
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr style="background-color: #f5f5f5;">
                                    <td style="text-align: left; padding: 4px 6px; font-weight: bold; color: #57595B; font-size: 8px; width: 45%;">
                                        SUCURSAL:
                                    </td>
                                    <td style="text-align: left; padding: 4px 6px; color: #333; font-size: 8.5px;">
                                        {{ $order->warehouse->name ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr style="background-color: #ffffff;">
                                    <td style="text-align: left; padding: 4px 6px; font-weight: bold; color: #57595B; font-size: 8px;">
                                        VENDEDOR:
                                    </td>
                                    <td style="text-align: left; padding: 4px 6px; color: #333; font-size: 8.5px;">
                                        {{ $order->seller->name ?? 'N/A' }} {{ $order->seller->last_name ?? '' }}
                                    </td>
                                </tr>
                                @if($order->operation_condition_id)
                                <tr style="background-color: #f5f5f5;">
                                    <td style="text-align: left; padding: 4px 6px; font-weight: bold; color: #57595B; font-size: 8px;">
                                        CONDICIÓN:
                                    </td>
                                    <td style="text-align: left; padding: 4px 6px; color: #333; font-size: 8.5px;">
                                        {{ $order->operationCondition->description ?? 'N/A' }}
                                        @if($order->operation_condition_id == 2 && $order->credit_days)
                                            ({{ $order->credit_days }} días)
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<!-- Contenido principal -->
<div class="content">
    <div style="border-bottom: 1px solid black; font-weight: bold; font-size: 14px; color: #333; padding-bottom: 4px;">
        Información del cliente
    </div>

    <!-- Info Cliente -->
    <div class="cliente-info">
        <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
            <tr>
                <td style="padding: 4px 2px 4px 0; width: 15%;"><b>NOMBRE:</b></td>
                <td style="padding: 4px 6px; width: 35%;">
                    @if($order->customer)
                        {{ $order->customer->name }} {{ $order->customer->last_name ?? '' }}
                    @else
                        Cliente General
                    @endif
                </td>
                <td style="padding: 4px 2px 4px 0; width: 15%;"><b>DOCUMENTO:</b></td>
                <td style="padding: 4px 6px; width: 35%;">{{ $order->customer->document_number ?? 'N/A' }}</td>
            </tr>
            @if($order->customer)
            <tr>
                <td style="padding: 4px 2px 4px 0;"><b>TELÉFONO:</b></td>
                <td style="padding: 4px 6px;">{{ $order->customer->phone ?? 'N/A' }}</td>
                <td style="padding: 4px 2px 4px 0;"><b>CORREO:</b></td>
                <td style="padding: 4px 6px; word-break: break-all;">{{ $order->customer->email ?? 'N/A' }}</td>
            </tr>
            @if($order->customer->address ?? null)
            <tr>
                <td style="padding: 4px 2px 4px 0; vertical-align: top;"><b>DIRECCIÓN:</b></td>
                <td colspan="3" style="padding: 4px 6px;">{{ $order->customer->address }}</td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    <!-- Tabla Productos -->
    <table class="tabla-productos" width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
        <tr>
            <th>Cant</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Precio Unitario</th>
            <th>Descuento</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @php
            $subtotal = 0;
            $totalDescuentos = 0;
        @endphp
        @foreach($order->items as $item)
            @php
                $itemSubtotal = $item->quantity * $item->price;
                $itemDescuento = $item->discount ?? 0;
                $itemTotal = $itemSubtotal - $itemDescuento;
                $subtotal += $itemSubtotal;
                $totalDescuentos += $itemDescuento;
                $product = $item->inventory->product ?? null;
            @endphp
            <tr>
                <td>{{ number_format($item->quantity, 0) }}</td>
                <td>{{ $product->internal_code ?? 'N/A' }}</td>
                <td>{{ $product->description ?? 'Producto sin descripción' }}</td>
                <td>${{ number_format($item->price, 2) }}</td>
                <td>${{ number_format($itemDescuento, 2) }}</td>
                <td>${{ number_format($itemTotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="footer">
    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 8px;">
                <table style="width: 100%;">
                    @php
                        $total = $order->sale_total ?? ($subtotal - $totalDescuentos);
                        $neto = $total / 1.13;
                        $iva = $neto * 0.13;
                    @endphp
                    <tr>
                        <td colspan="2" style="background-color: #57595B; color: white; text-align: center; padding: 4px;">
                            INFORMACIÓN ADICIONAL
                        </td>
                    </tr>
                    @if($order->operation_condition_id)
                    <tr>
                        <td style="padding: 4px 0; width: 50%;">Condición Operación:</td>
                        <td style="padding: 4px 0;">
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
                    @if($order->notes)
                    <tr>
                        <td colspan="2" style="padding: 4px 0; vertical-align: top;">
                            <b>Observaciones:</b><br>
                            {{ $order->notes }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width: 40%; vertical-align: top; padding: 8px;">
                <table style="width: 100%;">
                    <tr>
                        <td>Subtotal:</td>
                        <td>${{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($totalDescuentos > 0)
                    <tr>
                        <td>Descuentos:</td>
                        <td>-${{ number_format($totalDescuentos, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Neto:</td>
                        <td>${{ number_format($neto, 2) }}</td>
                    </tr>
                    <tr>
                        <td>IVA (13%):</td>
                        <td>${{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr style="background-color: #57595B; color: white;">
                        <td><b>TOTAL:</b></td>
                        <td>${{ number_format($total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div>
    <p style="text-align: center; font-size: 7.5px; margin-top: 10px; color: #666; font-style: italic;">
        Este documento es una orden interna, no representa un comprobante fiscal válido para efectos tributarios.
        Generado el {{ now()->format('d/m/Y H:i:s') }} - {{ $empresa->name ?? 'IMPORREPUESTOS' }}
    </p>
</div>
</body>
</html>
