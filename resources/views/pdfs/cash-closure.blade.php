<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cierre de Caja #{{ $opening->id }}</title>
    <style>
        @page {
            margin: 0mm;
            size: letter portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.3;
            padding: 12mm 12mm;
        }

        /* HEADER */
        .header {
            background: #475569;
            color: white;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            position: relative;
        }

        .header-grid {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .doc-subtitle {
            font-size: 10px;
            opacity: 0.95;
            margin-bottom: 1px;
        }

        .doc-info {
            font-size: 8px;
            opacity: 0.85;
        }

        .doc-number-box {
            background: rgba(255,255,255,0.15);
            padding: 6px 10px;
            border-radius: 4px;
            border: 2px solid rgba(255,255,255,0.25);
        }

        .doc-number-label {
            font-size: 7px;
            opacity: 0.9;
            margin-bottom: 1px;
        }

        .doc-number {
            font-size: 14px;
            font-weight: 700;
        }

        /* INFO CARDS */
        .info-section {
            margin-bottom: 8px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 5px 8px;
            width: 25%;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }

        .info-cell:last-child {
            border-right: none;
        }

        .info-row:last-child .info-cell {
            border-bottom: none;
        }

        .info-label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 9px;
            color: #0f172a;
            font-weight: 600;
        }

        /* SECTION TITLE */
        .section-title {
            background: #475569;
            color: white;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 5px 0;
            border-radius: 3px;
        }

        /* TABLES */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        thead {
            background: #f8fafc;
            border-bottom: 2px solid #475569;
        }

        th {
            padding: 5px 8px;
            text-align: left;
            font-size: 7px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        td {
            padding: 4px 8px;
            font-size: 8px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr.cash-row {
            background: #e0f2fe;
            font-weight: 700;
        }

        tbody tr.cash-row td {
            color: #0c4a6e;
        }

        tfoot {
            background: #f1f5f9;
            border-top: 2px solid #475569;
        }

        tfoot td {
            font-weight: 700;
            color: #0f172a;
            padding: 5px 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* DENOMINATION SECTION */
        .denomination-grid {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .denomination-column {
            display: table-cell;
            width: 50%;
            padding: 0 3px;
        }

        .denomination-column:first-child {
            padding-left: 0;
        }

        .denomination-column:last-child {
            padding-right: 0;
        }

        .denomination-header {
            background: #f8fafc;
            padding: 4px 8px;
            border-left: 3px solid #475569;
            margin-bottom: 4px;
            font-weight: 700;
            font-size: 8px;
            color: #1e293b;
            border-radius: 2px;
        }

        .denom-table {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }

        .denom-table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .denom-table th {
            font-size: 6px;
            padding: 3px 6px;
        }

        .denom-table td {
            font-size: 7px;
            padding: 2px 6px;
        }

        .denom-table tfoot {
            background: #475569;
            color: white;
            border-top: none;
        }

        .denom-table tfoot td {
            color: white;
            font-weight: 700;
            padding: 3px 6px;
        }

        /* CONCILIATION BOX */
        .conciliation-box {
            border: 2px solid #475569;
            border-radius: 4px;
            padding: 8px;
            margin: 6px 0;
            background: #f8fafc;
        }

        .conciliation-grid {
            display: table;
            width: 100%;
        }

        .conciliation-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 4px;
        }

        .conciliation-label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
            letter-spacing: 0.3px;
        }

        .conciliation-value {
            font-size: 12px;
            font-weight: 700;
        }

        .amount-positive {
            color: #059669;
        }

        .amount-negative {
            color: #475569;
        }

        .amount-neutral {
            color: #475569;
        }

        .status-label {
            font-size: 7px;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* NOTES BOX */
        .notes-box {
            background: #fef3c7;
            border-left: 2px solid #f59e0b;
            padding: 6px 8px;
            margin: 6px 0;
            border-radius: 2px;
        }

        .notes-title {
            font-weight: 700;
            margin-bottom: 3px;
            font-size: 7px;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .notes-content {
            font-size: 7px;
            color: #78350f;
            line-height: 1.4;
        }

        /* SIGNATURES */
        .signatures {
            margin-top: 15px;
            display: table;
            width: 100%;
        }

        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 6px;
        }

        .signature-line {
            border-top: 1px solid #334155;
            margin-top: 20px;
            padding-top: 4px;
            font-size: 7px;
            font-weight: 600;
        }

        .signature-role {
            font-size: 6px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* FOOTER */
        .footer {
            background: #f8fafc;
            padding: 8px 15px;
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .footer-company {
            font-weight: 700;
            color: #475569;
            margin-bottom: 1px;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .cash-badge {
            background: #bae6fd;
            color: #075985;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: 700;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-grid">
            <div class="header-left">
                <div class="doc-title">Cierre de Caja Diario</div>
                <div class="doc-subtitle">{{ $opening->cashRegister->name ?? 'N/A' }}</div>
                <div class="doc-info">{{ $opening->cashRegister->warehouse->name ?? 'N/A' }}</div>
                <div class="doc-info">Generado: {{ date('d/m/Y H:i:s') }}</div>
            </div>
            <div class="header-right">
                <div class="doc-number-box">
                    <div class="doc-number-label">N° DOCUMENTO</div>
                    <div class="doc-number">#{{ str_pad($opening->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO GENERAL -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Estado</div>
                    <div class="info-value">
                        @if($opening->status === 'closed')
                            <span class="badge badge-success">CERRADO</span>
                        @else
                            <span class="badge badge-warning">ABIERTO</span>
                        @endif
                    </div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Usuario</div>
                    <div class="info-value">{{ $opening->user->name ?? 'N/A' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Fecha Apertura</div>
                    <div class="info-value">{{ $opening->opened_at ? \Carbon\Carbon::parse($opening->opened_at)->format('d/m/Y H:i:s') : 'N/A' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Fecha Cierre</div>
                    <div class="info-value">{{ $opening->closed_at ? \Carbon\Carbon::parse($opening->closed_at)->format('d/m/Y H:i:s') : 'N/A' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Monto Apertura</div>
                    <div class="info-value">${{ number_format($opening->opening_amount, 2) }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Cerrado por</div>
                    <div class="info-value">{{ $opening->closingUser->name ?? $opening->user->name ?? 'N/A' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Efectivo Esperado</div>
                    <div class="info-value">${{ number_format($cash_details['expected_cash'] ?? 0, 2) }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Efectivo Contado</div>
                    <div class="info-value">${{ number_format($opening->closing_amount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOCUMENTOS DTE VENDIDOS -->
    @if(isset($dte_documents) && count($dte_documents) > 0)
        <div class="section-title">Documentos Electrónicos Vendidos (DTE)</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Documento</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Del Nº</th>
                    <th class="text-center">Al Nº</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dte_documents as $doc)
                    <tr>
                        <td>{{ $doc['name'] }} ({{ $doc['code'] }})</td>
                        <td class="text-center">{{ $doc['count'] }}</td>
                        <td class="text-center">{{ $doc['first_number'] }}</td>
                        <td class="text-center">{{ $doc['last_number'] }}</td>
                        <td class="text-right">${{ number_format($doc['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL DOCUMENTOS</td>
                    <td class="text-center">{{ array_sum(array_column($dte_documents, 'count')) }}</td>
                    <td colspan="2" class="text-center">-</td>
                    <td class="text-right">${{ number_format(array_sum(array_column($dte_documents, 'total')), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- VENTAS POR MÉTODO DE PAGO -->
    <div class="section-title">Ventas por Método de Pago</div>
    <table>
        <thead>
            <tr>
                <th>Método de Pago</th>
                <th class="text-center">Transacciones</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($sales['by_payment_method']) && count($sales['by_payment_method']) > 0)
                @foreach($sales['by_payment_method'] as $method => $data)
                    <tr class="{{ isset($data['code']) && $data['code'] === '01' ? 'cash-row' : '' }}">
                        <td>
                            {{ $method }}
                            @if(isset($data['code']) && $data['code'] === '01')
                                <span class="cash-badge">EFECTIVO</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $data['count'] }}</td>
                        <td class="text-right">${{ number_format($data['total'], 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center" style="padding: 15px; color: #94a3b8;">No hay ventas registradas</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL VENTAS</td>
                <td class="text-center">{{ $sales['count'] ?? 0 }}</td>
                <td class="text-right">${{ number_format($sales['total'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- MOVIMIENTOS DE CAJA -->
    <div class="section-title">Movimientos de Caja</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ingresos Adicionales</td>
                <td class="text-center">{{ $summary['count_incomes'] ?? 0 }}</td>
                <td class="text-right">${{ number_format($summary['total_income'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Egresos</td>
                <td class="text-center">{{ $summary['count_expenses'] ?? 0 }}</td>
                <td class="text-right">${{ number_format($summary['total_expense'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>EFECTIVO ESPERADO</td>
                <td class="text-center">-</td>
                <td class="text-right">${{ number_format($cash_details['expected_cash'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- CONTEO DE DENOMINACIONES -->
    @if(isset($cash_details['denominations']) && count($cash_details['denominations']) > 0)
        <div class="section-title">Conteo de Denominaciones</div>
        <div class="denomination-grid">
            <!-- BILLETES -->
            <div class="denomination-column">
                <div class="denomination-header">BILLETES</div>
                <table class="denom-table">
                    <thead>
                        <tr>
                            <th>Denom.</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $billTotal = 0;
                            $bills = $cash_details['denominations']->get('bill', collect());
                        @endphp
                        @forelse($bills->sortByDesc('denomination') as $denom)
                            @php $billTotal += $denom->total; @endphp
                            <tr>
                                <td>${{ number_format($denom->denomination, 2) }}</td>
                                <td class="text-center">{{ $denom->quantity }}</td>
                                <td class="text-right">${{ number_format($denom->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #94a3b8;">Sin billetes</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($bills->count() > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="text-right">${{ number_format($billTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- MONEDAS -->
            <div class="denomination-column">
                <div class="denomination-header">MONEDAS</div>
                <table class="denom-table">
                    <thead>
                        <tr>
                            <th>Denom.</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $coinTotal = 0;
                            $coins = $cash_details['denominations']->get('coin', collect());
                        @endphp
                        @forelse($coins->sortByDesc('denomination') as $denom)
                            @php $coinTotal += $denom->total; @endphp
                            <tr>
                                <td>${{ number_format($denom->denomination, 2) }}</td>
                                <td class="text-center">{{ $denom->quantity }}</td>
                                <td class="text-right">${{ number_format($denom->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #94a3b8;">Sin monedas</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($coins->count() > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="text-right">${{ number_format($coinTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    <!-- CONCILIACIÓN -->
    <div class="section-title">Conciliación</div>
    <div class="conciliation-box">
        <div class="conciliation-grid">
            <div class="conciliation-item">
                <div class="conciliation-label">Esperado</div>
                <div class="conciliation-value">${{ number_format($cash_details['expected_cash'] ?? 0, 2) }}</div>
            </div>
            <div class="conciliation-item">
                <div class="conciliation-label">Contado</div>
                <div class="conciliation-value">${{ number_format($opening->closing_amount ?? 0, 2) }}</div>
            </div>
            <div class="conciliation-item">
                @php
                    $difference = ($opening->closing_amount ?? 0) - ($cash_details['expected_cash'] ?? 0);
                    $diffClass = $difference < 0 ? 'amount-negative' : ($difference > 0 ? 'amount-positive' : 'amount-neutral');
                    $diffLabel = $difference < 0 ? 'Faltante' : ($difference > 0 ? 'Sobrante' : 'Cuadrado');
                @endphp
                <div class="conciliation-label">Diferencia</div>
                <div class="conciliation-value {{ $diffClass }}">
                    {{ $difference >= 0 ? '+' : '' }}${{ number_format(abs($difference), 2) }}
                </div>
                <div class="status-label {{ $diffClass }}">{{ $diffLabel }}</div>
            </div>
        </div>
    </div>

    <!-- NOTAS DE CIERRE -->
    @if($opening->closing_notes)
        <div class="notes-box">
            <div class="notes-title">Notas de Cierre</div>
            <div class="notes-content">{{ $opening->closing_notes }}</div>
        </div>
    @endif

    <!-- AUTORIZACIÓN -->
    @if($opening->authorized_by)
        <div class="notes-box">
            <div class="notes-title">Autorización</div>
            <div class="notes-content">
                <strong>Autorizado por:</strong> {{ $opening->authorizedByUser->name ?? 'N/A' }}<br>
                @if($opening->authorization_notes)
                    <strong>Notas:</strong> {{ $opening->authorization_notes }}
                @endif
            </div>
        </div>
    @endif

    <!-- FIRMAS -->
    <div class="signatures">
        <div class="signature-cell">
            <div class="signature-line">
                {{ $opening->user->name ?? 'N/A' }}
                <div class="signature-role">Cajero</div>
            </div>
        </div>
        <div class="signature-cell">
            <div class="signature-line">
                {{ $opening->closingUser->name ?? $opening->user->name ?? 'N/A' }}
                <div class="signature-role">Supervisor</div>
            </div>
        </div>
        @if($opening->authorized_by)
            <div class="signature-cell">
                <div class="signature-line">
                    {{ $opening->authorizedByUser->name ?? 'N/A' }}
                    <div class="signature-role">Autorizó</div>
                </div>
            </div>
        @endif
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-company">ImporRepuestos - Sistema de Gestión Empresarial</div>
        <div>Cierre de Caja #{{ $opening->id }} - Generado {{ date('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>
