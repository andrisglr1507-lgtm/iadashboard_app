@extends('layouts.app')

@section('title', 'Master Product')
@section('page_title', 'Master Product')

@section('page_actions')
<button class="btn-import-trigger" id="btnImportTrigger" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
    <i class="fas fa-file-excel"></i>
    <span>Import Excel</span>
</button>
<div class="badge-status">
    <span>{{ count($principals) }} Principals</span>
</div>
@endsection

@section('content')

<div class="content-panel table-panel" style="margin-top: 20px; padding: 24px 0 0 0; overflow: hidden;">
    <div class="panel-header" style="border-bottom: none; padding-bottom: 8px;">
        <h2 class="panel-title">Daftar Principal</h2>
    </div>

    <div class="table-container">
        <table class="premium-table" id="principalsTable">
            <thead>
                <tr>
                    <th>Nama Principal</th>
                    <th class="text-center" style="width: 180px;">Total Produk</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($principals as $p)
                    <tr>
                        <td class="font-weight-medium">
                            <i class="fas fa-industry text-muted" style="margin-right: 10px; color: #94a3b8;"></i>
                            {{ $p->principal ?: 'Tanpa Principal' }}
                        </td>
                        <td class="text-center font-weight-bold" style="color: #1a6d9f; font-size: 1.1rem; font-weight: 700;">
                            {{ number_format($p->total_products) }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('master.products.show', $p->principal ?: '-') }}" class="btn-detail-table">
                                <span>Detail</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4" style="color: #8ba0b5;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <p>Tidak ada data principal ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal-overlay" id="importModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-file-excel" style="color: #107c41; margin-right: 8px;"></i>
                Import Product dari Excel
            </h3>
            <button class="modal-close" id="btnModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Drop Zone -->
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-cloud-upload-alt drop-icon"></i>
                <p class="drop-text">Pilih atau Seret file Excel (.xlsx, .xls, .csv) ke sini</p>
                <input type="file" id="fileInput" accept=".xlsx, .xls, .csv" style="display: none;">
                <button class="btn-select-file" id="btnSelectFile">Pilih File</button>
            </div>
            
            <!-- File Info (Hidden by default) -->
            <div class="file-info" id="fileInfo">
                <div class="file-info-details">
                    <i class="far fa-file-excel excel-file-icon"></i>
                    <div>
                        <span class="file-name" id="fileName">file.xlsx</span>
                        <span class="file-size" id="fileSize">(0 KB)</span>
                    </div>
                </div>
                <button class="btn-remove-file" id="btnRemoveFile" title="Hapus file">&times;</button>
            </div>

            <!-- Preview Data (Hidden by default) -->
            <div class="preview-container" id="previewContainer">
                <h4 class="preview-title">Preview Data (Maksimal 5 Baris Pertama)</h4>
                <div class="table-scroll">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>ID Produk</th>
                                <th>Nama Produk</th>
                                <th>Principal</th>
                                <th>Barcode</th>
                                <th>Kode Karton</th>
                                <th>Pack Name</th>
                                <th>UOM</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="row-count-summary" id="rowCountSummary">Total data: 0 baris terdeteksi</div>
            </div>

            <!-- Alert Message -->
            <div class="modal-alert" id="modalAlert"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" id="btnCancelImport">Batal</button>
            <button class="btn-upload" id="btnSubmitUpload" disabled>
                <i class="fas fa-upload"></i>
                <span>Upload Data</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Load DataTables Default CSS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* Top Bar Action */
    .top-bar-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .btn-import-trigger {
        background: #107c41;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(16, 124, 65, 0.15);
    }
    
    .btn-import-trigger:hover {
        background: #0d6233;
        transform: translateY(-1px);
    }
    
    /* Table styles */
    .table-panel {
        padding: 24px 0 0 0;
        overflow: hidden;
    }
    
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px 20px 24px;
        border-bottom: 1px solid #eff3f8;
    }
    
    .panel-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0f2b3b;
        margin: 0;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    
    .premium-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    
    .premium-table th {
        background: #f8fafc;
        padding: 14px 24px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #8ba0b5;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #eff3f8;
    }
    
    .premium-table td {
        padding: 16px 24px;
        font-size: 0.9rem;
        color: #2c3e4e;
        border-bottom: 1px solid #eff3f8;
        vertical-align: middle;
    }
    
    .premium-table tbody tr {
        transition: background 0.15s;
    }
    
    .premium-table tbody tr:hover {
        background: #f8fafc;
    }

    .btn-detail-table {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #1a6d9f;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        background: #e9f0f8;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .btn-detail-table:hover {
        background: #1a6d9f;
        color: white;
    }
    
    .btn-detail-table i {
        transition: transform 0.2s;
    }
    
    .btn-detail-table:hover i {
        transform: translateX(3px);
    }
    
    .font-weight-bold {
        font-weight: 700;
    }
    
    .font-weight-medium {
        font-weight: 500;
    }
    
    .text-muted {
        color: #8ba0b5;
    }

    /* ==========================================
       DATATABLES PREMIUM STYLE OVERRIDES
       ========================================== */
    .dataTables_wrapper {
        position: relative;
        padding-top: 10px;
    }

    /* Controls Top Bar */
    .dataTables_length {
        float: left;
        padding: 0 24px 20px 24px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .dataTables_length select {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        outline: none !important;
        color: #334155;
        font-weight: 600;
        margin: 0 6px !important;
        background: white;
    }

    .dataTables_filter {
        float: right;
        padding: 0 24px 20px 24px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dataTables_filter input {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 7px 14px !important;
        outline: none !important;
        color: #334155;
        font-size: 0.85rem !important;
        width: 240px !important;
        transition: all 0.2s !important;
        margin-left: 0 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
    }

    .dataTables_filter input:focus {
        border-color: #1a6d9f !important;
        box-shadow: 0 0 0 3px rgba(26,109,159,0.12) !important;
    }

    /* Table borders resetting */
    table.dataTable.no-footer {
        border-bottom: 1px solid #eff3f8 !important;
    }

    table.dataTable th {
        border-bottom: 1px solid #eff3f8 !important;
    }

    /* Info Label Bottom */
    .dataTables_info {
        float: left;
        padding: 24px !important;
        font-size: 0.85rem !important;
        color: #64748b !important;
        font-weight: 500 !important;
    }

    /* Pagination Control Bottom */
    .dataTables_paginate {
        float: right;
        padding: 18px 24px !important;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pagination Buttons overrides */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important;
        background: transparent !important;
        color: #64748b !important;
        padding: 6px 12px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        cursor: pointer !important;
        transition: all 0.15s !important;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #e9f0f8 !important;
        color: #1a6d9f !important;
        border-color: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #f1f5f9 !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
        color: #cbd5e1 !important;
        cursor: not-allowed !important;
        background: transparent !important;
        border-color: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button i {
        font-size: 0.8rem;
    }

    /* Modal Overlay Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 43, 59, 0.4);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    
    .modal-overlay.open {
        opacity: 1;
        pointer-events: auto;
    }
    
    .modal-card {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 720px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transform: translateY(20px);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        margin: 20px;
    }
    
    .modal-overlay.open .modal-card {
        transform: translateY(0);
    }
    
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #0f2b3b;
        margin: 0;
        display: flex;
        align-items: center;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.6rem;
        color: #94a3b8;
        cursor: pointer;
        transition: color 0.15s;
        line-height: 1;
    }
    
    .modal-close:hover {
        color: #1e293b;
    }
    
    .modal-body {
        padding: 24px;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    /* Drop Zone Styles */
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 32px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: #f8fafc;
    }
    
    .drop-zone.hover {
        border-color: #1a6d9f;
        background: #ecf3fa;
    }
    
    .drop-icon {
        font-size: 2.5rem;
        color: #94a3b8;
        margin-bottom: 12px;
        transition: color 0.2s;
    }
    
    .drop-zone.hover .drop-icon {
        color: #1a6d9f;
    }
    
    .drop-text {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 16px;
    }
    
    .btn-select-file {
        background: white;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.15s;
    }
    
    .btn-select-file:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    
    /* File Info Styles */
    .file-info {
        display: none; /* Injected dynamically */
        align-items: center;
        justify-content: space-between;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 12px 16px;
        border-radius: 14px;
        margin-top: 16px;
    }
    
    .file-info-details {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .excel-file-icon {
        font-size: 1.8rem;
        color: #107c41;
    }
    
    .file-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #065f46;
        display: block;
    }
    
    .file-size {
        font-size: 0.75rem;
        color: #047857;
    }
    
    .btn-remove-file {
        background: none;
        border: none;
        font-size: 1.4rem;
        color: #065f46;
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.15s;
    }
    
    .btn-remove-file:hover {
        opacity: 1;
    }
    
    /* Preview Table Styles */
    .preview-container {
        display: none; /* Injected dynamically */
        margin-top: 24px;
    }
    
    .preview-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 12px 0;
    }
    
    .table-scroll {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    
    .preview-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.8rem;
    }
    
    .preview-table th {
        background: #f8fafc;
        padding: 10px 14px;
        color: #64748b;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .preview-table td {
        padding: 10px 14px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    
    .row-count-summary {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 10px;
        font-weight: 500;
        text-align: right;
    }
    
    /* Alert inside modal */
    .modal-alert {
        display: none;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.85rem;
        margin-top: 16px;
        line-height: 1.4;
    }
    
    .modal-alert.error {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        display: block;
    }
    
    .modal-alert.success {
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        display: block;
    }
    
    /* Footer Modal */
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .btn-cancel {
        background: white;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .btn-cancel:hover {
        background: #f8fafc;
        color: #1e293b;
    }
    
    .btn-upload {
        background: #107c41;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(16, 124, 65, 0.15);
    }
    
    .btn-upload:hover:not(:disabled) {
        background: #0d6233;
        transform: translateY(-1px);
    }
    
    .btn-upload:disabled {
        background: #cbd5e1;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }
    
    /* Loading Spinner */
    .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<!-- Load jQuery and DataTables JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Load SheetJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('importModal');
        const btnOpen = document.getElementById('btnImportTrigger');
        const btnClose = document.getElementById('btnModalClose');
        const btnCancel = document.getElementById('btnCancelImport');
        
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const btnSelectFile = document.getElementById('btnSelectFile');
        
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const btnRemoveFile = document.getElementById('btnRemoveFile');
        
        const previewContainer = document.getElementById('previewContainer');
        const previewBody = document.getElementById('previewBody');
        const rowCountSummary = document.getElementById('rowCountSummary');
        
        const btnSubmitUpload = document.getElementById('btnSubmitUpload');
        const modalAlert = document.getElementById('modalAlert');
        
        let parsedProducts = []; // Array to store products parsed from Excel

        // 1. Modal Open/Close functionality
        function openModal() {
            modal.classList.add('open');
            resetModalState();
        }
        
        function closeModal() {
            modal.classList.remove('open');
        }
        
        btnOpen.addEventListener('click', openModal);
        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        
        // Close modal when clicking outside card
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // 2. Select File triggers
        btnSelectFile.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);
        
        // 3. Drag and Drop events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('hover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('hover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('hover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        // 4. Remove File
        btnRemoveFile.addEventListener('click', resetModalState);

        // 5. Handle File Selection and Parsing
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;

            // Show file info
            fileName.textContent = file.name;
            fileSize.textContent = `(${formatBytes(file.size)})`;
            dropZone.style.display = 'none';
            fileInfo.style.display = 'flex';

            showAlert('', ''); // Clear previous alerts

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    
                    // Assume first sheet
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    
                    // Convert to JSON
                    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    
                    if (json.length < 2) {
                        throw new Error("File Excel kosong atau tidak memiliki baris data (header baris pertama diperlukan).");
                    }
                    
                    parseExcelData(json);
                } catch (error) {
                    console.error(error);
                    showAlert(error.message || "Gagal membaca file Excel. Pastikan format file sesuai.", "error");
                    resetModalState(false);
                }
            };
            
            reader.readAsArrayBuffer(file);
        }

        // 6. Column Mapping and Normalization
        const headerMap = {
            'id_product': ['id_product', 'id produk', 'id product', 'id', 'kode produk', 'kode_produk'],
            'product_name': ['product_name', 'nama produk', 'product name', 'nama_produk', 'nama', 'nama barang'],
            'principal': ['principal', 'prinsipal', 'pabrikan', 'pabrik', 'brand', 'vendor'],
            'barcode': ['barcode', 'kode barcode', 'bar code'],
            'carton_code': ['carton_code', 'kode karton', 'carton code', 'kode_karton'],
            'packname': ['packname', 'pack name', 'kemasan', 'nama_kemasan'],
            'uom': ['uom', 'satuan', 'isi_karton', 'isi', 'qty'],
            'is_active': ['is_active', 'status', 'is active', 'aktif', 'active', 'keaktifan']
        };

        function mapHeaders(headers) {
            const indices = {};
            
            headers.forEach((header, index) => {
                if (header === null || header === undefined) return;
                const normalized = header.toString().toLowerCase().trim();
                
                // Check if matches mapping
                for (const [key, aliases] of Object.entries(headerMap)) {
                    if (aliases.includes(normalized)) {
                        indices[key] = index;
                        break;
                    }
                }
            });
            
            return indices;
        }

        function parseExcelData(rows) {
            const headers = rows[0];
            const mappedIndices = mapHeaders(headers);

            // Validation: id_product and product_name are absolutely required
            if (mappedIndices['id_product'] === undefined) {
                throw new Error("Kolom kunci utama 'id_product' atau 'ID Produk' tidak ditemukan dalam file Excel.");
            }
            if (mappedIndices['product_name'] === undefined) {
                throw new Error("Kolom 'product_name' atau 'Nama Produk' tidak ditemukan dalam file Excel.");
            }

            parsedProducts = [];
            previewBody.innerHTML = '';

            const dataRows = rows.slice(1);
            let validRowsCount = 0;

            dataRows.forEach((row, rowIndex) => {
                // Skip completely empty rows
                if (row.length === 0 || row.every(cell => cell === null || cell === undefined || cell === '')) {
                    return;
                }

                const id_product = row[mappedIndices['id_product']];
                const product_name = row[mappedIndices['product_name']];

                // Skip if main fields are missing
                if (!id_product || !product_name) return;

                const principal = mappedIndices['principal'] !== undefined ? row[mappedIndices['principal']] : null;
                const barcode = mappedIndices['barcode'] !== undefined ? row[mappedIndices['barcode']] : null;
                const carton_code = mappedIndices['carton_code'] !== undefined ? row[mappedIndices['carton_code']] : null;
                const packname = mappedIndices['packname'] !== undefined ? row[mappedIndices['packname']] : null;
                
                let uom = null;
                if (mappedIndices['uom'] !== undefined) {
                    const parsedUom = parseInt(row[mappedIndices['uom']]);
                    uom = !isNaN(parsedUom) ? parsedUom : null;
                }

                let is_active = true;
                if (mappedIndices['is_active'] !== undefined) {
                    const rawActive = row[mappedIndices['is_active']];
                    if (rawActive !== null && rawActive !== undefined) {
                        const str = rawActive.toString().toLowerCase().trim();
                        if (['no', 'n', '0', 'false', 'inactive', 'non-aktif', 'nonaktif', 'tidak', 'tidak aktif'].includes(str)) {
                            is_active = false;
                        }
                    }
                }

                const productObj = {
                    id_product: id_product.toString().trim(),
                    product_name: product_name.toString().trim(),
                    principal: principal ? principal.toString().trim() : null,
                    barcode: barcode ? barcode.toString().trim() : null,
                    carton_code: carton_code ? carton_code.toString().trim() : null,
                    packname: packname ? packname.toString().trim() : null,
                    uom: uom,
                    is_active: is_active
                };

                parsedProducts.push(productObj);
                validRowsCount++;

                // Inject first 5 rows into preview table
                if (validRowsCount <= 5) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-mono" style="color: #1a6d9f; font-weight:600;">${escapeHtml(productObj.id_product)}</td>
                        <td style="font-weight:500;">${escapeHtml(productObj.product_name)}</td>
                        <td>${productObj.principal ? escapeHtml(productObj.principal) : '-'}</td>
                        <td>${productObj.barcode ? escapeHtml(productObj.barcode) : '-'}</td>
                        <td>${productObj.carton_code ? escapeHtml(productObj.carton_code) : '-'}</td>
                        <td>${productObj.packname ? escapeHtml(productObj.packname) : '-'}</td>
                        <td>${productObj.uom !== null ? productObj.uom + ' pcs' : '-'}</td>
                        <td class="text-center">
                            <span class="badge ${productObj.is_active ? 'badge-active' : 'badge-inactive'}">
                                ${productObj.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                    `;
                    previewBody.appendChild(tr);
                }
            });

            if (parsedProducts.length === 0) {
                throw new Error("Tidak ada data produk valid yang ditemukan dalam file (baris memerlukan ID Produk dan Nama Produk).");
            }

            // Show Preview container
            previewContainer.style.display = 'block';
            rowCountSummary.textContent = `Total data: ${parsedProducts.length} baris terdeteksi`;
            btnSubmitUpload.disabled = false;
        }

        // 7. Submit / Upload Data to Backend
        btnSubmitUpload.addEventListener('click', function() {
            if (parsedProducts.length === 0) return;

            // Set loading state
            btnSubmitUpload.disabled = true;
            const originalText = btnSubmitUpload.innerHTML;
            btnSubmitUpload.innerHTML = `<span class="spinner"></span> <span>Mengunggah...</span>`;
            showAlert('', '');

            // CSRF Token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('master.products.import') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    products: parsedProducts
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, "success");
                    btnSubmitUpload.innerHTML = `<i class="fas fa-check"></i> <span>Berhasil!</span>`;
                    
                    // Reload index page after 1.5s to see update principal lists
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || "Gagal mengupload data.");
                }
            })
            .catch(error => {
                console.error(error);
                showAlert(error.message || "Terjadi kesalahan saat mengunggah data ke server.", "error");
                btnSubmitUpload.disabled = false;
                btnSubmitUpload.innerHTML = originalText;
            });
        });

        // 8. Helper Functions
        function resetModalState(resetInput = true) {
            if (resetInput) {
                fileInput.value = '';
            }
            dropZone.style.display = 'block';
            fileInfo.style.display = 'none';
            previewContainer.style.display = 'none';
            previewBody.innerHTML = '';
            btnSubmitUpload.disabled = true;
            parsedProducts = [];
        }

        function showAlert(message, type) {
            modalAlert.className = 'modal-alert';
            modalAlert.style.display = 'none';
            modalAlert.textContent = '';
            
            if (message && type) {
                modalAlert.textContent = message;
                modalAlert.classList.add(type);
                modalAlert.style.display = 'block';
            }
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // DataTables Initialization for principalsTable
        if (typeof jQuery !== 'undefined' && $('#principalsTable tbody tr').length > 0 && !$('#principalsTable tbody tr td').hasClass('dataTables_empty')) {
            $('#principalsTable').DataTable({
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50],
                order: [[0, 'asc']],
                language: {
                    search: "",
                    searchPlaceholder: "Cari principal...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ principal",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 principal",
                    infoFiltered: "(difilter dari _MAX_ total principal)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    },
                    zeroRecords: "Tidak ada data principal yang cocok ditemukan."
                }
            });
        }
    });
</script>
@endpush
