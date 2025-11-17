<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'sales')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'sales':
                return [
                    'ID',
                    'Fecha',
                    'Cliente',
                    'Vendedor',
                    'Subtotal',
                    'Descuento',
                    'IVA',
                    'Total',
                    'Estado'
                ];
            case 'sales-by-seller':
                return [
                    'Vendedor',
                    'Total Ventas',
                    'Monto Total',
                    'Ticket Promedio'
                ];
            case 'sales-by-customer':
                return [
                    'Cliente',
                    'Total Ventas',
                    'Monto Total',
                    'Ticket Promedio',
                    'Última Venta'
                ];
            case 'sales-by-product':
                return [
                    'Código',
                    'Producto',
                    'Cantidad Vendida',
                    'Monto Total',
                    'Precio Promedio',
                    'Núm. Ventas'
                ];
            default:
                return ['Datos'];
        }
    }

    public function map($row): array
    {
        switch ($this->type) {
            case 'sales':
                return [
                    $row->id ?? $row['id'] ?? '',
                    $row->date ?? $row['date'] ?? '',
                    $row->customer->name ?? $row['customer']['name'] ?? 'N/A',
                    $row->employee->name ?? $row['employee']['name'] ?? 'N/A',
                    $row->subtotal ?? $row['subtotal'] ?? 0,
                    $row->discount ?? $row['discount'] ?? 0,
                    $row->tax ?? $row['tax'] ?? 0,
                    $row->total ?? $row['total'] ?? 0,
                    $row->status ?? $row['status'] ?? 'N/A'
                ];
            case 'sales-by-seller':
                return [
                    $row->employee->name ?? $row['employee']['name'] ?? 'N/A',
                    $row->total_sales ?? $row['total_sales'] ?? 0,
                    $row->total_amount ?? $row['total_amount'] ?? 0,
                    $row->average_ticket ?? $row['average_ticket'] ?? 0
                ];
            case 'sales-by-customer':
                return [
                    $row->customer->name ?? $row['customer']['name'] ?? 'N/A',
                    $row->total_sales ?? $row['total_sales'] ?? 0,
                    $row->total_amount ?? $row['total_amount'] ?? 0,
                    $row->average_ticket ?? $row['average_ticket'] ?? 0,
                    $row->last_sale_date ?? $row['last_sale_date'] ?? 'N/A'
                ];
            case 'sales-by-product':
                return [
                    $row->product->code ?? $row['product']['code'] ?? 'N/A',
                    $row->product->name ?? $row['product']['name'] ?? 'N/A',
                    $row->total_quantity ?? $row['total_quantity'] ?? 0,
                    $row->total_amount ?? $row['total_amount'] ?? 0,
                    $row->average_price ?? $row['average_price'] ?? 0,
                    $row->sales_count ?? $row['sales_count'] ?? 0
                ];
            default:
                return [(string)$row];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        $titles = [
            'sales' => 'Reporte de Ventas',
            'sales-by-seller' => 'Ventas por Vendedor',
            'sales-by-customer' => 'Ventas por Cliente',
            'sales-by-product' => 'Ventas por Producto'
        ];

        return $titles[$this->type] ?? 'Reporte';
    }
}
