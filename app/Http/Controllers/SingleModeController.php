<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\OpnameCountHeader;
use App\Models\OpnameCountDetail;
use App\Models\User;
use App\Models\MasterProduct;

class SingleModeController extends Controller
{
    /**
     * Display a listing of Single Mode records by joining opname_count_header, 
     * opname_count_detail, users, and master_products.
     */
    public function records()
    {
        $records = [];
        $error = null;

        try {

            $activeSessionId = session('active_opname_session_id');
            
            if (!$activeSessionId) {
                $error = "Belum ada sesi terpilih. Silakan pilih Sesi Aktif pada Header di pojok kanan atas.";
            } else {
                // Fetch joined data using Eloquent models with joins
                $records = OpnameCountHeader::where('opname_count_headers.session_id', $activeSessionId)
                    ->join('opname_count_details as d', 'opname_count_headers.count_id', '=', 'd.count_id')
                    ->leftJoin('user as u', 'd.user_id', '=', 'u.id')
                    ->leftJoin('master_products as p', 'd.id_product', '=', 'p.id_product')
                    ->select(
                        'opname_count_headers.session_id',
                        'opname_count_headers.status',
                        'u.name as user_name',
                        'd.id_product',
                        'p.product_name as nama_product',
                        'd.qty_karton',
                        'd.qty_pcs',
                        'd.final_qty as qty_fisik',
                        'opname_count_headers.created_at'
                    )
                    ->orderBy('opname_count_headers.created_at', 'desc')
                    ->get();
            }
                
        } catch (QueryException $e) {
            // Catch error if tables don't exist yet
            if (str_contains($e->getMessage(), 'Base table or view not found')) {
                $error = "Tabel opname_count_header atau opname_count_detail belum ditemukan di database. Struktur kueri akan disesuaikan setelah tabel dibuat.";
            } else {
                $error = "Terjadi kesalahan kueri: " . $e->getMessage();
            }
        }

        return view('single_mode.records', compact('records', 'error'));
    }
}
