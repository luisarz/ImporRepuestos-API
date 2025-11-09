<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Código Original',
            'Código de Barras',
            'Descripción',
            'Marca',
            'Categoría',
            'Unidad de Medida',
            'Estado',
            'Descontinuado',
            'Gravado'
        ];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->original_code,
            $product->barcode,
            $product->description,
            $product->brand->name ?? 'N/A',
            $product->category->name ?? 'N/A',
            $product->unitMeasurement->name ?? 'N/A',
            $product->is_active ? 'Activo' : 'Inactivo',
            $product->is_discontinued ? 'Sí' : 'No',
            $product->is_taxed ? 'Sí' : 'No'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Productos';
    }
}
