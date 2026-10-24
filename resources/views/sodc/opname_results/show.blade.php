@extends('layouts.app')
@section('title', 'Detail Jejak Audit Opname')
@section('page_title', 'Detail Jejak Audit Hasil Opname')

@section('page_actions')
<div style="display: flex; gap: 10px; align-items: center;">
    <a href="{{ route('sodc.results.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: background 0.2s;">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Rekonsiliasi
    </a>
    <button onclick="window.print()" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
        <i class="fas fa-print"></i> Cetak Dokumen
    </button>
</div>
@endsection

@section('content')
@php
    $product = $result->referenceDetail->product ?? null;
    $isMatch = $result->result_status == 'MATCH';
    $isFinal = in_array($result->result_status, ['FINAL', 'APPROVED']);
    
    $badgeColor = '#94a3b8';
    if ($isMatch) $badgeColor = '#10b981';
    elseif ($isFinal) $badgeColor = '#3b82f6';
    elseif (str_starts_with($result->result_status, 'RECOUNT')) $badgeColor = '#f59e0b';
    elseif (str_starts_with($result->result_status, 'WAITING')) $badgeColor = '#8b5cf6';

    $isNyasar = (float)$result->system_qty == 0;
@endphp

<div style="display: flex; flex-direction: column; gap: 24px; margin-top: 10px;">

    <!-- Card 1: Informasi Produk & Lokasi Bin -->
    <div style="background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
            <div>
                <span style="background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">
                    <i class="fas fa-map-marker-alt"></i> Lokasi Bin: {{ $result->referenceDetail->bin_code ?? '-' }}
                </span>
                @if($isNyasar)
                    <span style="background: #fef08a; color: #854d0e; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-left: 6px;">
                        NON-WMS (BARANG FISIK NYASAR)
                    </span>
                @endif
                <h3 style="margin: 10px 0 4px 0; color: #0f172a; font-size: 1.35rem; font-weight: 800;">
                    {{ $product->sku_name ?? 'Unknown Product' }}
                </h3>
                <div style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #64748b;">
                    SKU: <span style="color: #0ea5e9;">{{ $result->referenceDetail->sku_code ?? '-' }}</span>
                </div>
            </div>
            
            <div style="text-align: right;">
                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 4px;">Status Hasil Akhir:</div>
                <span style="background: {{ $badgeColor }}; color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block;">
                    {{ $result->result_status }}
                </span>
            </div>
        </div>

        <!-- Grid Spesifikasi Field Database -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Kategori Produk</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #0369a1; margin-top: 4px;">
                    <span style="background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                        {{ $product->product_category_code ?? '-' }}
                    </span>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Satuan Kemasan (Packname)</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #166534; margin-top: 4px;">
                    <span style="background: #dcfce7; padding: 2px 8px; border-radius: 6px;">
                        {{ $product->packname ?? '-' }}
                    </span>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">UOM (Isi per Pack)</div>
                <div style="font-size: 1rem; font-weight: 700; color: #0f172a; margin-top: 4px;">
                    {{ $product->uom ?? 1 }} Pcs
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Prinsipal / Supplier</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #334155; margin-top: 4px;">
                    {{ $product->principal ?? '-' }}
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Company Code</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #334155; margin-top: 4px;">
                    {{ $product->company_code ?? '-' }}
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Barcode Satuan / Karton</div>
                <div style="font-size: 0.82rem; font-family: monospace; font-weight: 600; color: #475569; margin-top: 4px;">
                    Item: {{ $product->barcode ?? '-' }}<br>
                    Dus: {{ $product->carton_code ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Ringkasan Rekonsiliasi Qty (KPI Bar) -->
    <div style="background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h4 style="margin: 0 0 16px 0; color: #1e293b; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-balance-scale" style="color: #0ea5e9;"></i> Ringkasan Rekonsiliasi Fisik vs Sistem
        </h4>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 14px;">
            <div style="text-align: center; padding: 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">WMS System Qty</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ (float)$result->system_qty }}</div>
            </div>

            <div style="text-align: center; padding: 14px; background: #f0fdf4; border-radius: 10px; border: 1px solid #bbf7d0;">
                <div style="font-size: 0.75rem; color: #166534; font-weight: 600;">Team A Qty</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #15803d; margin-top: 4px;">
                    {{ is_null($result->team_a_qty) ? '-' : (float)$result->team_a_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: #f0f9ff; border-radius: 10px; border: 1px solid #bae6fd;">
                <div style="font-size: 0.75rem; color: #0369a1; font-weight: 600;">Team B Qty</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0284c7; margin-top: 4px;">
                    {{ is_null($result->team_b_qty) ? '-' : (float)$result->team_b_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: #fffbeb; border-radius: 10px; border: 1px solid #fde68a;">
                <div style="font-size: 0.75rem; color: #92400e; font-weight: 600;">Recount 1</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #d97706; margin-top: 4px;">
                    {{ is_null($result->recount1_qty) ? '-' : (float)$result->recount1_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: #fef2f2; border-radius: 10px; border: 1px solid #fecaca;">
                <div style="font-size: 0.75rem; color: #991b1b; font-weight: 600;">Recount 2</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #dc2626; margin-top: 4px;">
                    {{ is_null($result->recount2_qty) ? '-' : (float)$result->recount2_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: #faf5ff; border-radius: 10px; border: 1px solid #e9d5ff;">
                <div style="font-size: 0.75rem; color: #6b21a8; font-weight: 600;">Recount 3</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #7e22ce; margin-top: 4px;">
                    {{ is_null($result->recount3_qty) ? '-' : (float)$result->recount3_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: #0f172a; color: white; border-radius: 10px;">
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">Final Qty Disetujui</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #38bdf8; margin-top: 4px;">
                    {{ is_null($result->final_qty) ? '-' : (float)$result->final_qty }}
                </div>
            </div>

            <div style="text-align: center; padding: 14px; background: {{ (float)$result->variance_qty == 0 ? '#f0fdf4' : '#fff1f2' }}; border-radius: 10px; border: 1px solid {{ (float)$result->variance_qty == 0 ? '#bbf7d0' : '#fecdd3' }};">
                <div style="font-size: 0.75rem; color: {{ (float)$result->variance_qty == 0 ? '#166534' : '#9f1239' }}; font-weight: 600;">Selisih (Variance)</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: {{ (float)$result->variance_qty == 0 ? '#15803d' : '#e11d48' }}; margin-top: 4px;">
                    {{ (float)$result->variance_qty > 0 ? '+' : '' }}{{ (float)$result->variance_qty }}
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Jejak Audit Lengkap Input Per User (Audit Trail) -->
    <div style="background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h4 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-history" style="color: #6366f1;"></i> Jejak Riwayat Input Fisik (Audit Trail Lengkap)
        </h4>
        <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 20px 0;">
            Rekaman historis pengguna yang melakukan input fisik di Bin ini mulai dari hitungan awal hingga tahapan Recount 3.
        </p>

        @if($counts->isEmpty())
            <div style="text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 10px;">
                <i class="fas fa-clipboard-list" style="font-size: 2.5rem; margin-bottom: 10px; color: #cbd5e1;"></i>
                <div style="font-weight: 600;">Belum ada input fisik yang tersimpan untuk barang ini.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #475569; text-align: left; font-size: 0.8rem; text-transform: uppercase;">
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1;">Tahap Hitung</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1;">User Penginput</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1;">Role / Jabatan</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1; text-align: right;">Input Karton</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1; text-align: right;">Input Pcs</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1; text-align: right;">Total Qty (Pcs)</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1;">Waktu Input</th>
                            <th style="padding: 12px 14px; border-bottom: 1px solid #cbd5e1; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($counts as $idx => $c)
                            @php
                                $stageName = 'Hitung Awal';
                                $stageColor = '#0ea5e9';
                                $stageBg = '#f0f9ff';

                                if ($c->count_sequence == 1) {
                                    if ($c->team_id == 'TEAM_A' || $idx == 0) {
                                        $stageName = 'Hitung Awal (Team A)';
                                        $stageColor = '#16a34a';
                                        $stageBg = '#f0fdf4';
                                    } else {
                                        $stageName = 'Hitung Awal (Team B)';
                                        $stageColor = '#0284c7';
                                        $stageBg = '#f0f9ff';
                                    }
                                } elseif ($c->count_sequence == 2) {
                                    $stageName = 'Recount 1 (R1)';
                                    $stageColor = '#d97706';
                                    $stageBg = '#fffbeb';
                                } elseif ($c->count_sequence == 3) {
                                    $stageName = 'Recount 2 (R2)';
                                    $stageColor = '#dc2626';
                                    $stageBg = '#fef2f2';
                                } elseif ($c->count_sequence >= 4) {
                                    $stageName = 'Recount 3 (R3)';
                                    $stageColor = '#7e22ce';
                                    $stageBg = '#faf5ff';
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 14px; font-weight: 700;">
                                    <span style="background: {{ $stageBg }}; color: {{ $stageColor }}; padding: 4px 10px; border-radius: 6px; border: 1px solid {{ $stageColor }}33;">
                                        {{ $stageName }}
                                    </span>
                                </td>
                                <td style="padding: 14px;">
                                    <div style="font-weight: 700; color: #1e293b;">
                                        <i class="fas fa-user-circle" style="color: #64748b; margin-right: 4px;"></i>
                                        {{ $c->user->full_name ?? ($c->user->username ?? 'User #' . $c->counted_by) }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #94a3b8; font-family: monospace;">
                                        NIK: {{ $c->user->employee_code ?? '-' }}
                                    </div>
                                </td>
                                <td style="padding: 14px;">
                                    <span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        {{ $c->user->role ?? '-' }}
                                    </span>
                                </td>
                                <td style="padding: 14px; text-align: right; font-weight: 600; color: #334155;">
                                    {{ (int)($c->input_karton ?? 0) }}
                                </td>
                                <td style="padding: 14px; text-align: right; font-weight: 600; color: #334155;">
                                    {{ (int)($c->input_pcs ?? 0) }}
                                </td>
                                <td style="padding: 14px; text-align: right; font-weight: 800; font-size: 1rem; color: #0f172a;">
                                    {{ (float)$c->count_qty }}
                                </td>
                                <td style="padding: 14px; color: #64748b; font-size: 0.82rem;">
                                    <i class="far fa-clock" style="margin-right: 4px;"></i>
                                    {{ $c->counted_at ? date('d M Y, H:i:s', strtotime($c->counted_at)) : ($c->created_at ? date('d M Y, H:i:s', strtotime($c->created_at)) : '-') }}
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <span style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                        {{ $c->count_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Card 4: Data Penugasan Recount (Jika Ada) -->
    @if(!empty($recountAssignments) && count($recountAssignments) > 0)
    <div style="background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h4 style="margin: 0 0 12px 0; color: #1e293b; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-tasks" style="color: #f59e0b;"></i> Log Penugasan Auditor Recount (Round 1 - 3)
        </h4>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; text-align: left;">
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;">Round</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;">Ditugaskan Kepada</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;">Ditugaskan Oleh</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;">Waktu Ditugaskan</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;">Waktu Submit</th>
                        <th style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recountAssignments as $ra)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 8px 12px; font-weight: bold; color: #d97706;">Round {{ $ra->round_number }}</td>
                            <td style="padding: 8px 12px; font-weight: 600; color: #0f172a;">{{ $ra->assigned_to_name ?? ('User #' . $ra->assigned_to) }}</td>
                            <td style="padding: 8px 12px; color: #64748b;">{{ $ra->assigned_by_name ?? 'Admin' }}</td>
                            <td style="padding: 8px 12px; color: #64748b;">{{ $ra->assigned_at ? date('d M Y H:i', strtotime($ra->assigned_at)) : '-' }}</td>
                            <td style="padding: 8px 12px; color: #64748b;">{{ $ra->submitted_at ? date('d M Y H:i', strtotime($ra->submitted_at)) : '-' }}</td>
                            <td style="padding: 8px 12px; text-align: center;">
                                <span style="background: {{ $ra->status == 'COMPLETED' ? '#dcfce7' : '#fef9c3' }}; color: {{ $ra->status == 'COMPLETED' ? '#15803d' : '#854d0e' }}; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                    {{ $ra->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
