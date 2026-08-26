<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BadStockRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BadStockController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dari Flutter
        $validator = Validator::make($request->all(), [
            'sku_code' => 'required|string|max:50',
            'qty_carton' => 'nullable|integer',
            'qty_pcs' => 'nullable|integer',
            'total_qty' => 'required|integer',
            'item_status' => 'required|in:SALEABLE,NON_SALEABLE',
            'category' => 'required|in:BAD_STOCK,EXPIRED_STOCK',
            'batch_no' => 'nullable|string|max:100',
            'expired_date' => 'nullable|date',
            'recorded_by' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $record = BadStockRecord::create([
                'sku_code' => $request->sku_code,
                'qty_carton' => $request->qty_carton ?? 0,
                'qty_pcs' => $request->qty_pcs ?? 0,
                'total_qty' => $request->total_qty,
                'item_status' => $request->item_status,
                'category' => $request->category,
                'batch_no' => $request->batch_no,
                'expired_date' => $request->expired_date,
                'recorded_by' => $request->recorded_by ?? auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Bad Stock berhasil disimpan',
                'data' => $record
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
