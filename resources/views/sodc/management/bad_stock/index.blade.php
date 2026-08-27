@extends('layouts.app')

@section('page_title', 'Rekap Bad Stock')

@section('page_actions')
<a href="{{ route('sodc.bad_stock.index') }}" class="btn-refresh" style="display: inline-flex; align-items: center; gap: 8px; background: white; color: var(--phoenix-text-muted); border: 1px solid var(--phoenix-border-color); padding: 8px 16px; border-radius: var(--phoenix-btn-radius); font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);" onmouseover="this.style.color='var(--phoenix-primary)'; this.style.borderColor='var(--phoenix-primary)';" onmouseout="this.style.color='var(--phoenix-text-muted)'; this.style.borderColor='var(--phoenix-border-color)';">
    <i class="fas fa-sync-alt"></i> Refresh Data
</a>
@endsection

@section('content')
<div class="content-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 24px; border-bottom: 1px solid var(--phoenix-border-color);">
        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark);">Daftar Barang Rusak & Expired</h3>
        
        <!-- Filter Bar -->
        <form action="{{ route('sodc.bad_stock.index') }}" method="GET" style="display: flex; gap: 12px; margin: 0;">
            <select name="category" onchange="this.form.submit()" style="padding: 8px 16px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); background-color: var(--phoenix-bg-main); color: var(--phoenix-dark); font-weight: 600; font-size: 0.85rem; outline: none;">
                <option value="">Semua Kategori</option>
                <option value="BAD_STOCK" {{ request('category') == 'BAD_STOCK' ? 'selected' : '' }}>Bad Stock</option>
                <option value="EXPIRED_STOCK" {{ request('category') == 'EXPIRED_STOCK' ? 'selected' : '' }}>Expired Stock</option>
                <option value="GOOD_STOCK" {{ request('category') == 'GOOD_STOCK' ? 'selected' : '' }}>Good Stock</option>
            </select>

            <select name="item_status" onchange="this.form.submit()" style="padding: 8px 16px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); background-color: var(--phoenix-bg-main); color: var(--phoenix-dark); font-weight: 600; font-size: 0.85rem; outline: none;">
                <option value="">Semua Status</option>
                <option value="SALEABLE" {{ request('item_status') == 'SALEABLE' ? 'selected' : '' }}>Saleable</option>
                <option value="NON_SALEABLE" {{ request('item_status') == 'NON_SALEABLE' ? 'selected' : '' }}>Non-Saleable</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>ID Product</th>
                    <th>Nama Product</th>
                    <th>UOM</th>
                    <th>Packname</th>
                    <th style="text-align: right;">Qty Carton</th>
                    <th style="text-align: right;">Qty Pcs</th>
                    <th style="text-align: right;">Total Qty</th>
                    <th style="text-align: center;">Koli</th>
                    <th style="text-align: center;">Kategori</th>
                    <th style="text-align: center;">Status</th>
                    <th>Batch No</th>
                    <th>Tgl Expired</th>
                    <th>User</th>
                    <th>Tgl Record</th>
                    <th style="text-align: center; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $row)
                <tr>
                    <td style="font-weight: 700; color: var(--phoenix-primary);">{{ $row->sku_code }}</td>
                    <td style="font-weight: 600; color: var(--phoenix-dark);">{{ Str::limit($row->product->sku_name ?? 'Unknown', 30) }}</td>
                    <td style="font-size: 0.85rem; color: var(--phoenix-text-muted);">{{ $row->product->uom ?? '-' }}</td>
                    <td style="font-size: 0.85rem; color: var(--phoenix-text-muted);">{{ $row->product->packname ?? '-' }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ $row->qty_carton }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ $row->qty_pcs }}</td>
                    <td style="text-align: right; font-weight: 800; color: var(--phoenix-dark);">{{ $row->total_qty }}</td>
                    <td style="text-align: center; font-weight: 600; color: var(--phoenix-primary);">{{ $row->koli ?? '-' }}</td>
                    <td style="text-align: center;">
                        @if($row->category == 'EXPIRED_STOCK')
                            <span style="font-size: 0.7rem; background: rgba(245, 158, 11, 0.1); color: var(--phoenix-warning); padding: 4px 8px; border-radius: 6px; font-weight: 800;">EXPIRED</span>
                        @elseif($row->category == 'GOOD_STOCK')
                            <span style="font-size: 0.7rem; background: rgba(16, 185, 129, 0.1); color: var(--phoenix-success); padding: 4px 8px; border-radius: 6px; font-weight: 800;">GOOD STOCK</span>
                        @else
                            <span style="font-size: 0.7rem; background: rgba(230, 55, 87, 0.1); color: var(--phoenix-danger); padding: 4px 8px; border-radius: 6px; font-weight: 800;">BAD STOCK</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($row->item_status == 'SALEABLE')
                            <span style="font-size: 0.7rem; background: rgba(39, 199, 143, 0.1); color: var(--phoenix-success); padding: 4px 8px; border-radius: 6px; font-weight: 800;">SALEABLE</span>
                        @else
                            <span style="font-size: 0.7rem; background: rgba(116, 129, 148, 0.1); color: var(--phoenix-text-muted); padding: 4px 8px; border-radius: 6px; font-weight: 800;">NON SALEABLE</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; font-weight: 600;">{{ $row->batch_no ?? '-' }}</td>
                    <td style="font-size: 0.85rem; color: var(--phoenix-text-muted);">{{ $row->expired_date ? \Carbon\Carbon::parse($row->expired_date)->format('d M Y') : '-' }}</td>
                    <td style="font-size: 0.85rem; font-weight: 600; color: var(--phoenix-dark);">{{ $row->user->full_name ?? $row->user->username ?? 'Sistem' }}</td>
                    <td style="font-size: 0.85rem; color: var(--phoenix-text-muted);">
                        {{ $row->created_at->format('d M Y') }} {{ $row->created_at->format('H:i') }}
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; justify-content: center; gap: 8px;">
                            <a href="{{ route('sodc.bad_stock.edit', $row->id) }}" style="color: var(--phoenix-primary); padding: 6px; border-radius: 6px; background: rgba(56, 116, 255, 0.1); transition: all 0.2s;" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('sodc.bad_stock.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: rgba(230, 55, 87, 0.1); border: none; color: var(--phoenix-danger); padding: 6px 8px; border-radius: 6px; cursor: pointer; transition: all 0.2s;" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" style="text-align: center; padding: 32px; color: var(--phoenix-text-muted);">
                        Belum ada data barang rusak atau expired.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($records->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--phoenix-border-color); background: #fdfdfd; border-radius: 0 0 var(--phoenix-border-radius) var(--phoenix-border-radius);">
        {{ $records->links() }}
    </div>
    @endif
</div>
@endsection
