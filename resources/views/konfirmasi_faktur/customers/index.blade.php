@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('page_title', 'Data Pelanggan (CSAN)')

@push('styles')
<style>
    /* KPI Mini Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--phoenix-border-color, #E6E9ED);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
    }
    .kpi-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 4px;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    /* Filter Bar Styles */
    .filter-wrapper {
        background: #ffffff;
        border: 1px solid var(--phoenix-border-color, #E6E9ED);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .filter-input, .filter-select {
        padding: 8px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #ffffff;
        color: #334155;
        font-weight: 500;
        outline: none;
        transition: border-color 0.2s;
    }
    .filter-input:focus, .filter-select:focus {
        border-color: #1ABB9C;
    }

    /* Buttons */
    .btn-export-excel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #107c41 0%, #0d6334 100%);
        color: #ffffff !important;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 18px;
        border-radius: 6px;
        text-decoration: none !important;
        border: none;
        box-shadow: 0 3px 8px rgba(16, 124, 65, 0.25);
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-export-excel:hover {
        background: linear-gradient(135deg, #0d6334 0%, #0a4d28 100%);
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(16, 124, 65, 0.35);
        color: #ffffff !important;
    }

    .btn-refresh-api {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-refresh-api:hover {
        color: #1ABB9C;
        border-color: #1ABB9C;
        background: #f8fafc;
    }

    /* Coordinate Map Link */
    .map-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        transition: all 0.2s;
    }
    .map-chip:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .tel-link {
        color: #0284c7;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }
    .tel-link:hover {
        color: #0369a1;
        text-decoration: underline;
    }

    /* Badges */
    .badge-mt {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-block;
    }
    .badge-gt {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-block;
    }
    .badge-branch {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('page_actions')
<a href="{{ route('konfirmasi_faktur.customers.index', array_merge(request()->query(), ['refresh' => 1])) }}" class="btn-refresh-api" title="Ambil data terbaru langsung dari API WMS">
    <i class="fas fa-sync-alt"></i> Refresh Data API
</a>
@endsection

@section('content')

{{-- Error Message if API fails --}}
@if($errorMessage)
    <div style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.4rem;"></i>
        <div>
            <div style="font-weight: 700;">Gagal Mengambil Data dari API</div>
            <div style="font-size: 0.85rem; margin-top: 2px;">{{ $errorMessage }}</div>
        </div>
    </div>
@endif

{{-- Summary KPI Cards --}}
<div class="kpi-grid">
    <!-- Total Pelanggan -->
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Pelanggan Cabang</div>
            <div class="kpi-value">{{ number_format($metrics['total'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Cabang: <strong>{{ $branch }}</strong></div>
        </div>
        <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="ri-store-2-line"></i>
        </div>
    </div>

    <!-- Modern Trade (MT) -->
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Modern Trade (MT)</div>
            <div class="kpi-value" style="color: #059669;">{{ number_format($metrics['total_mt'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Supermarket / Minimarket</div>
        </div>
        <div class="kpi-icon" style="background: #ecfdf5; color: #10b981;">
            <i class="ri-building-line"></i>
        </div>
    </div>

    <!-- General Trade (GT) -->
    <div class="kpi-card">
        <div>
            <div class="kpi-label">General Trade (GT)</div>
            <div class="kpi-value" style="color: #d97706;">{{ number_format($metrics['total_gt'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Toko Tradisional / Retail</div>
        </div>
        <div class="kpi-icon" style="background: #fffbeb; color: #f59e0b;">
            <i class="ri-shopping-basket-2-line"></i>
        </div>
    </div>

    <!-- With Coordinates -->
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Terdata Titik GPS</div>
            <div class="kpi-value" style="color: #7c3aed;">{{ number_format($metrics['total_gps'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                @if(($metrics['total'] ?? 0) > 0)
                    {{ round((($metrics['total_gps'] ?? 0) / $metrics['total']) * 100, 1) }}% dari total
                @else
                    0%
                @endif
            </div>
        </div>
        <div class="kpi-icon" style="background: #f5f3ff; color: #8b5cf6;">
            <i class="ri-map-pin-2-line"></i>
        </div>
    </div>
</div>

{{-- Filter Panel --}}
<div class="filter-wrapper">
    <form action="{{ route('konfirmasi_faktur.customers.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        
        <!-- Filter Cabang -->
        <!-- Input Manual Kode Cabang -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Kode Cabang</label>
            <div style="position: relative; display: flex; align-items: center;">
                <input 
                    type="text" 
                    name="branch" 
                    value="{{ $branch }}" 
                    class="filter-input" 
                    placeholder="Contoh: COGBGK" 
                    style="font-weight: 700; text-transform: uppercase; width: 160px; padding-left: 32px; letter-spacing: 0.05em;" 
                    oninput="this.value = this.value.toUpperCase();"
                    required
                >
                <i class="ri-store-2-line" style="position: absolute; left: 10px; color: #94a3b8; pointer-events: none; font-size: 1rem;"></i>
            </div>
        </div>

        <!-- Filter Search Keyword -->
        <div style="display: flex; flex-direction: column; gap: 4px; flex-grow: 1; min-width: 240px;">
            <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Cari Pelanggan</label>
            <div style="position: relative; display: flex; align-items: center;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    class="filter-input" 
                    placeholder="Ketik ID Pelanggan, Nama Toko, Alamat, atau No HP..." 
                    style="width: 100%; padding-left: 36px;"
                >
                <i class="fas fa-search" style="position: absolute; left: 12px; color: #94a3b8; pointer-events: none;"></i>
            </div>
        </div>

        <!-- Filter Tipe (MT/GT) -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Tipe Outlet</label>
            <select name="type" class="filter-select" onchange="this.form.submit()" style="min-width: 140px;">
                <option value="">Semua Tipe</option>
                <option value="MT" {{ $type === 'MT' ? 'selected' : '' }}>Modern Trade (MT)</option>
                <option value="GT" {{ $type === 'GT' ? 'selected' : '' }}>General Trade (GT)</option>
            </select>
        </div>

        <!-- Filter GPS -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Status GPS</label>
            <select name="gps_only" class="filter-select" onchange="this.form.submit()" style="min-width: 150px;">
                <option value="">Semua Status</option>
                <option value="1" {{ $gpsOnly === '1' ? 'selected' : '' }}>Ada Koordinat GPS</option>
                <option value="0" {{ $gpsOnly === '0' ? 'selected' : '' }}>Tanpa Koordinat GPS</option>
            </select>
        </div>

        <!-- Limit Per Halaman -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Limit / Hal</label>
            <select name="limit" class="filter-select" onchange="this.form.submit()" style="min-width: 110px;">
                <option value="10" {{ ($limit ?? 25) == 10 ? 'selected' : '' }}>10 / hal</option>
                <option value="25" {{ ($limit ?? 25) == 25 ? 'selected' : '' }}>25 / hal</option>
                <option value="50" {{ ($limit ?? 25) == 50 ? 'selected' : '' }}>50 / hal</option>
                <option value="100" {{ ($limit ?? 25) == 100 ? 'selected' : '' }}>100 / hal</option>
            </select>
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 8px; align-self: flex-end;">
            <button type="submit" class="btn btn-primary" style="background: #1ABB9C; border-color: #1ABB9C; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                <i class="fas fa-filter"></i> Terapkan
            </button>
            @if($search || $type || $gpsOnly || request('limit'))
                <a href="{{ route('konfirmasi_faktur.customers.index', ['branch' => $branch]) }}" class="btn btn-outline-secondary" style="padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; text-decoration: none;" title="Reset Filter">
                    <i class="fas fa-undo"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Data Table Panel --}}
<div class="content-panel" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--phoenix-border-color, #E6E9ED); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
    
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--phoenix-border-color, #E6E9ED); display: flex; justify-content: space-between; align-items: center; background: #fafafa; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;">Daftar Outlet Pelanggan</h3>
            <span class="badge-branch">{{ $branch }}</span>
            <span style="font-size: 0.8rem; color: #64748b;">
                (Menampilkan {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} dari {{ number_format($customers->total()) }} data)
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('konfirmasi_faktur.customers.export', request()->except(['page', 'limit'])) }}" class="btn-export-excel" style="padding: 6px 16px; font-size: 0.82rem;" title="Download seluruh data hasil API tanpa limit pagination">
                <i class="fas fa-file-excel"></i> Download Excel (Full {{ number_format($customers->total()) }} Data)
            </a>
            <span style="font-size: 0.8rem; color: #64748b;">
                Halaman {{ $customers->currentPage() }} dari {{ $customers->lastPage() ?: 1 }}
            </span>
        </div>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table class="premium-table" style="width: 100%; min-width: 1100px;">
            <thead>
                <tr>
                    <th style="width: 100px;">ID</th>
                    <th style="min-width: 240px;">Nama Pelanggan</th>
                    <th style="min-width: 150px;">No. Telepon / WA</th>
                    <th style="min-width: 260px;">Alamat</th>
                    <th style="min-width: 150px; text-align: center;">Titik Koordinat</th>
                    <th style="min-width: 130px; text-align: center;">Jam Operasional</th>
                    <th style="min-width: 90px; text-align: center;">Durasi</th>
                    <th style="min-width: 120px; text-align: center;">Tipe Outlet</th>
                    <th style="min-width: 90px; text-align: center;">Akses</th>
                    <th style="min-width: 100px; text-align: center;">Cabang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $row)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $row['phoneNumber'] ?? '');
                        if (str_starts_with($cleanPhone, '0')) {
                            $waNumber = '62' . substr($cleanPhone, 1);
                        } else {
                            $waNumber = $cleanPhone;
                        }
                    @endphp
                    <tr>
                        <!-- ID Pelanggan -->
                        <td style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 0.85rem;">
                            {{ $row['dataId'] ?? '-' }}
                        </td>

                        <!-- Nama Pelanggan -->
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.88rem;">
                                {{ $row['partnerName'] ?? '-' }}
                            </div>
                        </td>

                        <!-- Telepon / WhatsApp -->
                        <td>
                            @if(!empty($row['phoneNumber']) && $row['phoneNumber'] !== '0')
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="tel-link" title="Chat via WhatsApp">
                                    <i class="ri-whatsapp-line" style="color: #22c55e; font-size: 1.1rem;"></i>
                                    <span>{{ $row['phoneNumber'] }}</span>
                                </a>
                            @else
                                <span style="color: #94a3b8; font-size: 0.8rem;">-</span>
                            @endif
                        </td>

                        <!-- Alamat -->
                        <td style="font-size: 0.82rem; color: #475569; line-height: 1.45;">
                            {{ $row['address'] ?? '-' }}
                        </td>

                        <!-- Koordinat GPS -->
                        <td style="text-align: center;">
                            @if(!empty($row['coordinate']))
                                <a href="https://www.google.com/maps?q={{ urlencode($row['coordinate']) }}" target="_blank" class="map-chip" title="Lihat di Google Maps">
                                    <i class="ri-map-pin-line"></i> Maps
                                </a>
                                <div style="font-size: 0.68rem; color: #94a3b8; font-family: monospace; margin-top: 4px;">
                                    {{ Str::limit($row['coordinate'], 18) }}
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: #cbd5e1;">-</span>
                            @endif
                        </td>

                        <!-- Jam Buka - Tutup -->
                        <td style="text-align: center; font-size: 0.8rem; color: #334155;">
                            @if(!empty($row['openTime']) || !empty($row['closeTime']))
                                <div style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                                    <i class="ri-time-line" style="color: #64748b;"></i>
                                    {{ $row['openTime'] ?? '-' }} - {{ $row['closeTime'] ?? '-' }}
                                </div>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>

                        <!-- Durasi Kunjungan -->
                        <td style="text-align: center; font-size: 0.82rem; font-weight: 600; color: #475569;">
                            {{ !empty($row['visitTime']) ? $row['visitTime'] . ' mnt' : '-' }}
                        </td>

                        <!-- Tipe Outlet MT / GT -->
                        <td style="text-align: center;">
                            @if(isset($row['mt']) && $row['mt'] == 1)
                                <span class="badge-mt">MT</span>
                            @else
                                <span class="badge-gt">GT</span>
                            @endif
                        </td>

                        <!-- Akses Truk / Motor -->
                        <td style="text-align: center;">
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                @if(!empty($row['truckOnly']) && $row['truckOnly'] == 1)
                                    <span title="Akses Truk" style="color: #0284c7; font-size: 1rem;"><i class="ri-truck-line"></i></span>
                                @endif
                                @if(!empty($row['bikeOnly']) && $row['bikeOnly'] == 1)
                                    <span title="Akses Motor" style="color: #f59e0b; font-size: 1rem;"><i class="ri-riding-line"></i></span>
                                @endif
                                @if(empty($row['truckOnly']) && empty($row['bikeOnly']))
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </div>
                        </td>

                        <!-- Cabang -->
                        <td style="text-align: center;">
                            <span class="badge-branch">{{ $row['branchId'] ?? $branch }}</span>
                        </td>
                    </tr>
                @empty
                    <x-empty-state 
                        colspan="10"
                        title="Data Pelanggan Tidak Ditemukan"
                        description="Tidak ada data pelanggan yang sesuai dengan cabang '{{ $branch }}' atau kata kunci pencarian yang dimasukkan."
                        :actionUrl="route('konfirmasi_faktur.customers.index', ['branch' => $branch])"
                        actionText="Reset Pencarian"
                        actionIcon="fas fa-undo"
                    />
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar --}}
    @if($customers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--phoenix-border-color, #E6E9ED); background: #fafafa; display: flex; justify-content: center;">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
