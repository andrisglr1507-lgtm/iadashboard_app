<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\OpnameSession;
use App\Models\OpnameCountHeader;
use App\Models\OpnameCountDetail;
use App\Models\OpnameProduct;
use App\Models\OpnameRecountAssignment;
use App\Models\MasterProduct;
use App\Models\User;

class RecountDoubleController extends Controller
{
    /**
     * Main recount page
     */
    public function index()
    {
        $activeSessionId = session('active_opname_session_id');
        $error = null;
        $summaryData = [];
        $comprehensiveAnalysis = [];
        $stats = [
            'totalData' => 0, 'finalData' => 0, 'needR1' => 0, 'needR2' => 0,
            'hasAnalisa' => 0, 'possibleTypo' => 0, 'anomalyCount' => 0,
            'totalAnalysis' => 0, 'highConfidence' => 0, 'medConfidence' => 0,
            'lowConfidence' => 0, 'codeMatches' => 0, 'nameMatches' => 0,
        ];
        $principals = [];
        $users = [];

        if (!$activeSessionId) {
            $error = "Belum ada sesi terpilih. Silakan pilih Sesi Aktif pada Header.";
            return view('double_mode.recount', compact('error', 'summaryData', 'comprehensiveAnalysis', 'stats', 'principals', 'users', 'activeSessionId'));
        }

        try {
            // Validate session is mode 'D' or 'double'
            $session = OpnameSession::where('session_id', $activeSessionId)->first();
            if (!$session || !in_array($session->mode, ['D', 'double'])) {
                $error = "Session tidak ditemukan atau bukan mode Double (A Vs B).";
                return view('double_mode.recount', compact('error', 'summaryData', 'comprehensiveAnalysis', 'stats', 'principals', 'users', 'activeSessionId'));
            }

            // Get active users
            $users = User::where('is_active', 1)->orderBy('name')->get(['id', 'user_id', 'name', 'email']);

            // Get count data (A, B, R1, R2) grouped by product, location, and team
            $countResult = OpnameCountHeader::where('opname_count_headers.session_id', $activeSessionId)
                ->join('opname_count_details as d', 'opname_count_headers.count_id', '=', 'd.count_id')
                ->whereIn('opname_count_headers.team', ['A', 'B', 'R1', 'R2'])
                ->groupBy('d.id_product', 'opname_count_headers.location_code', 'opname_count_headers.team')
                ->select(
                    'd.id_product', 'opname_count_headers.location_code', 'opname_count_headers.team',
                    DB::raw('SUM(d.qty_karton) as qty_karton'),
                    DB::raw('SUM(d.qty_pcs) as qty_pcs'),
                    DB::raw('SUM(d.final_qty) as final_qty')
                )->get();

            $countData = [];
            foreach ($countResult as $row) {
                $loc = $row->location_code ?: '-';
                $countData[$row->id_product][$loc][$row->team] = [
                    'karton' => (int)$row->qty_karton,
                    'pcs' => (int)$row->qty_pcs,
                    'final_qty' => (int)$row->final_qty,
                ];
            }

            // Get stock system from opname_products
            $stockSystemData = [];
            $stockRows = OpnameProduct::where('session_id', $activeSessionId)
                ->select('id_product', 'stock_system')->get();
            foreach ($stockRows as $row) {
                $stockSystemData[$row->id_product] = (int)$row->stock_system;
            }

            // Get master products
            $masterProducts = [];
            $masterRows = MasterProduct::select('id_product', 'product_name', 'uom', 'packname', 'principal')->get();
            foreach ($masterRows as $row) {
                $masterProducts[$row->id_product] = $row->toArray();
            }

            // Get active assignments to prevent double assignment
            $activeAssignmentsRaw = OpnameRecountAssignment::where('session_id', $activeSessionId)
                ->whereIn('status', ['assigned', 'distributed', 'started'])
                ->select('id_product', 'location_code', 'round_number')
                ->get();
            
            $activeAssignments = [];
            foreach ($activeAssignmentsRaw as $assignment) {
                $loc = $assignment->location_code ?: '-';
                $activeAssignments[$assignment->id_product][$loc][$assignment->round_number] = true;
            }

            $processedProducts = [];
            $index = 1;

            // Build summary data based on Counted Products (Location Specific)
            foreach ($countData as $productId => $locations) {
                $processedProducts[$productId] = true;
                $stockSystem = $stockSystemData[$productId] ?? 0;
                $master = $masterProducts[$productId] ?? null;
                $productName = $master['product_name'] ?? 'Unknown';
                $uom = $master['uom'] ?? '-';
                $packname = $master['packname'] ?? '-';
                $principal = $master['principal'] ?? '-';
                $uomVal = (int)$uom;
                $ssKarton = $uomVal > 0 ? intdiv($stockSystem, $uomVal) : 0;
                $ssPcs = $uomVal > 0 ? $stockSystem % $uomVal : $stockSystem;

                foreach ($locations as $loc => $teams) {
                    $teamA = $teams['A'] ?? null;
                    $teamB = $teams['B'] ?? null;
                    $teamR1 = $teams['R1'] ?? null;
                    $teamR2 = $teams['R2'] ?? null;

                    $result = $this->calculateDoubleResult($teamA, $teamB, $stockSystem, $teamR1, $teamR2, $productId, $loc, $activeAssignments);

                    $hasInput = (($teamA && $teamA['final_qty'] > 0) || ($teamB && $teamB['final_qty'] > 0));
                    $analisis = null;
                    $highestQty = max($teamA['final_qty'] ?? 0, $teamB['final_qty'] ?? 0);

                    if ($stockSystem == 0 && $hasInput) {
                        $similar = $this->findSimilarProducts($productId, $stockSystemData, $masterProducts);
                        $similarWithStock = array_filter($similar, fn($s) => $s['stock_system'] > 0);

                        if (!empty($similarWithStock)) {
                            $analisis = [
                                'has_suggestion' => true,
                                'message' => '⚠️ Stok sistem 0, tapi ada fisik (Tinggi: ' . number_format($highestQty) . '). Kemungkinan kesalahan kode!',
                                'suggestions' => array_values($similarWithStock),
                                'type' => 'possible_typo',
                                's_qty' => $highestQty,
                            ];
                        } elseif (!empty($similar)) {
                            $analisis = [
                                'has_suggestion' => true,
                                'message' => 'ℹ️ Stok sistem 0, ada fisik (' . number_format($highestQty) . '). Ditemukan kode mirip (stok 0 juga).',
                                'suggestions' => $similar,
                                'type' => 'similar_code',
                                's_qty' => $highestQty,
                            ];
                        } else {
                            $analisis = [
                                'has_suggestion' => true,
                                'message' => '⚠️ ANOMALI: Stok 0, tapi ada fisik (' . number_format($highestQty) . '). Perlu investigasi!',
                                'suggestions' => [],
                                'type' => 'anomaly',
                                's_qty' => $highestQty,
                            ];
                        }
                    }

                    $summaryData[] = [
                        'no' => $index++,
                        'id_product' => $productId,
                        'location' => $loc,
                        'product_name' => $productName,
                        'packname' => $packname,
                        'uom' => $uom,
                        'principal' => $principal,
                        'stock_system' => $stockSystem,
                        'stock_system_karton' => $ssKarton,
                        'stock_system_pcs' => $ssPcs,
                        'a_karton' => $teamA ? $teamA['karton'] : '-',
                        'a_pcs' => $teamA ? $teamA['pcs'] : '-',
                        'a_total' => $teamA ? $teamA['final_qty'] : '-',
                        'b_karton' => $teamB ? $teamB['karton'] : '-',
                        'b_pcs' => $teamB ? $teamB['pcs'] : '-',
                        'b_total' => $teamB ? $teamB['final_qty'] : '-',
                        'r1_karton' => $teamR1 ? $teamR1['karton'] : '-',
                        'r1_pcs' => $teamR1 ? $teamR1['pcs'] : '-',
                        'r1_total' => $teamR1 ? $teamR1['final_qty'] : '-',
                        'r2_karton' => $teamR2 ? $teamR2['karton'] : '-',
                        'r2_pcs' => $teamR2 ? $teamR2['pcs'] : '-',
                        'r2_total' => $teamR2 ? $teamR2['final_qty'] : '-',
                        'result_stage' => $result['stage'],
                        'result_label' => $result['label'],
                        'result_total' => $result['final_total'],
                        'can_assign' => $result['can_assign'],
                        'round_to_assign' => $result['round_to_assign'],
                        'analisis' => $analisis,
                        'has_analisis' => $analisis !== null,
                    ];
                }
            }

            // Products in system stock but NEVER counted by any team in any location
            foreach ($stockSystemData as $productId => $stockSystem) {
                if (!isset($processedProducts[$productId])) {
                    $master = $masterProducts[$productId] ?? null;
                    $productName = $master['product_name'] ?? 'Unknown';
                    $uom = $master['uom'] ?? '-';
                    $packname = $master['packname'] ?? '-';
                    $principal = $master['principal'] ?? '-';
                    $uomVal = (int)$uom;
                    $ssKarton = $uomVal > 0 ? intdiv($stockSystem, $uomVal) : 0;
                    $ssPcs = $uomVal > 0 ? $stockSystem % $uomVal : $stockSystem;

                    $summaryData[] = [
                        'no' => $index++,
                        'id_product' => $productId,
                        'location' => '-',
                        'product_name' => $productName,
                        'packname' => $packname,
                        'uom' => $uom,
                        'principal' => $principal,
                        'stock_system' => $stockSystem,
                        'stock_system_karton' => $ssKarton,
                        'stock_system_pcs' => $ssPcs,
                        'a_karton' => '-', 'a_pcs' => '-', 'a_total' => '-',
                        'b_karton' => '-', 'b_pcs' => '-', 'b_total' => '-',
                        'r1_karton' => '-', 'r1_pcs' => '-', 'r1_total' => '-',
                        'r2_karton' => '-', 'r2_pcs' => '-', 'r2_total' => '-',
                        'result_stage' => 'waiting',
                        'result_label' => 'WAITING A/B',
                        'result_total' => null,
                        'can_assign' => false,
                        'round_to_assign' => null,
                        'analisis' => null,
                        'has_analisis' => false,
                    ];
                }
            }

            // Build comprehensive analysis
            $comprehensiveAnalysis = $this->buildComprehensiveAnalysis($stockSystemData, $countData, $masterProducts);

            // Collect unique principals
            foreach ($summaryData as $row) {
                if (!empty($row['principal']) && $row['principal'] !== '-') {
                    $principals[] = $row['principal'];
                }
            }
            $principals = array_unique($principals);
            sort($principals);

            // Build stats
            $stats = [
                'totalData' => count($summaryData),
                'finalData' => count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'final')),
                'needR1' => count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'need_r1')),
                'needR2' => count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'need_r2')),
                'hasAnalisa' => count(array_filter($summaryData, fn($d) => $d['has_analisis'] === true)),
                'possibleTypo' => count(array_filter($summaryData, fn($d) => $d['analisis'] && $d['analisis']['type'] === 'possible_typo')),
                'anomalyCount' => count(array_filter($summaryData, fn($d) => $d['analisis'] && $d['analisis']['type'] === 'anomaly')),
                'totalAnalysis' => count($comprehensiveAnalysis),
                'highConfidence' => count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Tinggi')),
                'medConfidence' => count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Sedang')),
                'lowConfidence' => count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Rendah')),
                'codeMatches' => count(array_filter($comprehensiveAnalysis, fn($a) => in_array($a['best_match_type'], ['code', 'both']))),
                'nameMatches' => count(array_filter($comprehensiveAnalysis, fn($a) => in_array($a['best_match_type'], ['name', 'both']))),
            ];

        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'Base table or view not found')) {
                $error = "Tabel yang diperlukan belum ditemukan di database.";
            } else {
                $error = "Terjadi kesalahan kueri: " . $e->getMessage();
            }
        }

        return view('double_mode.recount', compact('error', 'summaryData', 'comprehensiveAnalysis', 'stats', 'principals', 'users', 'activeSessionId'));
    }

    /**
     * AJAX: Get product detail history
     */
    public function getProductDetail(Request $request)
    {
        $sessionId = $request->input('session_id') ?? session('active_opname_session_id');
        $productId = $request->input('id_product');

        if (!$sessionId || !$productId) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        $product = MasterProduct::where('id_product', $productId)
            ->select('product_name', 'uom', 'packname', 'principal')
            ->first();

        $productName = $product->product_name ?? 'Unknown Product';
        $principal = $product->principal ?? '-';
        $uom = $product->uom ?? '-';
        $packname = $product->packname ?? '-';
        $cartonSize = is_numeric($uom) ? max(1, (int)$uom) : 1;

        $historyRows = OpnameCountHeader::where('opname_count_headers.session_id', $sessionId)
            ->join('opname_count_details as d', 'opname_count_headers.count_id', '=', 'd.count_id')
            ->where('d.id_product', $productId)
            ->orderBy('opname_count_headers.team')
            ->orderBy('opname_count_headers.location_code')
            ->select('d.detail_id', 'opname_count_headers.count_id', 'd.id_product', 'opname_count_headers.location_code', 'opname_count_headers.team', 'd.qty_karton', 'd.qty_pcs', 'd.final_qty', 'opname_count_headers.created_at')
            ->get();

        $history = $historyRows->map(function ($row) {
            return [
                'detail_id' => (int)$row->detail_id,
                'count_id' => (int)$row->count_id,
                'id_product' => $row->id_product,
                'location' => $row->location_code,
                'team' => $row->team,
                'qty_karton' => (int)$row->qty_karton,
                'qty_pcs' => (int)$row->qty_pcs,
                'final_qty' => (int)$row->final_qty,
                'timestamp' => $row->created_at ? date('d/m/Y H:i', strtotime($row->created_at)) : '-',
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'product' => [
                'id_product' => $productId,
                'product_name' => $productName,
                'principal' => $principal,
                'uom' => $uom,
                'packname' => $packname,
                'carton_size' => $cartonSize,
            ],
            'history' => $history,
        ]);
    }

    /**
     * AJAX: Assign users to recount products
     */
    public function assignUsers(Request $request)
    {
        $sessionId = $request->input('session_id') ?? session('active_opname_session_id');
        $assignedTo = $request->input('assigned_to');
        $selectedProductsRaw = $request->input('selected_products');
        $assignedBy = $request->input('assigned_by', 1);

        $selectedProducts = is_string($selectedProductsRaw) ? json_decode($selectedProductsRaw, true) : ($selectedProductsRaw ?? []);

        if (!$sessionId) return response()->json(['success' => false, 'message' => 'Session ID tidak ditemukan']);
        if (!$assignedTo) return response()->json(['success' => false, 'message' => 'Pilih user terlebih dahulu']);
        if (empty($selectedProducts)) return response()->json(['success' => false, 'message' => 'Tidak ada produk yang dipilih']);

        $inserted = 0;
        $skipped = 0;

        foreach ($selectedProducts as $productStr) {
            // format: id_product|location|round_number
            $parts = explode('|', $productStr);
            if (count($parts) !== 3) { $skipped++; continue; }

            $productId = $parts[0];
            $locationCode = $parts[1];
            $roundNumber = (int)$parts[2];
            
            if (!in_array($roundNumber, [2, 3])) { $skipped++; continue; }

            // Check duplicate
            $existing = OpnameRecountAssignment::where('session_id', $sessionId)
                ->where('id_product', $productId)
                ->where('location_code', $locationCode)
                ->where('round_number', $roundNumber)
                ->exists();

            if ($existing) { $skipped++; continue; }

            // Find previous assignment for R2
            $previousId = null;
            if ($roundNumber == 3) {
                $prev = OpnameRecountAssignment::where('session_id', $sessionId)
                    ->where('id_product', $productId)
                    ->where('location_code', $locationCode)
                    ->where('round_number', 2)
                    ->first();
                if ($prev) $previousId = $prev->assignment_id;
            }

            OpnameRecountAssignment::create([
                'session_id' => $sessionId,
                'location_code' => $locationCode,
                'id_product' => $productId,
                'round_number' => $roundNumber,
                'assigned_to' => $assignedTo,
                'assigned_by' => $assignedBy,
                'status' => 'assigned',
                'assigned_at' => now(),
                'is_final' => 0,
                'previous_assignment_id' => $previousId,
            ]);
            $inserted++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil assign {$inserted} tugas recount" . ($skipped > 0 ? ", {$skipped} gagal/skip" : ""),
            'inserted' => $inserted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * AJAX: Edit calculation detail history
     */
    public function editHistory(Request $request)
    {
        $sessionId = $request->input('session_id') ?? session('active_opname_session_id');
        $detailId = $request->input('detail_id');
        $newProductId = $request->input('new_id_product');
        $qtyKarton = (int)$request->input('qty_karton', 0);
        $qtyPcs = (int)$request->input('qty_pcs', 0);
        $finalQty = (int)$request->input('final_qty', 0);

        if (!$sessionId || !$detailId || !$newProductId) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        // 1. Validasi apakah product ID baru terdaftar di master produk
        $prodExists = MasterProduct::where('id_product', $newProductId)->exists();
        if (!$prodExists) {
            return response()->json(['success' => false, 'message' => 'Product ID tidak terdaftar di master produk']);
        }

        // 2. Ambil info record lama
        $oldRow = OpnameCountDetail::where('detail_id', $detailId)->select('count_id', 'id_product')->first();
        if (!$oldRow) {
            return response()->json(['success' => false, 'message' => 'Record asal tidak ditemukan']);
        }

        $countId = (int)$oldRow->count_id;
        $oldProductId = $oldRow->id_product;

        try {
            DB::beginTransaction();

            // 3. Cek duplikasi jika product ID diganti
            if ($newProductId !== $oldProductId) {
                $existingNew = OpnameCountDetail::where('count_id', $countId)
                    ->where('id_product', $newProductId)
                    ->select('detail_id', 'qty_karton', 'qty_pcs', 'final_qty')
                    ->first();

                if ($existingNew) {
                    $mergedKarton = (int)$existingNew->qty_karton + $qtyKarton;
                    $mergedPcs = (int)$existingNew->qty_pcs + $qtyPcs;
                    $mergedFinal = (int)$existingNew->final_qty + $finalQty;
                    $targetDetailId = (int)$existingNew->detail_id;

                    OpnameCountDetail::where('detail_id', $targetDetailId)->update([
                        'qty_karton' => $mergedKarton,
                        'qty_pcs' => $mergedPcs,
                        'final_qty' => $mergedFinal,
                    ]);

                    OpnameCountDetail::where('detail_id', $detailId)->delete();

                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Product ID digabungkan karena sudah ada record untuk produk ini']);
                }
            }

            OpnameCountDetail::where('detail_id', $detailId)->update([
                'id_product' => $newProductId,
                'qty_karton' => $qtyKarton,
                'qty_pcs' => $qtyPcs,
                'final_qty' => $finalQty,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Record berhasil diupdate']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Delete calculation detail history
     */
    public function deleteHistory(Request $request)
    {
        $sessionId = $request->input('session_id') ?? session('active_opname_session_id');
        $detailId = $request->input('detail_id');

        if (!$sessionId || !$detailId) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        try {
            OpnameCountDetail::where('detail_id', $detailId)->delete();
            return response()->json(['success' => true, 'message' => 'Record perhitungan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
        }
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    private function calculateDoubleResult($teamA, $teamB, $stockSystem, $teamR1, $teamR2, $productId, $loc, $activeAssignments)
    {
        $aTotal = isset($teamA['final_qty']) ? $teamA['final_qty'] : null;
        $bTotal = isset($teamB['final_qty']) ? $teamB['final_qty'] : null;
        $r1Total = isset($teamR1['final_qty']) ? $teamR1['final_qty'] : null;
        $r2Total = isset($teamR2['final_qty']) ? $teamR2['final_qty'] : null;

        if ($r2Total !== null) {
            return ['stage' => 'final', 'label' => 'FINAL R2', 'final_total' => $r2Total, 'can_assign' => false, 'round_to_assign' => null];
        }

        if ($r1Total !== null) {
            if ($r1Total == $aTotal || $r1Total == $bTotal) {
                return ['stage' => 'final', 'label' => 'FINAL R1', 'final_total' => $r1Total, 'can_assign' => false, 'round_to_assign' => null];
            }
            if (isset($activeAssignments[$productId][$loc][3])) {
                return ['stage' => 'assigned', 'label' => 'ASSIGNED R2', 'final_total' => null, 'can_assign' => false, 'round_to_assign' => null];
            }
            return ['stage' => 'need_r2', 'label' => 'NEED R2', 'final_total' => null, 'can_assign' => true, 'round_to_assign' => 3];
        }

        if ($aTotal !== null && $bTotal !== null) {
            if ($aTotal == $bTotal) {
                return ['stage' => 'final', 'label' => 'MATCH', 'final_total' => $aTotal, 'can_assign' => false, 'round_to_assign' => null];
            } else {
                if (isset($activeAssignments[$productId][$loc][2])) {
                    return ['stage' => 'assigned', 'label' => 'ASSIGNED R1', 'final_total' => null, 'can_assign' => false, 'round_to_assign' => null];
                }
                return ['stage' => 'need_r1', 'label' => 'NEED R1', 'final_total' => null, 'can_assign' => true, 'round_to_assign' => 2];
            }
        }
        
        // If either A or B hasn't submitted yet
        return ['stage' => 'waiting', 'label' => 'WAITING A/B', 'final_total' => null, 'can_assign' => false, 'round_to_assign' => null];
    }

    private function findSimilarProducts($productId, $allStockData, $masterProducts)
    {
        $similarProducts = [];
        $basePattern = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productId));

        foreach ($allStockData as $checkId => $stock) {
            if ($checkId === $productId) continue;
            $checkPattern = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($checkId));

            if (strpos($checkPattern, $basePattern) === 0 || strpos($basePattern, $checkPattern) === 0 || levenshtein($basePattern, $checkPattern) <= 3) {
                $diffLength = abs(strlen($checkPattern) - strlen($basePattern));
                $levDistance = levenshtein($basePattern, $checkPattern);

                if ($diffLength <= 3 || $levDistance <= 3) {
                    $similarProducts[] = [
                        'id_product' => $checkId,
                        'stock_system' => $stock,
                        'similarity_score' => min($diffLength, $levDistance),
                        'product_name' => $masterProducts[$checkId]['product_name'] ?? 'Unknown',
                    ];
                }
            }
        }
        usort($similarProducts, fn($a, $b) => $a['similarity_score'] - $b['similarity_score']);
        return $similarProducts;
    }

    private function calculateNameSimilarity($name1, $name2)
    {
        $n1 = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $name1))));
        $n2 = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $name2))));
        if (empty($n1) || empty($n2)) return 0;

        similar_text($n1, $n2, $charPercent);
        $words1 = array_unique(explode(' ', $n1));
        $words2 = array_unique(explode(' ', $n2));
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        $jaccard = $union > 0 ? ($intersection / $union) * 100 : 0;

        return ($charPercent * 0.6) + ($jaccard * 0.4);
    }

    private function buildComprehensiveAnalysis($stockSystemData, $countData, $masterProducts)
    {
        $analysis = [];

        foreach ($stockSystemData as $productId => $stockSystem) {
            if ($stockSystem != 0) continue;
            
            $teams = $countData[$productId] ?? [];
            if (empty($teams)) continue; // Not counted anywhere
            
            // Just check if ANY location has physical count for A or B
            $totalCounted = 0;
            foreach ($teams as $loc => $teamGroup) {
                $totalCounted += ($teamGroup['A']['final_qty'] ?? 0);
                $totalCounted += ($teamGroup['B']['final_qty'] ?? 0);
            }
            if ($totalCounted <= 0) continue;

            $sourceName = $masterProducts[$productId]['product_name'] ?? 'Unknown';
            $sourcePackname = $masterProducts[$productId]['packname'] ?? '-';
            $sourceUom = $masterProducts[$productId]['uom'] ?? '-';

            $matches = [];
            $seenIds = [];
            $baseCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productId));

            // Layer 1: Code similarity
            foreach ($stockSystemData as $candidateId => $candidateStock) {
                if ($candidateId === $productId || $candidateStock <= 0) continue;
                $checkCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($candidateId));
                $codeDist = levenshtein($baseCode, $checkCode);

                if ($codeDist <= 5) {
                    $candidateName = $masterProducts[$candidateId]['product_name'] ?? 'Unknown';
                    $nameScore = $this->calculateNameSimilarity($sourceName, $candidateName);
                    $codeScore = max(0, 100 - ($codeDist * 15));
                    $matchType = ($codeDist <= 3 && $nameScore >= 60) ? 'both' : 'code';
                    $combinedScore = ($matchType === 'both') ? ($codeScore * 0.35) + ($nameScore * 0.65) : ($codeScore * 0.7) + ($nameScore * 0.3);

                    if ($combinedScore >= 30) {
                        $matches[] = [
                            'id_product' => $candidateId, 'product_name' => $candidateName,
                            'packname' => $masterProducts[$candidateId]['packname'] ?? '-',
                            'uom' => $masterProducts[$candidateId]['uom'] ?? '-',
                            'stock_system' => $candidateStock, 'match_type' => $matchType,
                            'code_distance' => $codeDist, 'name_score' => round($nameScore, 1),
                            'code_score' => round($codeScore, 1), 'combined_score' => round($combinedScore, 1),
                        ];
                        $seenIds[$candidateId] = true;
                    }
                }
            }

            // Layer 2: Name similarity
            foreach ($stockSystemData as $candidateId => $candidateStock) {
                if ($candidateId === $productId || $candidateStock <= 0 || isset($seenIds[$candidateId])) continue;
                $candidateName = $masterProducts[$candidateId]['product_name'] ?? '';
                if (empty($candidateName)) continue;

                $nameScore = $this->calculateNameSimilarity($sourceName, $candidateName);
                if ($nameScore >= 65) {
                    $checkCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($candidateId));
                    $codeDist = levenshtein($baseCode, $checkCode);
                    $codeScore = max(0, 100 - ($codeDist * 15));
                    $combinedScore = ($nameScore * 0.75) + ($codeScore * 0.25);

                    $matches[] = [
                        'id_product' => $candidateId, 'product_name' => $candidateName,
                        'packname' => $masterProducts[$candidateId]['packname'] ?? '-',
                        'uom' => $masterProducts[$candidateId]['uom'] ?? '-',
                        'stock_system' => $candidateStock, 'match_type' => 'name',
                        'code_distance' => $codeDist, 'name_score' => round($nameScore, 1),
                        'code_score' => round($codeScore, 1), 'combined_score' => round($combinedScore, 1),
                    ];
                }
            }

            usort($matches, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);
            $matches = array_slice($matches, 0, 10);

            if (!empty($matches)) {
                $best = $matches[0];
                $confidence = ($best['match_type'] === 'both' && $best['combined_score'] >= 70) ? 'Tinggi' : ($best['combined_score'] >= 55 ? 'Sedang' : 'Rendah');

                $analysis[] = [
                    'id_product' => $productId, 'product_name' => $sourceName,
                    'packname' => $sourcePackname, 'uom' => $sourceUom,
                    'stock_system' => $stockSystem, 's_qty' => $totalCounted,
                    's_karton' => 0, 's_pcs' => 0,
                    'matches' => $matches, 'match_count' => count($matches),
                    'best_confidence' => $confidence, 'best_match_type' => $best['match_type'],
                    'best_score' => $best['combined_score'],
                ];
            }
        }

        usort($analysis, function ($a, $b) {
            $order = ['Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 2];
            $diff = ($order[$a['best_confidence']] ?? 3) - ($order[$b['best_confidence']] ?? 3);
            return $diff !== 0 ? $diff : $b['best_score'] <=> $a['best_score'];
        });

        return $analysis;
    }
}
