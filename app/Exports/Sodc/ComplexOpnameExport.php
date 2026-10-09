<?php

namespace App\Exports\Sodc;

use App\Models\OpnameResult;
use App\Models\OpnameCount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComplexOpnameExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return OpnameResult::with(['referenceDetail.product'])
            ->where('session_id', $this->sessionId)
            ->get();
    }

    public function map($result): array
    {
        $counts = OpnameCount::where('session_id', $this->sessionId)
            ->where('reference_detail_id', $result->reference_detail_id)
            ->get()
            ->groupBy('count_sequence');

        // Sequence 1 (Bisa 2 jika double mode, kita ambil salah satu atau jumlahkan? Kita ambil hitungan pertama / tim A)
        $c1 = $counts->has(1) ? $counts->get(1)->first() : null;
        $r1 = $counts->has(2) ? $counts->get(2)->first() : null;
        $r2 = $counts->has(3) ? $counts->get(3)->first() : null;
        $r3 = $counts->has(4) ? $counts->get(4)->first() : null;

        $p = $result->referenceDetail->product;

        return [
            $result->referenceDetail->bin_code ?? '-',
            $result->referenceDetail->sku_code ?? '-',
            $p->sku_name ?? '-',
            $p->uom ?? '-',
            $p->barcode ?? '-',
            $p->principal ?? '-',
            $result->system_qty,
            
            // Count 1
            $c1 ? (float)$c1->input_karton : '-',
            $c1 ? (float)$c1->input_pcs : '-',
            $c1 ? (float)$c1->count_qty : '-',

            // R1
            $r1 ? (float)$r1->input_karton : '-',
            $r1 ? (float)$r1->input_pcs : '-',
            $r1 ? (float)$r1->count_qty : '-',

            // R2
            $r2 ? (float)$r2->input_karton : '-',
            $r2 ? (float)$r2->input_pcs : '-',
            $r2 ? (float)$r2->count_qty : '-',

            // R3
            $r3 ? (float)$r3->input_karton : '-',
            $r3 ? (float)$r3->input_pcs : '-',
            $r3 ? (float)$r3->count_qty : '-',

            $result->final_qty,
            $result->result_status,
        ];
    }

    public function headings(): array
    {
        return [
            'Bin Code',
            'SKU Code',
            'Product Name',
            'UOM',
            'Barcode',
            'Principal',
            'WMS / System Qty',
            
            'C1 Karton',
            'C1 Pcs',
            'C1 Total Pcs',

            'R1 Karton',
            'R1 Pcs',
            'R1 Total Pcs',

            'R2 Karton',
            'R2 Pcs',
            'R2 Total Pcs',

            'R3 Karton',
            'R3 Pcs',
            'R3 Total Pcs',

            'Final Qty (Total Pcs)',
            'Status Akhir',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
