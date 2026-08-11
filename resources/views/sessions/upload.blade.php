@extends('layouts.app')

@section('title', 'Upload Data Sesi - ' . $session->session_id)
@section('page_title', 'Upload Data Opname')

@section('page_actions')
<a href="{{ route('sessions.index') }}" class="btn-action" style="padding: 8px 12px; background: #f8fafc; border: 1px solid var(--border); color: var(--text-secondary); text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 500;">
    <i class="fas fa-arrow-left" style="margin-right: 6px;"></i> Kembali
</a>
<div class="badge-status">
    <span>Sesi: {{ $session->session_id }}</span>
</div>
@endsection

@section('content')

<div class="upload-grid">
    <!-- Kolom Kiri: Panduan & Pembimbing Upload (Guidance) -->
    <div class="content-panel guide-panel">
        <div class="panel-header-simple">
            <h3 class="panel-title-simple">
                <i class="fas fa-info-circle text-primary"></i>
                Panduan Upload Data Opname
            </h3>
            <p class="panel-subtitle">Ikuti langkah-langkah di bawah ini untuk mengunggah berkas Excel hasil perhitungan fisik.</p>
        </div>

        <div class="step-list">
            <!-- Langkah 1 -->
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4 class="step-title">Siapkan Berkas Excel / CSV</h4>
                    <p class="step-desc">Berkas harus memiliki baris pertama sebagai **Header (Nama Kolom)** dan menggunakan format ekstensi `.xlsx`, `.xls`, atau `.csv`.</p>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4 class="step-title">Gunakan Nama Kolom yang Sesuai</h4>
                    <p class="step-desc">Sistem menggunakan pembaca pintar (Smart Column Matcher) yang mendeteksi kolom secara otomatis. Pastikan kolom berikut ada di berkas Anda:</p>
                    <table class="guide-table">
                        <thead>
                            <tr>
                                <th>Panduan Header Kolom</th>
                                <th>Contoh Nama Kolom</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>no_urut</strong></td>
                                <td>No Urut, Urut, No</td>
                            </tr>
                            <tr>
                                <td><strong>id_product</strong> <span class="required-tag">*</span></td>
                                <td>ID Produk, ID Product, Kode Produk</td>
                            </tr>
                            <tr>
                                <td><strong>stock_system</strong> <span class="required-tag">*</span></td>
                                <td>Stok Sistem, Stock, Qty System</td>
                            </tr>
                            <tr>
                                <td><strong>harga</strong></td>
                                <td>Harga, Price</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4 class="step-title">Perhatikan Aturan Sesi</h4>
                    <p class="step-desc">
                        Sesi ini dikonfigurasi untuk Gudang **{{ $session->warehouse_code }}** dengan lokasi bin default **{{ $session->bin_location }}**. 
                        Pastikan produk yang diunggah memang berada di area tersebut.
                    </p>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4 class="step-title">Seret File & Unggah</h4>
                    <p class="step-desc">Letakkan berkas Anda di area dropzone sebelah kanan. Anda dapat memantau **Preview Data** terlebih dahulu untuk mengecek kebenaran isi berkas sebelum menekan tombol **Upload Data**.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Drop Zone & Preview Data -->
    <div class="content-panel main-upload-panel">
        <div class="panel-header-simple">
            <h3 class="panel-title-simple">
                <i class="fas fa-cloud-upload-alt text-primary"></i>
                Area Upload Berkas
            </h3>
        </div>

        <!-- Drop Zone -->
        <div class="drop-zone" id="dropZone" style="margin-top: 15px;">
            <i class="fas fa-cloud-upload-alt drop-icon"></i>
            <p class="drop-text">Pilih atau Seret file Excel (.xlsx, .xls, .csv) di sini</p>
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

        <!-- Alert Message -->
        <div class="modal-alert" id="modalAlert"></div>

        <!-- Preview Data (Hidden by default) -->
        <div class="preview-container" id="previewContainer">
            <h4 class="preview-title">Preview Data (Maksimal 5 Baris Pertama)</h4>
            <div class="table-scroll">
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>No Urut</th>
                            <th>ID Produk</th>
                            <th class="text-right">Stok Sistem</th>
                            <th class="text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody">
                        <!-- Injected by JS -->
                    </tbody>
                </table>
            </div>
            <div class="row-count-summary" id="rowCountSummary">Total data: 0 baris terdeteksi</div>
            
            <div class="upload-actions">
                <button class="btn-submit-upload" id="btnSubmitUpload">
                    <i class="fas fa-upload"></i>
                    <span>Simpan & Mulai Sesi Opname</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1a6d9f;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    
    .btn-back:hover {
        color: #0f2b3b;
    }
    
    .back-nav {
        display: flex;
        align-items: center;
    }

    .upload-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
        margin-top: 20px;
    }

    @media (max-width: 1024px) {
        .upload-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Guidance Panel styling */
    .guide-panel {
        padding: 24px;
    }

    .panel-header-simple {
        margin-bottom: 20px;
    }

    .panel-title-simple {
        font-size: 1.15rem;
        font-weight: 600;
        color: #0f2b3b;
        margin: 0 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-title-simple i.text-primary {
        color: #1a6d9f;
    }

    .panel-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }

    .step-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .step-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e9f0f8;
        color: #1a6d9f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 4px 0;
    }

    .step-desc {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    .guide-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 0.75rem;
    }

    .guide-table th {
        background: #f8fafc;
        color: #64748b;
        padding: 6px 10px;
        font-weight: 600;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .guide-table td {
        padding: 6px 10px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .required-tag {
        color: #ef4444;
        font-weight: bold;
    }

    /* Upload Area Styling */
    .main-upload-panel {
        padding: 24px;
        display: flex;
        flex-direction: column;
    }

    /* Drop Zone Styles */
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 48px 20px;
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
        display: none;
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

    /* Alert styles */
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

    /* Preview Data */
    .preview-container {
        display: none;
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

    .upload-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .btn-submit-upload {
        background: #107c41;
        color: white;
        border: none;
        padding: 12px 24px;
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

    .btn-submit-upload:hover:not(:disabled) {
        background: #0d6233;
        transform: translateY(-1px);
    }

    .btn-submit-upload:disabled {
        background: #cbd5e1;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }

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
    
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<!-- Load SheetJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        let parsedRecords = []; // Stores records parsed from Excel

        // Trigger file input dialog
        btnSelectFile.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);

        // Drag and Drop
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

        // Reset file input
        btnRemoveFile.addEventListener('click', resetPageState);

        // Handle file reading
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;

            fileName.textContent = file.name;
            fileSize.textContent = `(${formatBytes(file.size)})`;
            dropZone.style.display = 'none';
            fileInfo.style.display = 'flex';
            showAlert('', '');

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    
                    if (json.length < 2) {
                        throw new Error("File Excel kosong atau tidak memiliki baris data (header baris pertama diperlukan).");
                    }
                    
                    parseExcelData(json);
                } catch (error) {
                    console.error(error);
                    showAlert(error.message || "Gagal membaca file Excel. Pastikan format file sesuai.", "error");
                    resetPageState(false);
                }
            };
            
            reader.readAsArrayBuffer(file);
        }

        // Column Mapping and Normalization
        const headerMap = {
            'no_urut': ['no_urut', 'no urut', 'urut', 'no', 'nomor'],
            'id_product': ['id_product', 'id produk', 'id product', 'id', 'kode produk', 'kode_produk'],
            'stock_system': ['stock_system', 'stok sistem', 'stock', 'stok', 'qty_system'],
            'harga': ['harga', 'price', 'harga satuan']
        };

        function mapHeaders(headers) {
            const indices = {};
            headers.forEach((header, index) => {
                if (header === null || header === undefined) return;
                const normalized = header.toString().toLowerCase().trim();
                for (const [key, aliases] of Object.entries(headerMap)) {
                    if (aliases.includes(normalized)) {
                        indices[key] = index;
                        break;
                    }
                }
            });
            return indices;
        }

        function parseNumberSafe(val) {
            if (typeof val === 'number') return val;
            if (val === null || val === undefined || val === '') return 0;
            let str = val.toString().trim();
            
            if (str.includes('.') && str.includes(',')) {
                if (str.lastIndexOf(',') > str.lastIndexOf('.')) {
                    str = str.replace(/\./g, '').replace(',', '.'); // 1.200,50 -> 1200.50
                } else {
                    str = str.replace(/,/g, ''); // 1,200.50 -> 1200.50
                }
            } else if (str.includes(',')) {
                const parts = str.split(',');
                if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                    str = str.replace(/,/g, ''); // 1,200 -> 1200
                } else {
                    str = str.replace(',', '.'); // 1,5 -> 1.5
                }
            } else if (str.includes('.')) {
                const parts = str.split('.');
                if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                    str = str.replace(/\./g, ''); // 1.200 -> 1200
                }
            }
            
            str = str.replace(/[^0-9.-]/g, '');
            const num = parseFloat(str);
            return isNaN(num) ? 0 : num;
        }

        function parseExcelData(rows) {
            const headers = rows[0];
            const indices = mapHeaders(headers);

            // Validation: id_product and stock_system are strictly required
            if (indices['id_product'] === undefined) {
                throw new Error("Kolom kunci utama 'id_product' atau 'ID Produk' tidak ditemukan dalam file Excel.");
            }
            if (indices['stock_system'] === undefined) {
                throw new Error("Kolom 'stock_system' atau 'Stok Sistem' tidak ditemukan dalam file Excel.");
            }

            parsedRecords = [];
            previewBody.innerHTML = '';
            
            const dataRows = rows.slice(1);
            let validRowsCount = 0;

            dataRows.forEach((row) => {
                if (row.length === 0 || row.every(cell => cell === null || cell === undefined || cell === '')) {
                    return;
                }

                const id_product = row[indices['id_product']];
                const rawStock = row[indices['stock_system']];
                
                if (!id_product || rawStock === undefined || rawStock === null || rawStock === '') return;
                
                const stock_system = parseNumberSafe(rawStock);

                const no_urut = indices['no_urut'] !== undefined ? parseInt(row[indices['no_urut']]) || 0 : 0;
                const harga = indices['harga'] !== undefined ? parseNumberSafe(row[indices['harga']]) : 0;
                
                // is_manual defaults to 0 since it is not uploaded via Excel
                const is_manual = 0;

                const recordObj = {
                    no_urut: no_urut,
                    id_product: id_product.toString().trim(),
                    stock_system: stock_system,
                    harga: harga,
                    is_manual: is_manual
                };

                parsedRecords.push(recordObj);
                validRowsCount++;

                // Inject first 5 rows for preview
                if (validRowsCount <= 5) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `   
                        <td>${recordObj.no_urut}</td>
                        <td class="font-mono text-id" style="color: #1a6d9f; font-weight:600;">${escapeHtml(recordObj.id_product)}</td>
                        <td class="text-right" style="font-weight:700; color: #0f2b3b;">${recordObj.stock_system}</td>
                        <td class="text-right">${recordObj.harga}</td>
                    `;
                    previewBody.appendChild(tr);
                }
            });

            if (parsedRecords.length === 0) {
                throw new Error("Tidak ada data opname valid yang ditemukan dalam file (baris memerlukan ID Produk dan Stok Sistem).");
            }

            previewContainer.style.display = 'block';
            rowCountSummary.textContent = `Total data: ${parsedRecords.length} baris terdeteksi`;
            btnSubmitUpload.disabled = false;
        }

        // Post parsing upload submission via Ajax
        btnSubmitUpload.addEventListener('click', function() {
            if (parsedRecords.length === 0) return;

            btnSubmitUpload.disabled = true;
            const originalContent = btnSubmitUpload.innerHTML;
            btnSubmitUpload.innerHTML = `<span class="spinner"></span> <span>Menyimpan ke Database...</span>`;
            showAlert('', '');

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('sessions.upload.submit', $session->session_id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    records: parsedRecords
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, "success");
                    btnSubmitUpload.innerHTML = `<i class="fas fa-check"></i> <span>Berhasil!</span>`;
                    
                    // Redirect back to session list after 1.5s
                    setTimeout(() => {
                        window.location.href = "{{ route('sessions.index') }}";
                    }, 1500);
                } else {
                    if (data.missing_products && data.missing_products.length > 0) {
                        let msg = data.message + "<br><br><strong>ID Produk yang belum ada:</strong><br>";
                        msg += "<ul style='margin-top: 8px; padding-left: 20px; max-height: 150px; overflow-y: auto;'>";
                        data.missing_products.forEach(id => {
                            msg += `<li>${escapeHtml(id)}</li>`;
                        });
                        msg += "</ul>";
                        
                        showAlert(msg, "error");
                        btnSubmitUpload.disabled = false;
                        btnSubmitUpload.innerHTML = originalContent;
                    } else {
                        throw new Error(data.message || "Gagal mengupload data opname.");
                    }
                }
            })
            .catch(error => {
                console.error(error);
                showAlert(error.message || "Terjadi kesalahan saat mengunggah data ke server.", "error");
                btnSubmitUpload.disabled = false;
                btnSubmitUpload.innerHTML = originalContent;
            });
        });

        // Reset page state
        function resetPageState(resetInput = true) {
            if (resetInput) {
                fileInput.value = '';
            }
            dropZone.style.display = 'block';
            fileInfo.style.display = 'none';
            previewContainer.style.display = 'none';
            previewBody.innerHTML = '';
            btnSubmitUpload.disabled = true;
            parsedRecords = [];
        }

        function showAlert(message, type) {
            modalAlert.className = 'modal-alert';
            modalAlert.style.display = 'none';
            modalAlert.innerHTML = '';
            
            if (message && type) {
                modalAlert.innerHTML = message;
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
    });
</script>
@endpush
