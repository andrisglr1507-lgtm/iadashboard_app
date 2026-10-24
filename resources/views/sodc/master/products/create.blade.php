@extends('layouts.app')
@section('title', 'Tambah Product')
@section('page_title', 'Tambah Product')

@section('content')
<div class="content-panel" style="padding: 24px; margin-top: 20px;">
    
    <div style="display: flex; gap: 40px; align-items: flex-start;">
        
        <!-- Kolom Form (Kiri) -->
        <div style="flex: 1;">
            <form action="{{ isset($item) ? route('sodc.products.update', $item->id) : route('sodc.products.store') }}" method="POST">
                @csrf
                @if(isset($item)) @method('PUT') @endif

                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>SKU Code</label>
                    <input type='text' name='sku_code' value="{{ $item->sku_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>SKU Name</label>
                    <input type='text' name='sku_name' value="{{ $item->sku_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Barcode (Item Barcode)</label>
                    <input type='text' id='barcode-input' name='barcode' value="{{ $item->barcode ?? '' }}" placeholder="Barcode satuan barang" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Carton Code (Barcode Karton/Luar)</label>
                    <input type='text' name='carton_code' value="{{ $item->carton_code ?? '' }}" placeholder="Barcode kemasan karton luar" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Packname (Satuan Kemasan)</label>
                    <input type='text' name='packname' value="{{ $item->packname ?? '' }}" placeholder="Misal: PCS, BOX, CTN, PACK, BAL" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>UOM (Isi per Kemasan)</label>
                    <input type='number' name='uom' value="{{ $item->uom ?? 1 }}" min="1" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Kategori Produk (Category)</label>
                    <input type='text' name='product_category_code' value="{{ $item->product_category_code ?? '' }}" placeholder="Misal: Puding, Minuman, Makanan" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Principal</label>
                    <input type='text' name='principal' value="{{ $item->principal ?? '' }}" placeholder="Nama Principal/Merk" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                <div style='margin-bottom: 15px;'>
                    <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Company Code</label>
                    <input type='text' name='company_code' value="{{ $item->company_code ?? '' }}" placeholder="Misal: CSAN" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 15px; width: 100%; padding: 12px; font-size: 0.95rem;">
                    <i class="fas fa-save"></i> Simpan Data Product
                </button>
            </form>
        </div>

        <!-- Kolom Gambar (Kanan) -->
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #f8fafc; border-radius: 12px; padding: 20px; min-height: 400px; gap: 20px;">
            <img src="{{ asset('images/box.png') }}" alt="Box Illustration" style="max-width: 80%; max-height: 300px; object-fit: contain;">
            
            <div id="barcode-container" style="display: none; background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Preview Barcode</div>
                <svg id="barcode"></svg>
            </div>
        </div>
        
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const barcodeInput = document.getElementById('barcode-input');
        const barcodeContainer = document.getElementById('barcode-container');
        const barcodeSvg = document.getElementById('barcode');

        function generateBarcode() {
            const val = barcodeInput.value.trim();
            if (val) {
                barcodeContainer.style.display = 'block';
                JsBarcode(barcodeSvg, val, {
                    format: "CODE128",
                    lineColor: "#0f172a",
                    width: 2,
                    height: 50,
                    displayValue: true,
                    fontSize: 14,
                    margin: 0
                });
            } else {
                barcodeContainer.style.display = 'none';
            }
        }

        // Generate saat halaman dimuat (misal jika sedang mode edit)
        generateBarcode();

        // Generate secara real-time saat user mengetik
        barcodeInput.addEventListener('input', generateBarcode);
    });
</script>
@endpush