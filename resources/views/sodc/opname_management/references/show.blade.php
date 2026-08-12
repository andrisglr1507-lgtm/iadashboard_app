@extends('layouts.app')
@section('title', 'Detail Reference WMS')
@section('page_title', 'Detail Opname Reference')

@section('page_actions')
<a href="{{ route('sodc.references.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
    <!-- Info Dokumen -->
    <div style="flex: 1; min-width: 300px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
        <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Informasi Dokumen</h3>
        <table style="width: 100%; font-size: 0.9rem;">
            <tr>
                <td style="padding: 8px 0; color: #64748b; width: 40%;">Reference Code</td>
                <td style="padding: 8px 0; font-weight: 600; color: #0f172a;">{{ $reference->reference_code }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b;">Location Scope</td>
                <td style="padding: 8px 0; font-weight: 600; color: #0369a1;">Multi-Warehouse (Global)</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b;">Tanggal WMS</td>
                <td style="padding: 8px 0; font-weight: 600; color: #0f172a;">{{ date('d F Y, H:i', strtotime($reference->reference_datetime)) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b;">Status</td>
                <td style="padding: 8px 0;">
                    <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">{{ $reference->status }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Angka -->
    <div style="flex: 2; min-width: 300px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8); display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
        <div style="flex: 1; min-width: 120px; text-align: center; border-right: 1px solid #e2e8f0;">
            <div style="font-size: 2rem; font-weight: 800; color: #0ea5e9;">{{ $reference->total_bin }}</div>
            <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-top: 5px;">Total Bin</div>
        </div>
        <div style="flex: 1; min-width: 120px; text-align: center; border-right: 1px solid #e2e8f0;">
            <div style="font-size: 2rem; font-weight: 800; color: #8b5cf6;">{{ $reference->total_sku }}</div>
            <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-top: 5px;">Total SKU</div>
        </div>
        <div style="flex: 1; min-width: 120px; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: #10b981;">{{ (float)$reference->total_qty }}</div>
            <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-top: 5px;">Total Qty (Pcs)</div>
        </div>
    </div>
</div>

<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a;">Rincian Item (Snapshot WMS)</h3>
    <div style="overflow-x: auto;">
        <table class="premium-table" style="width: 100%; text-align: left; border-collapse: collapse; min-width: 800px;">
            <thead>
            <tr>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Bin Code</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Product Info</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Packname & UOM</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>System Qty (Karton & Pcs)</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Batch / Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $row)
            @php
                $sysQty = (float)$row->system_qty;
                $uomInt = ($row->product && $row->product->uom > 0) ? (int)$row->product->uom : 1;
                $ctn = floor($sysQty / $uomInt);
                $pcs = $sysQty % $uomInt;
            @endphp
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #0369a1;'>{{ $row->bin_code }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <div style="font-weight: bold; color: #0f172a;">{{ $row->sku_code }}</div>
                    <div style="font-size: 0.85rem; color: #64748b;">{{ $row->product ? $row->product->sku_name : '-' }}</div>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">{{ $row->product ? $row->product->packname : '-' }}</div>
                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                        Isi: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: bold;">{{ $uomInt }} Pcs</span>
                    </div>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <div style="font-size: 0.85rem; color: #475569;">
                        Total: <b style="color: #10b981;">{{ $sysQty }} Pcs</b>
                    </div>
                    <div style="margin-top: 5px;">
                        <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; color: #334155;">{{ $ctn }} Karton</span>
                        <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; color: #334155;">{{ $pcs }} Pcs</span>
                    </div>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    @if($row->batch_number || $row->expiry_date)
                        <div style="font-size: 0.85rem; color: #475569;">Batch: <b>{{ $row->batch_number ?: '-' }}</b></div>
                        <div style="font-size: 0.85rem; color: #475569;">Exp: <b>{{ $row->expiry_date ? date('d-m-Y', strtotime($row->expiry_date)) : '-' }}</b></div>
                    @else
                        <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $details->links() }}
    </div>
</div>
@endsection
