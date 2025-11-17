<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $headings;
    protected $title;

    public function __construct($data, $headings = [], $title = 'Reporte')
    {
        $this->data = is_array($data) ? new Collection($data) : $data;
        $this->headings = $headings;
        $this->title = $title;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        if (!empty($this->headings)) {
            return $this->headings;
        }

        // Si no se especificaron encabezados, usar las claves del primer elemento
        if ($this->data->isNotEmpty()) {
            $first = $this->data->first();
            if (is_array($first)) {
                return array_keys($first);
            } elseif (is_object($first)) {
                return array_keys((array)$first);
            }
        }

        return ['Datos'];
    }

    public function map($row): array
    {
        if (is_array($row)) {
            return array_values($row);
        } elseif (is_object($row)) {
            return array_values((array)$row);
        }

        return [$row];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
