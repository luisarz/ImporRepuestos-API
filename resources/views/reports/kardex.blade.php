<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Kardex</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 18px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 15px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 8px;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 7px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #4CAF50;
            color: white;
        }
        .badge-danger {
            background-color: #f44336;
            color: white;
        }
        .badge-warning {
            background-color: #ff9800;
            color: white;
        }
        .text-success { color: #4CAF50; font-weight: bold; }
        .text-danger { color: #f44336; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Kardex - Movimientos de Inventario</h1>
        <div class="info">
            <strong>Período:</strong> {{ $startDate ?? 'Todos' }} - {{ $endDate ?? 'Todos' }} |
            @if($warehouse)
                <strong>Almacén:</strong> {{ $warehouse }} |
            @endif
            @if($movementType)
                <strong>Tipo:</strong> {{ ucfirst($movementType) }} |
            @endif
            <strong>Fecha:</strong> {{ $date }} |
            <strong>Total Movimientos:</strong> {{ $kardex->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo Doc.</th>
                <th>No. Doc.</th>
                <th>Producto</th>
                <th>Tipo Mov.</th>
                <th>Stock Previo</th>
                <th>Cantidad</th>
                <th>Saldo</th>
                <th>Costo Unit.</th>
                <th>Almacén</th>
                <th>Entidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kardex as $item)
            @php
                $movementType = '';
                $badgeClass = '';
                $quantityClass = '';
                $quantity = 0;

                if ($item->stock_in > 0) {
                    $movementType = 'Entrada';
                    $badgeClass = 'badge-success';
                    $quantityClass = 'text-success';
                    $quantity = '+' . number_format($item->stock_in, 2);
                } elseif ($item->stock_out > 0) {
                    $movementType = 'Salida';
                    $badgeClass = 'badge-danger';
                    $quantityClass = 'text-danger';
                    $quantity = '-' . number_format($item->stock_out, 2);
                } else {
                    $movementType = 'Ajuste';
                    $badgeClass = 'badge-warning';
                    $quantity = '0.00';
                }

                $warehouseName = $item->warehouse?->name ?? 'N/A';
                $batchCode = $item->inventoryBatch?->batch?->code ?? null;
                $displayWarehouse = $batchCode ? "$warehouseName - $batchCode" : $warehouseName;
            @endphp
            <tr>
                <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y H:i') : 'N/A' }}</td>
                <td>{{ $item->document_type ?? 'N/A' }}</td>
                <td>{{ $item->document_number ?? 'N/A' }}</td>
                <td>
                    <strong>{{ $item->inventory?->product?->code ?? 'N/A' }}</strong><br>
                    {{ $item->inventory?->product?->description ?? 'N/A' }}
                </td>
                <td>
                    <span class="badge {{ $badgeClass }}">{{ $movementType }}</span>
                </td>
                <td>{{ number_format($item->previous_stock ?? 0, 2) }}</td>
                <td class="{{ $quantityClass }}">{{ $quantity }}</td>
                <td><strong>{{ number_format($item->stock_actual ?? 0, 2) }}</strong></td>
                <td>${{ number_format($item->promedial_cost ?? 0, 2) }}</td>
                <td>{{ $displayWarehouse }}</td>
                <td>{{ $item->entity ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generado automáticamente por el sistema | {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
