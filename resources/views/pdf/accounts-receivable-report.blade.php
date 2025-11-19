<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cuentas por Cobrar</title>
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
            padding: 10mm 8mm;
        }

        .header {
            background: #2563EB;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-bottom: 3px solid #1E40AF;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .header .company {
            font-size: 11px;
            font-weight: 600;
        }

        .header-right {
            text-align: right;
            margin-top: -45px;
        }

        .header-right .doc-date {
            font-size: 10px;
            background: #1E40AF;
            padding: 6px 10px;
            display: inline-block;
        }

        .filters-section {
            background: #DBEAFE;
            border: 2px solid #2563EB;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .filters-section h3 {
            color: #1E40AF;
            font-size: 11px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .filter-item {
            display: inline-block;
            background: white;
            padding: 4px 8px;
            margin-right: 8px;
            margin-bottom: 4px;
            border-radius: 3px;
            font-size: 9px;
            border: 1px solid #2563EB;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 8px;
        }

        .summary-card-inner {
            background: #F3F4F6;
            border: 2px solid #2563EB;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
        }

        .summary-card-label {
            font-size: 9px;
            color: #6B7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-card-value {
            font-size: 16px;
            color: #1E40AF;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead {
            background: #2563EB;
            color: white;
        }

        thead th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            border-right: 1px solid #1E40AF;
        }

        thead th:last-child {
            border-right: none;
        }

        thead th.text-right {
            text-align: right;
        }

        thead th.text-center {
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid #E5E7EB;
        }

        tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        tbody td {
            padding: 6px;
            font-size: 9px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-red {
            color: #DC2626;
        }

        .text-green {
            color: #059669;
        }

        .text-orange {
            color: #EA580C;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 3px;
        }

        .badge-pending {
            background: #FEE2E2;
            color: #991B1B;
        }

        .badge-partial {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-overdue {
            background: #FECACA;
            color: #7F1D1D;
            border: 1px solid #DC2626;
        }

        .totals-section {
            background: #DBEAFE;
            border: 3px solid #2563EB;
            padding: 12px;
            margin-top: 15px;
        }

        .totals-section h3 {
            color: #1E40AF;
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: bold;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 4px;
        }

        .totals-grid {
            display: table;
            width: 100%;
        }

        .total-item {
            display: table-cell;
            width: 33.33%;
            padding: 6px;
            text-align: center;
        }

        .total-label {
            font-size: 9px;
            color: #374151;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .total-value {
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #2563EB;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .footer .company-footer {
            font-weight: 600;
            color: #2563EB;
            font-size: 10px;
            margin-bottom: 3px;
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
        <h1>REPORTE DE CUENTAS POR COBRAR</h1>
        <div class="company">ImporRepuestos - Sistema de Gestión</div>
        <div class="header-right">
            <div class="doc-date">{{ $date }}</div>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(!empty($filterInfo))
    <div class="filters-section">
        <h3>Filtros Aplicados:</h3>
        @foreach($filterInfo as $filter)
            <span class="filter-item">{{ $filter }}</span>
        @endforeach
    </div>
    @endif

    <!-- Resumen -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-card-inner">
                <div class="summary-card-label">Total Ventas</div>
                <div class="summary-card-value">${{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-inner">
                <div class="summary-card-label">Total Pagado</div>
                <div class="summary-card-value text-green">${{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-inner">
                <div class="summary-card-label">Saldo Pendiente</div>
                <div class="summary-card-value text-red">${{ number_format($totalPending, 2) }}</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-inner">
                <div class="summary-card-label">Ventas Pendientes</div>
                <div class="summary-card-value">{{ $totalCount }}</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cuentas -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Venta</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 12%;">Vencimiento</th>
                <th class="text-right" style="width: 12%;">Total</th>
                <th class="text-right" style="width: 12%;">Pagado</th>
                <th class="text-right" style="width: 12%;">Saldo</th>
                <th class="text-center" style="width: 7%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accountsReceivable as $sale)
            <tr>
                <td class="font-bold">#{{ $sale->id }}</td>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                <td>
                    @if($sale->customer)
                        {{ $sale->customer->name }} {{ $sale->customer->last_name }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="@if($sale->due_date && \Carbon\Carbon::parse($sale->due_date)->lt(\Carbon\Carbon::now())) text-red font-bold @endif">
                    @if($sale->due_date)
                        {{ \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') }}
                        @if(\Carbon\Carbon::parse($sale->due_date)->lt(\Carbon\Carbon::now()))
                            <br><span class="badge badge-overdue">VENCIDA</span>
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right font-bold">${{ number_format($sale->sale_total, 2) }}</td>
                <td class="text-right text-green">${{ number_format($sale->total_paid ?? 0, 2) }}</td>
                <td class="text-right font-bold text-red">
                    ${{ number_format($sale->current_balance ?? $sale->pending_balance ?? 0, 2) }}
                </td>
                <td class="text-center">
                    @if($sale->payment_status == 1)
                        <span class="badge badge-pending">Pendiente</span>
                    @elseif($sale->payment_status == 2)
                        <span class="badge badge-partial">Parcial</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales Finales -->
    <div class="totals-section">
        <h3>Resumen General</h3>
        <div class="totals-grid">
            <div class="total-item">
                <div class="total-label">Total de Cuentas</div>
                <div class="total-value">{{ $totalCount }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Ventas Vencidas</div>
                <div class="total-value text-red">{{ $overdueCount }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Total a Cobrar</div>
                <div class="total-value text-red">${{ number_format($totalPending, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p class="company-footer">ImporRepuestos - Sistema de Gestión</p>
        <p>Reporte generado el {{ $date }}</p>
        <p style="margin-top: 3px; font-size: 7px; color: #9ca3af;">
            Este documento es un reporte informativo de cuentas por cobrar
        </p>
    </div>
</body>
</html>
