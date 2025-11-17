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
        .header .transfer-number {
            font-size: 14px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 10px;
            letter-spacing: 0.5px;
        }
        .status-PENDING {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-IN_TRANSIT {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .status-RECEIVED {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-CANCELLED {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .info-section {
            margin-bottom: 12px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }
        .info-section h2 {
            font-size: 11px;
            color: #e12828;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e12828;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-grid {
            width: 100%;
            margin-top: 8px;
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
            padding: 6px;
            background-color: #f9fafb;
            border-left: 3px solid #e12828;
            margin-bottom: 8px;
        }
        .info-item:nth-child(2n) {
            margin-right: 0;
        }
        .info-item label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
            font-weight: bold;
        }
        .info-item .value {
            font-size: 11px;
            color: #111827;
            font-weight: 600;
        }
        .warehouse-box {
            width: 48%;
            float: left;
            margin-right: 4%;
            background: #fff5f5;
            border: 2px solid #e12828;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(225, 40, 40, 0.1);
        }
        .warehouse-box:nth-child(2n) {
            margin-right: 0;
        }
        .warehouse-box .label {
            font-size: 8px;
            color: #e12828;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .warehouse-box .value {
            font-size: 11px;
            color: #c22020;
            font-weight: 700;
            line-height: 1.2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border: 1px solid #e5e7eb;
        }
        table thead {
            background: #e12828;
            color: white;
        }
        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e12828;
        }
        table td {
            padding: 6px;
            font-size: 9px;
            border: 1px solid #e5e7eb;
            background: white;
        }
        table tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 12px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .totals-grid {
            width: 100%;
        }
        .totals-grid::after {
            content: "";
            display: table;
            clear: both;
        }
        .total-left {
            width: 60%;
            float: left;
        }
        .total-right {
            width: 38%;
            float: right;
        }
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #d1d5db;
            font-size: 9px;
        }
        .total-item.grand-total {
            font-size: 12px;
            font-weight: 700;
            color: #e12828;
            background: white;
            padding: 8px;
            border-radius: 6px;
            margin-top: 4px;
            border: 2px solid #e12828;
            box-shadow: 0 2px 6px rgba(225, 40, 40, 0.1);
        }
        .observations-box {
            background: #fef3c7;
            border-left: 5px solid #f59e0b;
            padding: 8px;
            margin-top: 12px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.1);
        }
        .observations-box .label {
            font-size: 8px;
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .observations-box .value {
            font-size: 9px;
            color: #78350f;
            line-height: 1.4;
        }
        .timeline {
            margin-top: 12px;
            background: white;
            border-radius: 8px;
            padding: 10px;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .timeline h2 {
            font-size: 11px;
            color: #e12828;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #e12828;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .timeline-container {
            position: relative;
            padding-left: 42px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: -31px;
            top: 26px;
            width: 2px;
            height: calc(100% - 8px);
            background: #e5e7eb;
        }
        .timeline-last {
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .timeline-last::after {
            display: none !important;
        }
        .timeline-icon {
            position: absolute;
            left: -45px;
            top: 0;
            width: 28px;
            height: 28px;
            background: #e12828;
            color: white;
            border-radius: 50%;
            line-height: 28px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(225, 40, 40, 0.3);
            border: 2px solid white;
        }
        .timeline-icon.pending {
            background: #f59e0b;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
        }
        .timeline-icon.in-transit {
            background: #e12828;
            box-shadow: 0 2px 6px rgba(225, 40, 40, 0.3);
        }
        .timeline-icon.received {
            background: #10b981;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        }
        .timeline-icon-disabled {
            background: #9ca3af !important;
            box-shadow: 0 2px 6px rgba(156, 163, 175, 0.2) !important;
        }
        .timeline-content {
            padding: 6px 10px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .timeline-title {
            font-weight: 700;
            font-size: 10px;
            color: #111827;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .timeline-date {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .timeline-user {
            font-size: 8px;
            color: #4b5563;
            line-height: 1.2;
            background: #fff;
            padding: 3px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 3px;
            border: 1px solid #e5e7eb;
        }
        .timeline-description {
            font-size: 7px;
            color: #6b7280;
            line-height: 1.2;
            margin-top: 3px;
            font-style: italic;
        }
        .timeline-pending {
            opacity: 0.6;
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
    </style>
</head>
<body>
    <div class="page">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="company-name">ImporRepuestos - Sistema de Gestión de Inventario</div>
            <h1>TRASLADO DE MERCADERÍA</h1>
            <div class="transfer-number">{{ $transfer->transfer_number }}</div>
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
        <h2>ALMACENES INVOLUCRADOS</h2>
        <div class="warehouse-box">
            <div class="label">ORIGEN</div>
            <div class="value">{{ $transfer->warehouseOrigin->name }}</div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 3px;">
                {{ $transfer->warehouseOrigin->code ?? '' }}
            </div>
        </div>
        <div class="warehouse-box">
            <div class="label">DESTINO</div>
            <div class="value">{{ $transfer->warehouseDestination->name }}</div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 3px;">
                {{ $transfer->warehouseDestination->code ?? '' }}
            </div>
        </div>
        <div style="clear: both;"></div>
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
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-size: 8px; font-weight: 600;">PEND</span>
                        @elseif($item->status === 'SENT')
                            <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 8px; font-weight: 600;">ENV</span>
                        @elseif($item->status === 'RECEIVED')
                            <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 8px; font-weight: 600;">REC</span>
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
            <div class="total-left">
                <div class="total-item">
                    <span>Total de Items:</span>
                    <strong>{{ $transfer->items->count() }}</strong>
                </div>
                <div class="total-item">
                    <span>Total de Unidades:</span>
                    <strong>{{ number_format($totalQuantity, 2) }}</strong>
                </div>
            </div>
            <div class="total-right">
                <div class="total-item grand-total">
                    <span>TOTAL:</span>
                    <span>${{ number_format($totalCost, 2) }}</span>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <!-- Timeline -->
    @if($transfer->status !== 'CANCELLED')
    <div class="timeline">
        <h2>SEGUIMIENTO</h2>
        <div class="timeline-container">
            <!-- Created -->
            <div class="timeline-item">
                <div class="timeline-icon pending">1</div>
                <div class="timeline-content">
                    <div class="timeline-title">Creado</div>
                    <div class="timeline-date">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <!-- Sent -->
            @if($transfer->sent_at)
            <div class="timeline-item">
                <div class="timeline-icon in-transit">2</div>
                <div class="timeline-content">
                    <div class="timeline-title">Enviado</div>
                    <div class="timeline-date">{{ \Carbon\Carbon::parse($transfer->sent_at)->format('d/m/Y H:i') }}</div>
                    @if($transfer->sentByUser)
                    <div class="timeline-user">{{ $transfer->sentByUser->name }}</div>
                    @endif
                </div>
            </div>
            @else
            <div class="timeline-item timeline-pending">
                <div class="timeline-icon timeline-icon-disabled">2</div>
                <div class="timeline-content">
                    <div class="timeline-title" style="color: #9ca3af;">Pendiente Envío</div>
                </div>
            </div>
            @endif

            <!-- Received -->
            @if($transfer->received_at)
            <div class="timeline-item timeline-last">
                <div class="timeline-icon received">3</div>
                <div class="timeline-content">
                    <div class="timeline-title">Recibido</div>
                    <div class="timeline-date">{{ \Carbon\Carbon::parse($transfer->received_at)->format('d/m/Y H:i') }}</div>
                    @if($transfer->receivedByUser)
                    <div class="timeline-user">{{ $transfer->receivedByUser->name }}</div>
                    @endif
                </div>
            </div>
            @else
            <div class="timeline-item timeline-pending timeline-last">
                <div class="timeline-icon timeline-icon-disabled">3</div>
                <div class="timeline-content">
                    <div class="timeline-title" style="color: #9ca3af;">Pendiente Recibir</div>
                </div>
            </div>
            @endif
        </div>
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
    </div>
</body>
</html>
