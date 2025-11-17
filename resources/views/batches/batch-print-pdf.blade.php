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
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 30px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        .batch-info {
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            font-size: 14px;
            color: #2563eb;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }
        .info-item {
            padding: 10px;
            background-color: #f9fafb;
            border-left: 3px solid #2563eb;
        }
        .info-item label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            display: block;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .info-item .value {
            font-size: 13px;
            color: #111827;
            font-weight: 600;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .quantity-box {
            background-color: #eff6ff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .quantity-box .label {
            font-size: 11px;
            color: #1e40af;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .quantity-box .value {
            font-size: 28px;
            color: #1e3a8a;
            font-weight: bold;
        }
        .barcode-section {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
        }
        .barcode-section .code {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 10px;
            color: #111827;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .warning-box .label {
            font-size: 10px;
            color: #92400e;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .warning-box .value {
            font-size: 12px;
            color: #78350f;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }
        table td {
            padding: 8px;
            font-size: 11px;
            border-bottom: 1px solid #e5e7eb;
        }
        .expiration-alert {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>INFORMACIÓN DE LOTE</h1>
        <div class="subtitle">Sistema de Gestión de Inventario - ImporRepuestos</div>
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
                    <label>Código de Lote</label>
                    <div class="value" style="font-size: 16px; color: #2563eb;">{{ $batch->code }}</div>
                </div>
                <div class="info-item">
                    <label>Origen</label>
                    <div class="value">{{ $batch->origenCode->description ?? $batch->origenCode->code ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Almacén</label>
                    <div class="value">{{ $batch->inventory->warehouse->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Estado</label>
                    <div class="value">
                        <span class="status-badge {{ $batch->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $batch->is_active ? 'ACTIVO' : 'INACTIVO' }}
                        </span>
                    </div>
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
            <div class="info-grid">
                <div class="quantity-box">
                    <div class="label">Cantidad Inicial</div>
                    <div class="value">{{ number_format($batch->initial_quantity, 2) }}</div>
                </div>
                <div class="quantity-box">
                    <div class="label">Cantidad Disponible</div>
                    <div class="value">{{ number_format($batch->available_quantity, 2) }}</div>
                </div>
            </div>

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
</body>
</html>
