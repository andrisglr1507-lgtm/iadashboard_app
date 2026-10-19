<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrackingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Location (Bin)',
            'Kode SKU',
            'Nama Produk',
            'Qty System',
            'Input Team A',
            'Input Team B',
            'Final Qty',
            'Status Tracking'
        ];
    }

    public function map($row): array
    {
        $status = 'BELUM DIHITUNG';
        if (is_null($row->result_status) && is_null($row->team_a_qty) && is_null($row->team_b_qty)) {
            $status = 'BELUM DIHITUNG';
        } else if (!is_null($row->team_a_qty) && is_null($row->team_b_qty)) {
            $status = 'HANYA TEAM A';
        } else if (is_null($row->team_a_qty) && !is_null($row->team_b_qty)) {
            $status = 'HANYA TEAM B';
        } else {
            $status = $row->result_status ?: 'MISMATCH';
        }

        return [
            $row->bin_code,
            $row->sku_code,
            $row->sku_name ?: 'Unknown Product',
            $row->system_qty,
            $row->team_a_qty,
            $row->team_b_qty,
            $row->final_qty,
            $status
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
