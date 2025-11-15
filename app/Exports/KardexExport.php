<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $kardex;

    public function __construct($kardex)
    {
        $this->kardex = $kardex;
    }

    public function collection()
    {
        return $this->kardex;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo Doc.',
            'No. Doc.',
            'Producto',
            'Código',
            'Tipo Movimiento',
            'Stock Previo',
            'Cantidad',
            'Saldo',
            'Costo Unit.',
            'Almacén',
            'Lote',
            'Entidad'
        ];
    }

    public function map($item): array
    {
        $movementType = '';
        if ($item->stock_in > 0) {
            $movementType = 'Entrada';
        } elseif ($item->stock_out > 0) {
            $movementType = 'Salida';
        } else {
            $movementType = 'Ajuste';
        }

        $quantity = $item->stock_in > 0 ? $item->stock_in : $item->stock_out;

        return [
            $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y H:i') : '',
            $item->document_type ?? 'N/A',
            $item->document_number ?? 'N/A',
            $item->inventory?->product?->description ?? 'N/A',
            $item->inventory?->product?->code ?? 'N/A',
            $movementType,
            number_format($item->previous_stock ?? 0, 2, '.', ','),
            number_format($quantity ?? 0, 2, '.', ','),
            number_format($item->stock_actual ?? 0, 2, '.', ','),
            number_format($item->promedial_cost ?? 0, 2, '.', ','),
            $item->warehouse?->name ?? 'N/A',
            $item->inventoryBatch?->batch?->code ?? '',
            $item->entity ?? 'N/A'
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
        return 'Kardex';
    }
}
