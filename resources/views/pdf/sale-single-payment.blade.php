<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
            background: #ffffff;
            padding: 15mm 10mm;
        }

        .header {
            background: #2563EB;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 4px solid #1E40AF;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 0.8px;
        }

        .header .company {
            font-size: 13px;
            font-weight: 600;
        }

        .header-right {
            text-align: right;
            margin-top: -50px;
        }

        .header-right .doc-number {
            font-size: 18px;
            font-weight: bold;
            background: #1E40AF;
            padding: 10px 15px;
            display: inline-block;
        }

        .header-right .doc-date {
            font-size: 11px;
            margin-top: 6px;
            background: #1E40AF;
            padding: 4px 8px;
            display: inline-block;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #2563EB;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: uppercase;
            border-left: 4px solid #1E40AF;
        }

        .info-grid {
            border: 3px solid #2563EB;
            overflow: hidden;
        }

        .info-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row:nth-child(even) {
            background-color: #f9fafb;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            padding: 10px 12px;
            font-weight: 600;
            color: #1E40AF;
            background: #DBEAFE;
            border-right: 2px solid #2563EB;
            font-size: 10px;
        }

        .info-value {
            display: table-cell;
            padding: 10px 12px;
            color: #1f2937;
            font-size: 11px;
            font-weight: 500;
        }

        .payment-amount-box {
            background: #DBEAFE;
            border: 4px solid #2563EB;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }

        .payment-amount-box .label {
            font-size: 13px;
            color: #1E40AF;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .payment-amount-box .amount {
            font-size: 40px;
            color: #2563EB;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #059669;
        }

        .badge-primary {
            background-color: #DBEAFE;
            color: #2563EB;
            border: 1px solid #2563EB;
        }

        .summary-box {
            background: #DBEAFE;
            border: 3px solid #2563EB;
            padding: 18px;
            margin-top: 20px;
        }

        .summary-box h3 {
            color: #2563EB;
            font-size: 14px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563EB;
            text-transform: uppercase;
            font-weight: bold;
        }

        .balance-info {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .balance-item {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
        }

        .balance-item .label {
            font-size: 11px;
            color: #374151;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .balance-item .value {
            font-size: 20px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 3px solid #2563EB;
            text-align: center;
            color: #64748b;
            font-size: 9px;
        }

        .footer .company-footer {
            font-weight: 600;
            color: #2563EB;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 50px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #374151;
            margin-top: 60px;
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
        <h1>COMPROBANTE DE PAGO</h1>
        <div class="company">ImporRepuestos - Sistema de Gestión</div>
        <div class="header-right">
            <div class="doc-number">PAGO #{{ $payment->id }}</div>
            <div class="doc-date">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <!-- Monto del Pago -->
    <div class="payment-amount-box">
        <div class="label">Monto Pagado</div>
        <div class="amount">${{ number_format($payment->payment_amount, 2) }}</div>
    </div>

    <!-- Información de la Venta -->
    <div class="section">
        <div class="section-title">Información de la Venta</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Número de Venta:</div>
                <div class="info-value" style="font-weight: bold; font-size: 12px; color: #2563EB;">
                    {{ $sale->document_internal_number ?? 'N/A' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Cliente:</div>
                <div class="info-value">
                    @if($sale->customer)
                        {{ $sale->customer->name }} {{ $sale->customer->last_name }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            @if($sale->customer && ($sale->customer->document_number || $sale->customer->nit || $sale->customer->nrc))
            <div class="info-row">
                <div class="info-label">Documento / NIT / NRC:</div>
                <div class="info-value">
                    @if($sale->customer->document_number)
                        Doc: {{ $sale->customer->document_number }}
                    @endif
                    @if($sale->customer->nit)
                        {{ $sale->customer->document_number ? ' / ' : '' }}NIT: {{ $sale->customer->nit }}
                    @endif
                    @if($sale->customer->nrc)
                        {{ ($sale->customer->document_number || $sale->customer->nit) ? ' / ' : '' }}NRC: {{ $sale->customer->nrc }}
                    @endif
                </div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Fecha de Venta:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total de la Venta:</div>
                <div class="info-value" style="font-weight: bold; color: #2563EB;">
                    ${{ number_format($sale->sale_total, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Pago -->
    <div class="section">
        <div class="section-title">Detalle del Pago</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Método de Pago:</div>
                <div class="info-value">
                    <span class="badge badge-primary">
                        {{ $payment->paymentMethod->name ?? 'N/A' }}
                    </span>
                </div>
            </div>
            @if($payment->reference)
            <div class="info-row">
                <div class="info-label">Referencia:</div>
                <div class="info-value" style="font-family: 'Courier New', monospace;">
                    {{ $payment->reference }}
                </div>
            </div>
            @endif
            @if($payment->bank_account_id)
            <div class="info-row">
                <div class="info-label">Cuenta Bancaria:</div>
                <div class="info-value">{{ $payment->bank_account_id }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Cajero:</div>
                <div class="info-value">
                    @if($payment->casher)
                        {{ $payment->casher->name }} {{ $payment->casher->last_name }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha y Hora:</div>
                <div class="info-value">
                    {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Saldos -->
    <div class="summary-box">
        <h3>Resumen de Saldos</h3>
        <div class="balance-info">
            <div class="balance-item">
                <div class="label">Saldo Anterior</div>
                <div class="value" style="color: #dc2626;">
                    ${{ number_format($previousBalance, 2) }}
                </div>
            </div>
            <div class="balance-item">
                <div class="label">Nuevo Saldo</div>
                <div class="value" style="color: {{ $payment->actual_balance > 0 ? '#f59e0b' : '#059669' }};">
                    ${{ number_format($payment->actual_balance, 2) }}
                    @if($payment->actual_balance <= 0)
                        <div style="margin-top: 8px;">
                            <span class="badge badge-success">PAGADO COMPLETAMENTE</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                Firma del Cajero
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Firma del Cliente
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p class="company-footer">ImporRepuestos - Sistema de Gestión</p>
        <p>Comprobante oficial de pago - Documento generado el {{ $date }}</p>
        <p style="margin-top: 5px; font-size: 8px; color: #9ca3af;">
            Este documento es un comprobante válido de pago realizado
        </p>
    </div>
</body>
</html>
