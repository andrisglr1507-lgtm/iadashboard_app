@extends('layouts.app')

@section('title', 'Edit Bad Stock')
@section('page_title', 'Edit Bad Stock')

@section('content')
<div class="content-panel" style="padding: 24px; margin-top: 20px;">
    
    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="margin-bottom: 24px;">
        <h4 style="margin: 0; color: var(--phoenix-dark);">{{ $record->sku_code }}</h4>
        <div style="color: var(--phoenix-text-muted); font-size: 0.9rem;">{{ $record->product->sku_name ?? 'Unknown Product' }}</div>
    </div>

    <form action="{{ route('sodc.bad_stock.update', $record->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
            
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Qty Carton</label>
                <input type="number" name="qty_carton" value="{{ old('qty_carton', $record->qty_carton) }}" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Qty PCS</label>
                <input type="number" name="qty_pcs" value="{{ old('qty_pcs', $record->qty_pcs) }}" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Total Qty</label>
                <input type="number" name="total_qty" value="{{ old('total_qty', $record->total_qty) }}" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;" required>
            </div>
            
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Category</label>
                <select name="category" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;" required>
                    <option value="BAD_STOCK" {{ old('category', $record->category) == 'BAD_STOCK' ? 'selected' : '' }}>Bad Stock</option>
                    <option value="EXPIRED_STOCK" {{ old('category', $record->category) == 'EXPIRED_STOCK' ? 'selected' : '' }}>Expired Stock</option>
                    <option value="GOOD_STOCK" {{ old('category', $record->category) == 'GOOD_STOCK' ? 'selected' : '' }}>Good Stock</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Item Status</label>
                <select name="item_status" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;" required>
                    <option value="SALEABLE" {{ old('item_status', $record->item_status) == 'SALEABLE' ? 'selected' : '' }}>Saleable</option>
                    <option value="NON_SALEABLE" {{ old('item_status', $record->item_status) == 'NON_SALEABLE' ? 'selected' : '' }}>Non-Saleable</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Batch No</label>
                <input type="text" name="batch_no" value="{{ old('batch_no', $record->batch_no) }}" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--phoenix-dark); margin-bottom: 8px;">Expired Date</label>
                <input type="date" name="expired_date" value="{{ old('expired_date', $record->expired_date) }}" style="width: 100%; padding: 10px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); font-family: inherit; font-size: 0.9rem;">
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn-primary" style="background: var(--phoenix-primary); color: white; border: none; padding: 10px 24px; border-radius: var(--phoenix-btn-radius); font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Simpan Perubahan
            </button>
            <a href="{{ route('sodc.bad_stock.index') }}" style="background: var(--phoenix-bg-main); color: var(--phoenix-text-muted); border: 1px solid var(--phoenix-border-color); padding: 10px 24px; border-radius: var(--phoenix-btn-radius); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='var(--phoenix-bg-main)'">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
