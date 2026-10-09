<?php

namespace App\Exports\Sodc;

use App\Models\OpnameResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SimpleOpnameExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        return [
            $result->referenceDetail->bin_code ?? '-',
            $result->referenceDetail->sku_code ?? '-',
            $result->referenceDetail->product->sku_name ?? '-',
            $result->system_qty,
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
            'WMS / System Qty',
            'Final Qty',
            'Status',
        ];
    }
}
