@extends('layouts.app')

@section('title', 'Upload BA Pemeriksaan')
@section('page_title', 'Upload BA Pemeriksaan')

@section('page_actions')
<a href="{{ route('ba_pemeriksaan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #475569; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;">
    <i class="fas fa-arrow-left"></i>
    <span>Kembali</span>
</a>
@endsection

@section('content')
<div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8); margin-top: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">Upload Excel Sesi #{{ $header->id_ba }}</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Periode: <strong>{{ $header->periode }}</strong> &bull; PIC: <strong>{{ $header->pic_pemeriksaan }}</strong></p>
        </div>
        <span style="background: #fef9c3; color: #854d0e; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Status: Draft</span>
    </div>

    <!-- Drop Zone -->
    <div class="drop-zone" id="dropZone" style="border: 2px dashed #cbd5e1; border-radius: 16px; padding: 48px 20px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.25s;">
        <i class="fas fa-cloud-upload-alt drop-icon" style="font-size: 3rem; color: #94a3b8; margin-bottom: 16px;"></i>
        <p class="drop-text" style="font-size: 1rem; color: #64748b; margin-bottom: 20px;">Pilih atau Seret file Excel (.xlsx, .xls, .csv) ke sini</p>
        <input type="file" id="fileInput" accept=".xlsx, .xls, .csv" style="display: none;">
        <button class="btn-select-file" id="btnSelectFile" style="background: white; color: #334155; border: 1px solid #cbd5e1; padding: 10px 24px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">Pilih File Excel</button>
    </div>
    
    <!-- File Info -->
    <div class="file-info" id="fileInfo" style="display: none; align-items: center; justify-content: space-between; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 16px 20px; border-radius: 14px; margin-top: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <i class="far fa-file-excel" style="font-size: 2rem; color: #107c41;"></i>
            <div>
                <span id="fileName" style="font-size: 1rem; font-weight: 600; color: #065f46; display: block;">file.xlsx</span>
                <span id="fileSize" style="font-size: 0.8rem; color: #047857;">(0 KB)</span>
            </div>
        </div>
        <button id="btnRemoveFile" style="background: none; border: none; font-size: 1.5rem; color: #065f46; cursor: pointer; opacity: 0.6;">&times;</button>
    </div>

    <!-- Preview Data -->
    <div id="previewContainer" style="display: none; margin-top: 32px;">
        <h4 style="font-size: 1rem; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Preview Data (Maksimal 5 Baris)</h4>
        <div style="width: 100%; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="background: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Cabang</th>
                        <th style="background: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Invoice</th>
                        <th style="background: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Pelanggan</th>
                        <th style="background: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Tgl Faktur</th>
                        <th style="background: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Nilai Invoice</th>
                    </tr>
                </thead>
                <tbody id="previewBody"></tbody>
            </table>
        </div>
        <div id="rowCountSummary" style="font-size: 0.85rem; color: #64748b; margin-top: 12px; font-weight: 500; text-align: right;">Total data: 0 baris terdeteksi</div>

        <div style="margin-top: 24px; text-align: right;">
            <button id="btnSubmitUpload" style="background: #107c41; color: white; border: none; padding: 12px 28px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(16, 124, 65, 0.15);">
                <i class="fas fa-upload"></i> Proses Upload & Simpan
            </button>
        </div>
    </div>

    <!-- Alert -->
    <div id="modalAlert" style="display: none; padding: 16px; border-radius: 12px; font-size: 0.9rem; margin-top: 20px;"></div>

</div>
@endsection

@push('scripts')
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
        
        let parsedRecords = [];

        btnSelectFile.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);
        
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#1a6d9f'; dropZone.style.background = '#ecf3fa'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#cbd5e1'; dropZone.style.background = '#f8fafc'; });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#cbd5e1'; dropZone.style.background = '#f8fafc';
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        btnRemoveFile.addEventListener('click', () => {
            fileInput.value = '';
            parsedRecords = [];
            dropZone.style.display = 'block';
            fileInfo.style.display = 'none';
            previewContainer.style.display = 'none';
            showAlert('', '');
        });

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
                    const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                    const rawJson = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    
                    if (rawJson.length < 2) throw new Error('File Excel kosong atau tidak memiliki header.');
                    
                    const headers = rawJson[0].map(h => String(h).trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, ''));
                    
                    parsedRecords = [];
                    for (let i = 1; i < rawJson.length; i++) {
                        const row = rawJson[i];
                        if (row.length === 0 || !row.some(cell => cell !== undefined && cell !== '')) continue;
                        
                        const record = {};
                        headers.forEach((header, index) => {
                            record[header] = row[index] !== undefined ? row[index] : null;
                        });
                        parsedRecords.push(record);
                    }
                    
                    if (parsedRecords.length === 0) throw new Error('Tidak ada data yang valid untuk di-import.');
                    
                    renderPreview(parsedRecords);
                    
                } catch (error) {
                    showAlert('error', 'Gagal memproses file: ' + error.message);
                    btnRemoveFile.click();
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function renderPreview(data) {
            previewBody.innerHTML = '';
            const limit = Math.min(data.length, 5);
            for (let i = 0; i < limit; i++) {
                const row = data[i];
                previewBody.innerHTML += `
                    <tr>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9;">${row.cabang || '-'}</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9;">${row.invoice || '-'}</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9;">${row.pelanggan || '-'}</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9;">${row.tgl_faktur || '-'}</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9;">${row.nilai_invoice || '-'}</td>
                    </tr>
                `;
            }
            rowCountSummary.textContent = `Total data: ${data.length} baris siap di-import`;
            previewContainer.style.display = 'block';
        }

        btnSubmitUpload.addEventListener('click', function() {
            if (parsedRecords.length === 0) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = 'Memproses...';
            this.disabled = true;
            
            fetch('{{ route("ba_pemeriksaan.import", $header->id_ba) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ records: parsedRecords })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => window.location.href = '{{ route("ba_pemeriksaan.detail", $header->id_ba) }}', 1500);
                } else {
                    showAlert('error', data.message || 'Terjadi kesalahan saat import data.');
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan jaringan atau server.');
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });

        function showAlert(type, message) {
            if (!type) { modalAlert.style.display = 'none'; return; }
            modalAlert.style.display = 'block';
            modalAlert.style.background = type === 'error' ? '#fef2f2' : '#ecfdf5';
            modalAlert.style.color = type === 'error' ? '#991b1b' : '#065f46';
            modalAlert.style.border = `1px solid ${type === 'error' ? '#fca5a5' : '#6ee7b7'}`;
            modalAlert.innerHTML = message;
        }

        function formatBytes(bytes) {
            if (!+bytes) return '0 Bytes';
            const k = 1024, i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${['Bytes', 'KB', 'MB', 'GB'][i]}`;
        }
    });
</script>
@endpush
