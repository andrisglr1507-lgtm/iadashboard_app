<?php

namespace App\Exports\KonfirmasiFaktur;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'ID Pelanggan',
            'Nama Pelanggan',
            'No. Telepon',
            'Alamat',
            'Koordinat GPS',
            'Jam Buka',
            'Jam Tutup',
            'Waktu Kunjungan (Mnt)',
            'Tipe Outlet',
            'Akses Motor',
            'Akses Truk',
            'Kode Cabang'
        ];
    }

    public function map($row): array
    {
        $row = (array) $row;
        
        $mt = isset($row['mt']) && $row['mt'] == 1 ? 'Modern Trade (MT)' : 'General Trade (GT)';
        $bikeOnly = isset($row['bikeOnly']) && $row['bikeOnly'] == 1 ? 'Ya' : 'Tidak';
        $truckOnly = isset($row['truckOnly']) && $row['truckOnly'] == 1 ? 'Ya' : 'Tidak';

        return [
            $row['dataId'] ?? '-',
            $row['partnerName'] ?? '-',
            $row['phoneNumber'] ?? '-',
            $row['address'] ?? '-',
            $row['coordinate'] ?? '-',
            $row['openTime'] ?? '-',
            $row['closeTime'] ?? '-',
            $row['visitTime'] ?? '-',
            $mt,
            $bikeOnly,
            $truckOnly,
            $row['branchId'] ?? '-'
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1ABB9C']
                ]
            ],
        ];
    }
}
