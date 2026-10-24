@extends('layouts.app')
@section('title', 'Import Product')
@section('page_title', 'Import Master Product')

@section('page_actions')
<a href="{{ route('sodc.products.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Product
</a>
@endsection

@section('content')
@if(session('error'))
    <div style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-times-circle" style="font-size: 1.2rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div style="padding: 28px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="margin: 0 0 6px 0; color: #1e293b; display: flex; align-items: center; gap: 10px; font-size: 1.25rem;">
                <i class="fas fa-file-import" style="color: #0ea5e9;"></i> Upload File CSV Master Product
            </h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
                Gunakan fitur ini untuk menambah produk baru atau memperbarui data (seperti Packname, UOM, Kategori) secara massal via CSV.
            </p>
        </div>
        <a href="{{ route('sodc.products.template') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 10px 18px; border-radius: 8px; font-size: 0.88rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">
            <i class="fas fa-download"></i> Download Template CSV Resmi
        </a>
    </div>

    <!-- Table Column Guidelines -->
    <div style="margin-bottom: 24px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
        <h5 style="margin: 0 0 12px 0; font-size: 0.92rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Format Struktur Kolom CSV (Sesuai Tabel Database):
        </h5>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem; background: white; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569; text-align: left; font-weight: 700;">
                        <th style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1;">Nama Kolom CSV</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1;">Wajib?</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1;">Keterangan</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1;">Contoh Isi</th>
                    </tr>
                </thead>
                <tbody style="color: #334155;">
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold; color: #0284c7;">sku_code</td>
                        <td style="padding: 8px 12px;"><span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Wajib</span></td>
                        <td style="padding: 8px 12px;">Kode unik SKU (Kunci identifikasi produk)</td>
                        <td style="padding: 8px 12px; font-family: monospace;">PRD001 / 01-001B</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">sku_name</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Nama deskripsi lengkap produk</td>
                        <td style="padding: 8px 12px;">Kopi Saset ABC 10x20g</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">barcode</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Barcode satuan (Item Barcode)</td>
                        <td style="padding: 8px 12px; font-family: monospace;">8991234567890</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">carton_code</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Barcode kemasan karton / luar</td>
                        <td style="padding: 8px 12px; font-family: monospace;">C-8991234567890</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #fefce8;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold; color: #854d0e;">product_category_code</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;"><b>Kategori Produk</b> (Kategori dari WMS/Master)</td>
                        <td style="padding: 8px 12px;">Puding / Minuman / Biskuit</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #f0fdf4;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold; color: #166534;">packname</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;"><b>Satuan Kemasan</b> (bukan kategori)</td>
                        <td style="padding: 8px 12px;"><span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 700;">PCS / BOX / CTN / PACK / BAL</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">uom</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Kuantitas / isi satuan terkecil per pack (Angka)</td>
                        <td style="padding: 8px 12px; font-weight: 600;">1 / 12 / 24 / 30</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">principal</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Nama Supplier / Prinsipal</td>
                        <td style="padding: 8px 12px;">ABC Group / Mayora</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold;">company_code</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Kode Cabang / Perusahaan</td>
                        <td style="padding: 8px 12px; font-family: monospace;">CSAN</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: bold; color: #0f766e;">is_active</td>
                        <td style="padding: 8px 12px; color: #94a3b8;">Opsional</td>
                        <td style="padding: 8px 12px;">Status aktif produk (<b>1</b> = Aktif, <b>0</b> = Nonaktif). Default: 1</td>
                        <td style="padding: 8px 12px; font-family: monospace; font-weight: 600;">1 / 0</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <small style="display: block; margin-top: 8px; color: #64748b;">
            * Sistem mengenali nama kolom secara fleksibel (misal: <code>packname</code> / <code>kemasan</code>, <code>uom</code> / <code>isi</code>, <code>product_category_code</code> / <code>kategori</code>, <code>company_code</code> / <code>cabang</code>).
        </small>
    </div>

    <!-- Form Import -->
    <form action="{{ route('sodc.products.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Pilihan Metode Import / Update -->
        <div style="margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; color: #1e293b; margin-bottom: 10px; font-size: 0.95rem;">
                Pilih Metode Update Data:
            </label>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                <!-- Opsi 1: Upsert -->
                <label style="border: 2px solid #0ea5e9; background: #f0f9ff; border-radius: 10px; padding: 14px; cursor: pointer; display: flex; gap: 12px; align-items: flex-start; transition: all 0.2s;">
                    <input type="radio" name="import_mode" value="upsert" checked style="margin-top: 4px; accent-color: #0ea5e9;">
                    <div>
                        <div style="font-weight: 700; color: #0369a1; font-size: 0.92rem;">Update & Tambah Baru (Rekomendasi)</div>
                        <div style="font-size: 0.8rem; color: #0284c7; margin-top: 4px; line-height: 1.4;">
                            Jika SKU sudah ada ➔ update datanya dengan nilai CSV.<br>
                            Jika SKU belum ada ➔ tambahkan sebagai baris baru.
                        </div>
                    </div>
                </label>

                <!-- Opsi 2: Update Only Empty -->
                <label style="border: 1px solid #cbd5e1; background: white; border-radius: 10px; padding: 14px; cursor: pointer; display: flex; gap: 12px; align-items: flex-start; transition: all 0.2s;" onmouseover="this.style.borderColor='#94a3b8'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#cbd5e1'">
                    <input type="radio" name="import_mode" value="update_only_empty" style="margin-top: 4px; accent-color: #0ea5e9;">
                    <div>
                        <div style="font-weight: 700; color: #334155; font-size: 0.92rem;">Hanya Isi Kolom yang Kosong</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; line-height: 1.4;">
                            Hanya mengupdate kolom di database yang nilainya masih kosong/NULL (misal: mengisi Packname yang belum terisi tanpa menimpa data lama).
                        </div>
                    </div>
                </label>

                <!-- Opsi 3: Insert Only -->
                <label style="border: 1px solid #cbd5e1; background: white; border-radius: 10px; padding: 14px; cursor: pointer; display: flex; gap: 12px; align-items: flex-start; transition: all 0.2s;" onmouseover="this.style.borderColor='#94a3b8'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#cbd5e1'">
                    <input type="radio" name="import_mode" value="insert_only" style="margin-top: 4px; accent-color: #0ea5e9;">
                    <div>
                        <div style="font-weight: 700; color: #334155; font-size: 0.92rem;">Hanya Tambah Baris Baru</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; line-height: 1.4;">
                            Hanya menambahkan SKU yang belum terdaftar. SKU yang sudah ada di database akan otomatis dilewati (skip).
                        </div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- File Uploader Area -->
        <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 36px 20px; text-align: center; margin-bottom: 24px; transition: border-color 0.2s;">
            <i class="fas fa-file-csv" style="font-size: 3rem; color: #0284c7; margin-bottom: 12px; display: block;"></i>
            <h4 style="margin: 0 0 6px 0; color: #1e293b; font-size: 1.05rem; font-weight: 700;">Pilih File CSV yang Akan Di-import</h4>
            <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 16px 0;">Pastikan format ekstensi file adalah <b>.csv</b> (Ukuran maksimal 5MB)</p>
            
            <input type="file" name="file" accept=".csv,text/csv" required style="width: 100%; max-width: 350px; margin: 0 auto; display: block; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background: white; font-size: 0.85rem;">
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.products.index') }}" style="color: #64748b; font-weight: 600; text-decoration: none; padding: 10px 20px;">Batal</a>
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 28px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);">
                <i class="fas fa-cloud-upload-alt"></i> Proses Import Sekarang
            </button>
        </div>
    </form>
</div>
@endsection
