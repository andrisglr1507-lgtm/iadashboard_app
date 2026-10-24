<!-- Modal Sinkronisasi Produk WMS -->
<div class="modal fade" id="syncWmsModal" tabindex="-1" aria-labelledby="syncWmsModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header" style="background: #0f172a; color: white; padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(14, 165, 233, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                        <i class="fas fa-sync-alt" id="syncHeaderIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="syncWmsModalLabel" style="font-size: 1.1rem; font-weight: 700; margin: 0; color: white;">Sinkronisasi Produk WMS API</h5>
                        <p style="margin: 0; font-size: 0.78rem; color: #94a3b8;">Bandingkan data WMS & hanya download SKU yang belum ada di database</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btnCloseModalX"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 24px;">
                <!-- State 1: Loading / Comparing -->
                <div id="syncStateLoading" style="text-align: center; padding: 36px 20px;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; margin-bottom: 16px; border-width: 0.25em;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">Memeriksa & Membandingkan Data...</h5>
                    <p style="color: #64748b; font-size: 0.85rem; max-width: 440px; margin: 0 auto;">
                        Menghubungi endpoint WMS API dan mencocokkan dengan data produk di database. Harap tunggu sebentar.
                    </p>
                </div>

                <!-- State 2: Comparison Result -->
                <div id="syncStateResult" style="display: none;">
                    <!-- Metrics Cards -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center;">
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total Produk di WMS</div>
                            <div id="metricTotalWms" style="font-size: 1.6rem; font-weight: 800; color: #0284c7; margin-top: 4px;">0</div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center;">
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Sudah di Database</div>
                            <div id="metricTotalDb" style="font-size: 1.6rem; font-weight: 800; color: #475569; margin-top: 4px;">0</div>
                        </div>
                        <div id="cardNewSku" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px; text-align: center;">
                            <div style="font-size: 0.72rem; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">SKU Baru Ditemukan</div>
                            <div id="metricNewCount" style="font-size: 1.6rem; font-weight: 800; color: #16a34a; margin-top: 4px;">0</div>
                        </div>
                    </div>

                    <!-- No New Products Banner -->
                    <div id="boxNoNewProducts" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 24px; text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 2.8rem; color: #16a34a; margin-bottom: 12px; display: block;"></i>
                        <h5 style="font-weight: 700; color: #166534; margin-bottom: 6px;">Database Sudah Up-to-Date!</h5>
                        <p style="color: #15803d; font-size: 0.88rem; margin: 0;">Semua produk yang ada di WMS API sudah terdaftar di database. Tidak ada SKU baru yang perlu diunduh.</p>
                    </div>

                    <!-- New Products Found Banner & Preview Table -->
                    <div id="boxHasNewProducts" style="display: none;">
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-info-circle" style="color: #2563eb; font-size: 1.2rem; flex-shrink: 0;"></i>
                            <div style="font-size: 0.85rem; color: #1e40af;">
                                Ditemukan <b id="textNewCountBadge">0</b> SKU baru dari WMS yang belum terdaftar di database. Klik tombol <b>Download & Simpan</b> di bawah untuk menyimpannya.
                            </div>
                        </div>

                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                            <div style="max-height: 260px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.83rem;">
                                    <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 2; border-bottom: 1px solid #e2e8f0;">
                                        <tr>
                                            <th style="padding: 10px 12px; color: #475569; font-weight: 700;">SKU Code</th>
                                            <th style="padding: 10px 12px; color: #475569; font-weight: 700;">Nama Produk</th>
                                            <th style="padding: 10px 12px; color: #475569; font-weight: 700;">Kategori & UOM</th>
                                            <th style="padding: 10px 12px; color: #475569; font-weight: 700;">Principal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableNewProductsBody">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- State 3: Error State -->
                <div id="syncStateError" style="display: none; text-align: center; padding: 28px 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2.8rem; color: #ef4444; margin-bottom: 12px; display: block;"></i>
                    <h5 style="font-weight: 700; color: #991b1b; margin-bottom: 6px;">Gagal Memeriksa Data WMS</h5>
                    <p id="syncErrorMessage" style="color: #b91c1c; font-size: 0.85rem; margin-bottom: 18px; max-width: 500px; margin-left: auto; margin-right: auto;">-</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnRetrySync" style="border-radius: 6px; font-weight: 600; padding: 6px 16px;">
                        <i class="fas fa-redo"></i> Coba Periksa Ulang
                    </button>
                </div>

                <!-- State 4: Saving in Progress -->
                <div id="syncStateSaving" style="display: none; text-align: center; padding: 36px 20px;">
                    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem; margin-bottom: 16px; border-width: 0.25em;">
                        <span class="visually-hidden">Saving...</span>
                    </div>
                    <h5 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">Menyimpan Produk Baru ke Database...</h5>
                    <p style="color: #64748b; font-size: 0.85rem; margin: 0;">Sedang mengimpor SKU baru ke tabel database. Harap tidak menutup halaman ini.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelSync" style="font-weight: 600; font-size: 0.85rem; padding: 8px 18px; border-radius: 6px;">
                    Tutup
                </button>
                <button type="button" class="btn btn-success" id="btnSaveNewProducts" style="display: none; font-weight: 600; font-size: 0.85rem; padding: 8px 22px; border-radius: 6px; background: #16a34a; border: none; align-items: center; gap: 8px;">
                    <i class="fas fa-cloud-download-alt"></i> <span id="btnSaveText">Download & Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let syncModalElement = document.getElementById('syncWmsModal');
    if (!syncModalElement) return;

    let syncModal = new bootstrap.Modal(syncModalElement);
    let newProductsCache = [];

    // Tombol trigger sync
    const triggerButtons = document.querySelectorAll('.btn-trigger-sync-wms');
    triggerButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            syncModal.show();
            startSyncCheck();
        });
    });

    // Tombol Retry
    const btnRetry = document.getElementById('btnRetrySync');
    if (btnRetry) {
        btnRetry.addEventListener('click', function() {
            startSyncCheck();
        });
    }

    // Fungsi cek & bandingkan data
    function startSyncCheck() {
        // Reset view states
        document.getElementById('syncStateLoading').style.display = 'block';
        document.getElementById('syncStateResult').style.display = 'none';
        document.getElementById('syncStateError').style.display = 'none';
        document.getElementById('syncStateSaving').style.display = 'none';
        
        document.getElementById('btnSaveNewProducts').style.display = 'none';
        document.getElementById('btnCancelSync').disabled = false;
        document.getElementById('btnCloseModalX').disabled = false;

        fetch("{{ route('sodc.products.sync_check') }}", {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('syncStateLoading').style.display = 'none';

            if (!data.success) {
                showError(data.message || 'Gagal memeriksa data produk WMS.');
                return;
            }

            // Tampilkan hasil
            document.getElementById('syncStateResult').style.display = 'block';
            document.getElementById('metricTotalWms').textContent = Number(data.total_wms).toLocaleString();
            document.getElementById('metricTotalDb').textContent = Number(data.total_db).toLocaleString();
            document.getElementById('metricNewCount').textContent = Number(data.new_count).toLocaleString();

            newProductsCache = data.new_products || [];

            if (data.new_count === 0) {
                // Tidak ada produk baru
                document.getElementById('boxNoNewProducts').style.display = 'block';
                document.getElementById('boxHasNewProducts').style.display = 'none';
                document.getElementById('btnSaveNewProducts').style.display = 'none';
                
                // Ubah kartu SKU baru jadi netral
                const cardNew = document.getElementById('cardNewSku');
                cardNew.style.background = '#f8fafc';
                cardNew.style.borderColor = '#e2e8f0';
                document.getElementById('metricNewCount').style.color = '#64748b';
            } else {
                // Ada produk baru
                document.getElementById('boxNoNewProducts').style.display = 'none';
                document.getElementById('boxHasNewProducts').style.display = 'block';
                document.getElementById('textNewCountBadge').textContent = data.new_count;

                const cardNew = document.getElementById('cardNewSku');
                cardNew.style.background = '#f0fdf4';
                cardNew.style.borderColor = '#bbf7d0';
                document.getElementById('metricNewCount').style.color = '#16a34a';

                // Render tabel preview SKU baru
                const tbody = document.getElementById('tableNewProductsBody');
                tbody.innerHTML = '';
                newProductsCache.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="padding: 8px 12px; font-weight: 700; color: #0284c7;">${item.sku_code}</td>
                        <td style="padding: 8px 12px; font-weight: 600; color: #334155;">${item.sku_name || '-'}</td>
                        <td style="padding: 8px 12px; color: #64748b;">${item.product_category_code || '-'} <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">UOM: ${item.uom}</span></td>
                        <td style="padding: 8px 12px; font-weight: 500; color: #475569;">${item.principal || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });

                // Tampilkan tombol download/simpan
                const btnSave = document.getElementById('btnSaveNewProducts');
                btnSave.style.display = 'inline-flex';
                document.getElementById('btnSaveText').textContent = `Download & Simpan (${data.new_count}) SKU Baru`;
            }
        })
        .catch(err => {
            document.getElementById('syncStateLoading').style.display = 'none';
            showError('Terjadi kesalahan jaringan atau server timeout: ' + err.message);
        });
    }

    function showError(msg) {
        document.getElementById('syncStateError').style.display = 'block';
        document.getElementById('syncErrorMessage').textContent = msg;
    }

    // Tombol Eksekusi Simpan SKU Baru
    const btnSave = document.getElementById('btnSaveNewProducts');
    if (btnSave) {
        btnSave.addEventListener('click', function() {
            if (newProductsCache.length === 0) return;

            document.getElementById('syncStateResult').style.display = 'none';
            document.getElementById('syncStateSaving').style.display = 'block';
            document.getElementById('btnSaveNewProducts').style.display = 'none';
            document.getElementById('btnCancelSync').disabled = true;
            document.getElementById('btnCloseModalX').disabled = true;

            fetch("{{ route('sodc.products.sync_save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    products: newProductsCache
                })
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: resData.message,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(135deg, #16a34a, #15803d)",
                                borderRadius: "8px",
                                fontWeight: "600"
                            }
                        }).showToast();
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    document.getElementById('syncStateSaving').style.display = 'none';
                    document.getElementById('btnCancelSync').disabled = false;
                    document.getElementById('btnCloseModalX').disabled = false;
                    showError(resData.message || 'Gagal menyimpan produk baru.');
                }
            })
            .catch(err => {
                document.getElementById('syncStateSaving').style.display = 'none';
                document.getElementById('btnCancelSync').disabled = false;
                document.getElementById('btnCloseModalX').disabled = false;
                showError('Terjadi kesalahan saat menyimpan: ' + err.message);
            });
        });
    }
});
</script>
