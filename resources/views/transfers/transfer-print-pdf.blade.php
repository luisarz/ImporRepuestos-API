<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traslado {{ $transfer->transfer_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 22px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .header .transfer-number {
            font-size: 16px;
            color: #666;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }
        .status-PENDING {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-IN_TRANSIT {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-RECEIVED {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-CANCELLED {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            font-size: 13px;
            color: #2563eb;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }
        .info-item {
            padding: 8px;
            background-color: #f9fafb;
            border-left: 3px solid #2563eb;
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
        .warehouse-box {
            background-color: #eff6ff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .warehouse-box .label {
            font-size: 10px;
            color: #1e40af;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .warehouse-box .value {
            font-size: 14px;
            color: #1e3a8a;
            font-weight: bold;
        }
        .arrow {
            text-align: center;
            font-size: 24px;
            color: #2563eb;
            padding: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table thead {
            background-color: #2563eb;
            color: white;
        }
        table th {
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        table td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 8px;
        }
        .totals-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
        }
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #d1d5db;
        }
        .total-item.grand-total {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 2px solid #2563eb;
            padding-top: 10px;
        }
        .observations-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin-top: 15px;
            border-radius: 4px;
        }
        .observations-box .label {
            font-size: 9px;
            color: #92400e;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .observations-box .value {
            font-size: 10px;
            color: #78350f;
        }
        .timeline {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .timeline-item {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .timeline-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .timeline-icon {
            width: 30px;
            height: 30px;
            background-color: #2563eb;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .timeline-icon.pending {
            background-color: #f59e0b;
        }
        .timeline-icon.in-transit {
            background-color: #3b82f6;
        }
        .timeline-icon.received {
            background-color: #10b981;
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-title {
            font-weight: bold;
            font-size: 11px;
            color: #111827;
            margin-bottom: 2px;
        }
        .timeline-date {
            font-size: 9px;
            color: #6b7280;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>TRASLADO DE MERCADERÍA</h1>
        <div class="transfer-number">{{ $transfer->transfer_number }}</div>
        <div>
            <span class="status-badge status-{{ $transfer->status }}">
                @if($transfer->status === 'PENDING')
                    PENDIENTE
                @elseif($transfer->status === 'IN_TRANSIT')
                    EN TRÁNSITO
                @elseif($transfer->status === 'RECEIVED')
                    RECIBIDO
                @elseif($transfer->status === 'CANCELLED')
                    CANCELADO
                @endif
            </span>
        </div>
    </div>

    <!-- Transfer Information -->
    <div class="info-section">
        <h2>INFORMACIÓN DEL TRASLADO</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Fecha de Traslado</label>
                <div class="value">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <label>Creado</label>
                <div class="value">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Warehouses -->
    <div class="info-section">
        <h2>ALMACENES</h2>
        <div class="info-grid">
            <div class="warehouse-box">
                <div class="label">Origen</div>
                <div class="value">{{ $transfer->warehouseOrigin->name }}</div>
            </div>
            <div class="warehouse-box">
                <div class="label">Destino</div>
                <div class="value">{{ $transfer->warehouseDestination->name }}</div>
            </div>
        </div>
        <div class="arrow">→</div>
    </div>

    <!-- Items Table -->
    <div class="info-section">
        <h2>PRODUCTOS TRASLADADOS</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 35%;">Producto</th>
                    <th style="width: 15%;">Lote</th>
                    <th style="width: 10%;" class="text-center">Cantidad</th>
                    <th style="width: 12%;" class="text-right">Costo Unit.</th>
                    <th style="width: 13%;" class="text-right">Subtotal</th>
                    <th style="width: 5%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQuantity = 0;
                    $totalCost = 0;
                @endphp
                @foreach($transfer->items as $item)
                @php
                    $subtotal = $item->quantity * $item->unit_cost;
                    $totalQuantity += $item->quantity;
                    $totalCost += $subtotal;
                @endphp
                <tr>
                    <td>{{ $item->product->code ?? 'N/A' }}</td>
                    <td>{{ $item->product->description ?? 'N/A' }}</td>
                    <td>{{ $item->batch->code ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right">${{ number_format($subtotal, 2) }}</td>
                    <td class="text-center">
                        @if($item->status === 'PENDING')
                            <span style="color: #f59e0b;">●</span>
                        @elseif($item->status === 'SENT')
                            <span style="color: #3b82f6;">●</span>
                        @elseif($item->status === 'RECEIVED')
                            <span style="color: #10b981;">✓</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-grid">
            <div>
                <div class="total-item">
                    <span>Total de Items:</span>
                    <strong>{{ $transfer->items->count() }}</strong>
                </div>
                <div class="total-item">
                    <span>Total de Unidades:</span>
                    <strong>{{ number_format($totalQuantity, 2) }}</strong>
                </div>
            </div>
            <div>
                <div class="total-item grand-total">
                    <span>VALOR TOTAL:</span>
                    <span>${{ number_format($totalCost, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    @if($transfer->status !== 'CANCELLED')
    <div class="timeline">
        <h2 style="font-size: 13px; color: #2563eb; margin-bottom: 15px;">SEGUIMIENTO</h2>

        <!-- Created -->
        <div class="timeline-item">
            <div class="timeline-icon pending">1</div>
            <div class="timeline-content">
                <div class="timeline-title">Traslado Creado</div>
                <div class="timeline-date">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <!-- Sent -->
        @if($transfer->sent_at)
        <div class="timeline-item">
            <div class="timeline-icon in-transit">2</div>
            <div class="timeline-content">
                <div class="timeline-title">Enviado - En Tránsito</div>
                <div class="timeline-date">
                    {{ \Carbon\Carbon::parse($transfer->sent_at)->format('d/m/Y H:i') }}
                    @if($transfer->sentBy)
                        - Por: {{ $transfer->sentBy->name ?? 'Usuario #' . $transfer->sent_by }}
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Received -->
        @if($transfer->received_at)
        <div class="timeline-item">
            <div class="timeline-icon received">3</div>
            <div class="timeline-content">
                <div class="timeline-title">Recibido</div>
                <div class="timeline-date">
                    {{ \Carbon\Carbon::parse($transfer->received_at)->format('d/m/Y H:i') }}
                    @if($transfer->receivedBy)
                        - Por: {{ $transfer->receivedBy->name ?? 'Usuario #' . $transfer->received_by }}
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Observations -->
    @if($transfer->observations)
    <div class="observations-box">
        <div class="label">Observaciones</div>
        <div class="value">{{ $transfer->observations }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Documento generado automáticamente por el sistema</strong></p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>ImporRepuestos - Sistema de Gestión de Traslados</p>
    </div>
</body>
</html>
