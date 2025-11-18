<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos</title>
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

        /* Encabezado principal */
        .header {
            background: #3b82f6;
            color: white;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-bottom: 3px solid #3b82f6;
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
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .header .company {
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
            opacity: 0.95;
        }

        .header .doc-info {
            font-size: 9px;
            margin-top: 2px;
            opacity: 0.85;
        }

        /* Secciones */
        .section {
            margin-bottom: 12px;
        }

        .section-title {
            background: #3b82f6;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            border-left: 3px solid #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Grilla de información */
        .info-grid {
            border: 1px solid #cbd5e1;
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
            padding: 5px 8px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            font-size: 9px;
        }

        .info-value {
            display: table-cell;
            padding: 5px 8px;
            color: #1e293b;
            font-size: 9px;
        }

        /* Tabla de pagos */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }

        table thead {
            background: #3b82f6;
            color: white;
        }

        table th {
            padding: 6px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
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
            padding: 5px;
            font-size: 9px;
            color: #334155;
        }

        /* Fila total */
        .total-row {
            background: #dbeafe !important;
            font-weight: bold;
            border-top: 2px solid #3b82f6 !important;
        }

        .total-row td {
            padding: 6px 5px !important;
            font-size: 10px !important;
            color: #1e40af !important;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
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

        /* Caja de resumen */
        .summary-box {
            background: #eff6ff;
            border: 2px solid #3b82f6;
            border-radius: 3px;
            padding: 10px;
            margin-top: 12px;
        }

        .summary-box h3 {
            color: #3b82f6;
            font-size: 11px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .summary-label {
            font-weight: 600;
            color: #475569;
            font-size: 8px;
        }

        .summary-value {
            font-weight: bold;
            color: #1e40af;
            font-size: 10px;
        }

        /* Utilidades de texto */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-primary {
            color: #3b82f6;
        }

        .text-success {
            color: #059669;
        }

        .text-warning {
            color: #d97706;
        }

        .amount-large {
            font-size: 11px;
            font-weight: bold;
        }

        /* Pie de página */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #3b82f6;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer .company-footer {
            font-weight: 600;
            color: #3b82f6;
            font-size: 9px;
            margin-bottom: 4px;
        }

        /* Optimización para impresión */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header, table thead, .badge, .summary-box, .total-row {
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
                <h1>REPORTE DE PAGOS</h1>
                <div class="company">ImporRepuestos - Sistema de Gestión</div>
            </div>
            <div class="header-right">
                <div style="font-size: 9px; font-weight: 600;">DOCUMENTO OFICIAL</div>
                <div style="font-size: 8px; margin-top: 2px; opacity: 0.85;">{{ $date }}</div>
            </div>
        </div>
    </div>

    <!-- Información de la Venta -->
    <div class="section">
        <div class="section-title">Información de la Venta</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Número de Documento:</div>
                <div class="info-value" style="font-weight: bold;">{{ $sale->document_internal_number ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cliente:</div>
                <div class="info-value">
                    @if($sale->customer)
                        {{ $sale->customer->name }} {{ $sale->customer->last_name }}
                    @else
                        Cliente General
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Venta:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total de la Venta:</div>
                <div class="info-value amount-large text-primary">${{ number_format($saleTotal, 2) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Pagado:</div>
                <div class="info-value amount-large text-success">${{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Saldo Pendiente:</div>
                <div class="info-value amount-large {{ $balance > 0 ? 'text-warning' : 'text-success' }}">
                    ${{ number_format($balance, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Pagos -->
    <div class="section">
        <div class="section-title">Detalle de Pagos - Total: {{ $paymentsCount }} {{ $paymentsCount == 1 ? 'Pago' : 'Pagos' }}</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No.</th>
                    <th style="width: 18%;">Método de Pago</th>
                    <th class="text-right" style="width: 12%;">Monto Pagado</th>
                    <th style="width: 20%;">Referencia</th>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 15%;">Hora</th>
                    <th style="width: 15%;">Cajero</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $index => $payment)
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #3b82f6;">{{ $index + 1 }}</td>
                    <td>
                        <span class="badge badge-primary">
                            {{ $payment->paymentMethod->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #059669; font-size: 9px;">
                        ${{ number_format($payment->payment_amount, 2) }}
                    </td>
                    <td style="font-family: 'Courier New', monospace; font-size: 8px; color: #64748b;">
                        {{ $payment->reference ?: 'Sin referencia' }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('H:i:s') }}</td>
                    <td>
                        @if($payment->casher)
                            {{ $payment->casher->name }} {{ $payment->casher->last_name }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" class="text-right">TOTAL PAGADO:</td>
                    <td class="text-right">${{ number_format($totalPaid, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Resumen General -->
    <div class="summary-box">
        <h3>Resumen General</h3>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell" style="width: 25%;">
                    <div class="summary-label">Número de Pagos:</div>
                    <div class="summary-value">{{ $paymentsCount }}</div>
                </div>
                <div class="summary-cell" style="width: 25%;">
                    <div class="summary-label">Monto Total Pagado:</div>
                    <div class="summary-value text-success">${{ number_format($totalPaid, 2) }}</div>
                </div>
                <div class="summary-cell" style="width: 25%;">
                    <div class="summary-label">Saldo Pendiente:</div>
                    <div class="summary-value {{ $balance > 0 ? 'text-warning' : 'text-success' }}">
                        ${{ number_format($balance, 2) }}
                    </div>
                </div>
                <div class="summary-cell" style="width: 25%;">
                    <div class="summary-label">Estado del Pago:</div>
                    <div class="summary-value">
                        @if($balance <= 0)
                            <span class="badge badge-success">PAGADO</span>
                        @else
                            <span class="badge badge-warning">PARCIAL</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p class="company-footer">ImporRepuestos - Sistema de Gestión</p>
        <p>Comprobante oficial de pagos - {{ $date }}</p>
    </div>
</body>
</html>
