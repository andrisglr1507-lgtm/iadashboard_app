@extends('layouts.app')
@section('title', 'Reference WMS')
@section('page_title', 'Opname Reference')

@section('page_actions')
<a href="{{ route('sodc.references.import_page') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none;">
    <i class="fas fa-file-import"></i> Import Data WMS
</a>
@endsection

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div style="margin-top: 20px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <table class="premium-table" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Reference Code</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Date</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Totals (Bin/SKU/Qty)</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: bold;'>
                    <a href="{{ route('sodc.references.show', $row->id) }}" style="color: #0369a1; text-decoration: none;">{{ $row->reference_code }}</a>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ date('d-m-Y H:i', strtotime($row->reference_datetime)) }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 5px;">{{ $row->total_bin }} Bin</span>
                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 5px;">{{ $row->total_sku }} SKU</span>
                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">{{ (float)$row->total_qty }} Qty</span>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">{{ $row->status }}</span>
                </td>
                <td>
                    <a href="{{ route('sodc.references.show', $row->id) }}" style="color: #0284c7; margin-right: 10px; font-size: 0.9rem;"><i class="fas fa-eye"></i> Detail</a>
                    <form action="{{ route('sodc.references.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Menghapus Referensi WMS ini akan menghapus semua detail baris stock di dalamnya. Yakin?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: 0.9rem;"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">Belum ada Dokumen Referensi WMS. Silakan Import.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        {{ $data->links() }}
    </div>
</div>
@endsection