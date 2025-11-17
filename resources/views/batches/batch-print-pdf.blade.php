<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lote {{ $batch->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            padding: 0;
            color: #1f2937;
            background: #f9fafb;
        }
        .page {
            background: white;
            padding: 20px 30px;
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            background: #e12828;
            color: white;
            padding: 15px 20px;
            margin: -20px -30px 15px -30px;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .header-content {
            position: relative;
            z-index: 1;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .header .company-name {
            font-size: 9px;
            opacity: 0.9;
            font-weight: 300;
            margin-bottom: 10px;
        }
        .header .batch-code {
            font-size: 14px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 4px;
        }
        .batch-info {
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 15px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }
        .info-section h2 {
            font-size: 11px;
            color: #e12828;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e12828;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-grid {
            width: 100%;
            margin-top: 10px;
        }
        .info-grid::after {
            content: "";
            display: table;
            clear: both;
        }
        .info-item {
            width: 48%;
            float: left;
            margin-right: 4%;
            padding: 8px;
            background-color: white;
            border-left: 3px solid #e12828;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .info-item:nth-child(2n) {
            margin-right: 0;
        }
        .info-item label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            display: block;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .info-item .value {
            font-size: 12px;
            color: #111827;
            font-weight: 600;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .quantity-box {
            width: 48%;
            float: left;
            margin-right: 4%;
            background: #fff5f5;
            border: 2px solid #e12828;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(225, 40, 40, 0.1);
        }
        .quantity-box:nth-child(2n) {
            margin-right: 0;
        }
        .quantity-box .label {
            font-size: 8px;
            color: #e12828;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .quantity-box .value {
            font-size: 20px;
            color: #c22020;
            font-weight: 700;
        }
        .barcode-section {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border: 2px dashed #e12828;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .barcode-section .code {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 8px;
            color: #e12828;
            background: white;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .footer {
            margin-top: 20px;
            padding: 12px;
            background: #e12828;
            color: white;
            text-align: center;
            border-radius: 6px;
            font-size: 8px;
        }
        .footer p {
            margin: 2px 0;
            opacity: 0.95;
        }
        .footer strong {
            font-weight: 700;
            font-size: 9px;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 5px solid #f59e0b;
            padding: 10px;
            margin-top: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.1);
        }
        .warning-box .label {
            font-size: 9px;
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .warning-box .value {
            font-size: 10px;
            color: #78350f;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #e5e7eb;
        }
        table thead {
            background: #e12828;
            color: white;
        }
        table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e12828;
        }
        table td {
            padding: 8px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
            background: white;
        }
        table tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .expiration-alert {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="company-name">ImporRepuestos - Sistema de Gestión de Inventario</div>
            <h1>INFORMACIÓN DE LOTE</h1>
            <div class="batch-code">{{ $batch->code }}</div>
            <span class="status-badge status-{{ $batch->is_active ? 'active' : 'inactive' }}">
                {{ $batch->is_active ? 'ACTIVO' : 'INACTIVO' }}
            </span>
        </div>
    </div>

    <!-- Batch Information -->
    <div class="batch-info">

        <!-- Product Section -->
        <div class="info-section">
            <h2>INFORMACIÓN DEL PRODUCTO</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Código Producto</label>
                    <div class="value">{{ $batch->inventory->product->code ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Código Original</label>
                    <div class="value">{{ $batch->inventory->product->original_code ?? 'N/A' }}</div>
                </div>
                <div class="info-item full-width">
                    <label>Descripción</label>
                    <div class="value">{{ $batch->inventory->product->description ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Batch Details Section -->
        <div class="info-section">
            <h2>DETALLES DEL LOTE</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Origen</label>
                    <div class="value">{{ $batch->origenCode->description ?? $batch->origenCode->code ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Almacén</label>
                    <div class="value">{{ $batch->inventory->warehouse->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Dates Section -->
        <div class="info-section">
            <h2>FECHAS</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Fecha de Ingreso</label>
                    <div class="value">{{ $batch->incoming_date ? \Carbon\Carbon::parse($batch->incoming_date)->format('d/m/Y') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Fecha de Vencimiento</label>
                    <div class="value {{ $isExpired ? 'expiration-alert' : '' }}">
                        {{ $batch->expiration_date ? \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') : 'N/A' }}
                        @if($isExpired)
                            <span style="font-size: 10px;"> (VENCIDO)</span>
                        @elseif($daysToExpire <= 30 && $daysToExpire > 0)
                            <span style="font-size: 10px; color: #f59e0b;"> ({{ $daysToExpire }} días para vencer)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quantities Section -->
        <div class="info-section">
            <h2>CANTIDADES</h2>
            <div class="quantity-box">
                <div class="label">Cantidad Inicial</div>
                <div class="value">{{ number_format($batch->initial_quantity, 2) }}</div>
            </div>
            <div class="quantity-box">
                <div class="label">Cantidad Disponible</div>
                <div class="value">{{ number_format($batch->available_quantity, 2) }}</div>
            </div>
            <div style="clear: both;"></div>

            <!-- Usage Stats -->
            <table style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Cantidad Inicial</th>
                        <th>Cantidad Disponible</th>
                        <th>Cantidad Utilizada</th>
                        <th>% Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($batch->initial_quantity, 2) }}</td>
                        <td>{{ number_format($batch->available_quantity, 2) }}</td>
                        <td>{{ number_format($batch->initial_quantity - $batch->available_quantity, 2) }}</td>
                        <td>{{ $batch->initial_quantity > 0 ? number_format(($batch->available_quantity / $batch->initial_quantity) * 100, 2) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Purchase Info (if available) -->
        @if($batch->purchaseItem)
        <div class="info-section">
            <h2>INFORMACIÓN DE COMPRA</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>No. Documento</label>
                    <div class="value">{{ $batch->purchaseItem->purchase->document_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Fecha de Compra</label>
                    <div class="value">
                        {{ $batch->purchaseItem->purchase->purchase_date ?
                           \Carbon\Carbon::parse($batch->purchaseItem->purchase->purchase_date)->format('d/m/Y') :
                           'N/A' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Precio Unitario</label>
                    <div class="value">${{ number_format($batch->purchaseItem->unit_price ?? 0, 2) }}</div>
                </div>
                <div class="info-item">
                    <label>Cantidad Comprada</label>
                    <div class="value">{{ number_format($batch->purchaseItem->quantity ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Observations -->
        @if($batch->observations)
        <div class="info-section">
            <h2>OBSERVACIONES</h2>
            <div class="info-item full-width" style="margin-top: 10px;">
                <div class="value">{{ $batch->observations }}</div>
            </div>
        </div>
        @endif

        <!-- Expiration Warning -->
        @if($isExpired || ($daysToExpire <= 30 && $daysToExpire > 0))
        <div class="warning-box">
            <div class="label">
                @if($isExpired)
                    ⚠️ ADVERTENCIA: LOTE VENCIDO
                @else
                    ⚠️ ADVERTENCIA: PRÓXIMO A VENCER
                @endif
            </div>
            <div class="value">
                @if($isExpired)
                    Este lote ha expirado. Verifique la calidad del producto antes de su uso.
                @else
                    Este lote vencerá en {{ $daysToExpire }} días ({{ \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}).
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Barcode Section -->
    <div class="barcode-section">
        <div style="font-size: 12px; color: #6b7280; margin-bottom: 10px;">CÓDIGO DE LOTE</div>
        <div class="code">{{ $batch->code }}</div>
        <div style="font-size: 10px; color: #9ca3af; margin-top: 10px;">
            Escanee este código para identificación rápida
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Documento generado automáticamente por el sistema</strong></p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>ImporRepuestos - Sistema de Gestión de Inventario</p>
    </div>
    </div>
</body>
</html>
