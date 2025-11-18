<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
            background: #ffffff;
            padding: 15mm 10mm;
        }

        .header {
            background: #009EF7;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 4px solid #0077C1;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            text-align: right;
        }

        .header h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 0.8px;
        }

        .header .company {
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        .header .doc-info {
            font-size: 11px;
            margin-top: 3px;
            background: #0077C1;
            padding: 6px 10px;
            display: inline-block;
            font-weight: bold;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            background: #009EF7;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
            border-left: 4px solid #0077C1;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            border: 2px solid #009EF7;
            overflow: hidden;
        }

        .info-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row:nth-child(even) {
            background-color: #f8fafc;
        }

        .info-label {
            display: table-cell;
            width: 30%;
            padding: 7px 10px;
            font-weight: 600;
            color: #0077C1;
            background: #E8F5FE;
            border-right: 2px solid #009EF7;
            font-size: 9px;
        }

        .info-value {
            display: table-cell;
            padding: 7px 10px;
            color: #1e293b;
            font-size: 10px;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #009EF7;
            margin-top: 10px;
        }

        table thead {
            background: #009EF7;
            color: white;
        }

        table th {
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        table th:last-child {
            border-right: none;
        }

        table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table td {
            padding: 6px 5px;
            font-size: 9px;
            color: #334155;
        }

        .total-row {
            background: #E8F5FE !important;
            font-weight: bold;
            border-top: 3px solid #009EF7 !important;
        }

        .total-row td {
            padding: 10px 5px !important;
            font-size: 11px !important;
            color: #0077C1 !important;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .badge-info {
            background-color: #E8F5FE;
            color: #009EF7;
            border: 1px solid #009EF7;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 3px solid #009EF7;
            text-align: center;
            color: #64748b;
            font-size: 9px;
        }

        .footer p {
            margin: 3px 0;
        }

        .footer .company-footer {
            font-weight: 600;
            color: #009EF7;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 40px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #374151;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 10px;
            color: #374151;
            font-weight: 600;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <h1>ORDEN DE COMPRA</h1>
                <div class="company">ImporRepuestos - Sistema de Gestión</div>
            </div>
            <div class="header-right">
                <div class="doc-info">OC #{{ $purchase->purchase_number ?? $purchase->id }}</div>
                <div style="font-size: 9px; margin-top: 5px;">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Información del Proveedor -->
    <div class="section">
        <div class="section-title">Información del Proveedor</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Proveedor:</div>
                <div class="info-value" style="font-weight: bold;">
                    {{ $purchase->provider->comercial_name ?? $purchase->provider->legal_name ?? 'N/A' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Documento:</div>
                <div class="info-value">{{ $purchase->provider->document_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Información de la Compra -->
    <div class="section">
        <div class="section-title">Información de la Orden</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Almacén:</div>
                <div class="info-value">{{ $purchase->warehouse->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Encargado:</div>
                <div class="info-value">
                    @if($purchase->employee)
                        {{ $purchase->employee->name }} {{ $purchase->employee->last_name }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Compra:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Condición de Operación:</div>
                <div class="info-value">
                    <span class="badge {{ $purchase->operation_condition_id == '1' ? 'badge-success' : 'badge-warning' }}">
                        {{ $purchase->operationCondition->name ?? 'N/A' }}
                    </span>
                </div>
            </div>
            @if($purchase->due_date)
            <div class="info-row">
                <div class="info-label">Fecha de Vencimiento:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($purchase->due_date)->format('d/m/Y') }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value">
                    @php
                        $statusConfig = [
                            '1' => ['label' => 'Procesando', 'class' => 'badge-warning'],
                            '2' => ['label' => 'Finalizada', 'class' => 'badge-success'],
                            '3' => ['label' => 'Anulada', 'class' => 'badge-danger']
                        ];
                        $status = $statusConfig[$purchase->status_purchase] ?? ['label' => 'Desconocido', 'class' => 'badge-info'];
                    @endphp
                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="section">
        <div class="section-title">Detalle de Productos</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No.</th>
                    <th style="width: 40%;">Producto</th>
                    <th style="width: 15%;" class="text-right">Cantidad</th>
                    <th style="width: 15%;" class="text-right">Precio Unit.</th>
                    <th style="width: 10%;" class="text-right">Desc. %</th>
                    <th style="width: 15%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseItems as $index => $item)
                @php
                    $product = $item->batches->first()?->inventory?->product;
                @endphp
                <tr>
                    <td class="text-center" style="font-weight: bold;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $product->product_name ?? 'Producto desconocido' }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">
                            Código: {{ $product->product_code ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->discount ?? 0, 2) }}%</td>
                    <td class="text-right" style="font-weight: bold;">${{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach

                <!-- Totales -->
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTAL:</td>
                    <td class="text-right">${{ number_format($purchase->total_purchase, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                Firma del Responsable de Compra
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Firma del Proveedor
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p class="company-footer">ImporRepuestos - Sistema de Gestión</p>
        <p>Orden de Compra - Documento generado el {{ $date }}</p>
        <p style="margin-top: 5px; font-size: 8px; color: #9ca3af;">
            Este documento es una orden oficial de compra
        </p>
    </div>
</body>
</html>
