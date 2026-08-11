<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterProduct;

class MasterProductController extends Controller
{
    /**
     * Display a listing of product counts grouped by principal.
     */
    public function index()
    {
        $principals = MasterProduct::select('principal')
            ->selectRaw('COUNT(*) as total_products')
            ->groupBy('principal')
            ->orderBy('principal', 'asc')
            ->get();

        return view('master.index', compact('principals'));
    }

    /**
     * Display the detailed list of products for a specific principal.
     */
    public function show(Request $request, $principal)
    {
        $query = MasterProduct::query();

        // If principal is '-' or 'none', fetch products with null or empty principal
        if ($principal === '-' || strtolower($principal) === 'none') {
            $query->where(function($q) {
                $q->whereNull('principal')
                  ->orWhere('principal', '');
            });
            $principalName = 'Tanpa Principal';
        } else {
            $query->where('principal', $principal);
            $principalName = $principal;
        }

        // Order by product name
        $query->orderBy('product_name', 'asc');

        // Fetch all products for client-side DataTables
        $products = $query->get();

        return view('master.show', compact('products', 'principalName'));
    }

    /**
     * Import products from JSON array.
     */
    public function import(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id_product' => 'required|string|max:20',
            'products.*.product_name' => 'required|string|max:100',
        ]);

        $products = $request->input('products');
        
        $data = [];
        foreach ($products as $p) {
            $data[] = [
                'id_product' => $p['id_product'],
                'product_name' => $p['product_name'],
                'principal' => isset($p['principal']) ? (string)$p['principal'] : null,
                'barcode' => isset($p['barcode']) ? (string)$p['barcode'] : null,
                'carton_code' => isset($p['carton_code']) ? (string)$p['carton_code'] : null,
                'packname' => isset($p['packname']) ? (string)$p['packname'] : null,
                'uom' => isset($p['uom']) && $p['uom'] !== '' ? intval($p['uom']) : null,
                'is_active' => isset($p['is_active']) ? (bool)$p['is_active'] : true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk upsert in chunks of 500
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $chunk) {
            MasterProduct::upsert($chunk, ['id_product'], [
                'product_name', 'principal', 'barcode', 'carton_code', 'packname', 'uom', 'is_active', 'updated_at'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($data) . ' produk berhasil di-import.',
        ]);
    }
}
