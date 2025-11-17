<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1B84FF;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #1B84FF;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 9px;
            color: #666;
        }

        .summary {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .summary-item {
            flex: 1;
            min-width: 150px;
            padding: 8px;
            background-color: white;
            border-left: 3px solid #1B84FF;
        }

        .summary-item .label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }

        .summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .filters {
            font-size: 9px;
            color: #666;
            margin-bottom: 10px;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: #1B84FF;
            color: white;
        }

        th {
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }

        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generado el: {{ $generatedAt }}</p>
        <p class="filters">Filtros aplicados: {{ $filters }}</p>
    </div>

    @if(!empty($summary))
    <div class="summary">
        @foreach($summary as $key => $value)
        <div class="summary-item">
            <div class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
            <div class="value">
                @if(is_numeric($value))
                    @if(str_contains($key, 'amount') || str_contains($key, 'total') || str_contains($key, 'cost') || str_contains($key, 'value'))
                        ${{ number_format($value, 2) }}
                    @else
                        {{ number_format($value, 0) }}
                    @endif
                @else
                    {{ $value }}
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($row as $key => $cell)
                        <td class="{{ is_numeric($cell) ? 'text-right' : '' }}">
                            @if(is_numeric($cell) && (str_contains($key, 'amount') || str_contains($key, 'total') || str_contains($key, 'cost') || str_contains($key, 'price')))
                                ${{ number_format($cell, 2) }}
                            @elseif(is_numeric($cell))
                                {{ number_format($cell, 0) }}
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" class="text-center">No hay datos para mostrar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>ImporRepuestos - Sistema de Gestión | Página {PAGE_NUM} de {PAGE_COUNT}</p>
    </div>
</body>
</html>
