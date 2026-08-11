<?php
// recount_single.php - Mode Single (S vs Stock System) dengan Assignment, Filter, Detail Modal & Edit/Delete
// Dilengkapi dengan fitur Analisa Produk Mirip (Hanya untuk stock_system=0 & ada input Team S)

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";

$activeSessionId = $_GET['session_id'] ?? $_SESSION['active_session'] ?? null;

if (!$activeSessionId) {
    header("Location: index.php");
    exit;
}

// Cek session (mode 'S' atau 'single')
$checkSession = $conn->prepare("SELECT * FROM opname_sessions WHERE session_id = ?");
$checkSession->bind_param("s", $activeSessionId);
$checkSession->execute();
$session = $checkSession->get_result()->fetch_assoc();
$checkSession->close();

if (!$session || !in_array($session['mode'], ['S', 'single'])) {
    die("<div class='p-4 text-center text-red-600'>Session tidak ditemukan atau bukan mode Single</div>");
}

$_SESSION['active_session'] = $activeSessionId;

// ============================================
// AMBIL DATA USER UNTUK COMBOBOX
// ============================================
$users = [];
$userQuery = $conn->query("SELECT user_id, name, email FROM user WHERE is_active = 1 ORDER BY name");
if ($userQuery) {
    while ($row = $userQuery->fetch_assoc()) {
        $users[] = $row;
    }
}

// ============================================
// AMBIL DATA COUNT (S, R1, R2, R3)
// ============================================
$stmt = $conn->prepare("
    SELECT 
        d.id_product,
        h.team,
        SUM(d.qty_karton) as qty_karton,
        SUM(d.qty_pcs) as qty_pcs,
        SUM(d.final_qty) as final_qty
    FROM opname_count_headers h
    JOIN opname_count_details d ON h.count_id = d.count_id
    WHERE h.session_id = ? AND h.team IN ('S', 'R1', 'R2', 'R3')
    GROUP BY d.id_product, h.team
");

$stmt->bind_param("s", $activeSessionId);
$stmt->execute();
$countResult = $stmt->get_result();

$countData = [];
while ($row = $countResult->fetch_assoc()) {
    $productId = $row['id_product'];
    $team = $row['team'];
    $countData[$productId][$team] = [
        'karton' => (int)$row['qty_karton'],
        'pcs' => (int)$row['qty_pcs'],
        'final_qty' => (int)$row['final_qty']
    ];
}
$stmt->close();

// ============================================
// AMBIL STOCK SYSTEM DARI OPNAME_PRODUCTS
// ============================================
$stockSystemData = [];
$stmtStock = $conn->prepare("
    SELECT id_product, stock_system 
    FROM opname_products 
    WHERE session_id = ?
");
$stmtStock->bind_param("s", $activeSessionId);
$stmtStock->execute();
$stockResult = $stmtStock->get_result();

while ($row = $stockResult->fetch_assoc()) {
    $stockSystemData[$row['id_product']] = (int)$row['stock_system'];
}
$stmtStock->close();

// ============================================
// AMBIL MASTER PRODUCT (Termasuk kolom principal)
// ============================================
$masterProducts = [];
$masterQuery = $conn->query("SELECT id_product, product_name, uom, packname, principal FROM master_products");
if ($masterQuery) {
    while ($row = $masterQuery->fetch_assoc()) {
        $masterProducts[$row['id_product']] = $row;
    }
}

// ============================================
// FUNGSI ANALISA PRODUK MIRIP (DUPLICATE/ERROR INPUT DETECTION)
// HANYA UNTUK KASUS: stock_system = 0 DAN ada input dari Team S
// ============================================
function findSimilarProducts($productId, $allStockData, $masterProducts) {
    $similarProducts = [];
    
    // Bersihkan kode dari karakter khusus
    $basePattern = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productId));
    
    foreach ($allStockData as $checkId => $stock) {
        if ($checkId === $productId) continue;
        
        $checkPattern = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($checkId));
        
        // Cek apakah kode mengandung pattern yang sama atau memiliki kemiripan
        if (strpos($checkPattern, $basePattern) === 0 || 
            strpos($basePattern, $checkPattern) === 0 ||
            levenshtein($basePattern, $checkPattern) <= 3) {
            
            // Hitung panjang perbedaan
            $diffLength = abs(strlen($checkPattern) - strlen($basePattern));
            $levDistance = levenshtein($basePattern, $checkPattern);
            
            // Jika perbedaan minimal (kemungkinan typo atau suffix)
            if ($diffLength <= 3 || $levDistance <= 3) {
                $similarProducts[] = [
                    'id_product' => $checkId,
                    'stock_system' => $stock,
                    'similarity_score' => min($diffLength, $levDistance),
                    'product_name' => $masterProducts[$checkId]['product_name'] ?? 'Unknown'
                ];
            }
        }
    }
    
    // Urutkan berdasarkan similarity score (terdekat dulu)
    usort($similarProducts, function($a, $b) {
        return $a['similarity_score'] - $b['similarity_score'];
    });
    
    return $similarProducts;
}

// ============================================
// FUNGSI ANALISA KEMIRIPAN NAMA PRODUK
// ============================================
function calculateNameSimilarity($name1, $name2) {
    // Normalisasi: lowercase, hapus karakter spesial, trim spasi ganda
    $n1 = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $name1))));
    $n2 = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $name2))));
    
    if (empty($n1) || empty($n2)) return 0;
    
    // 1. Character-level similarity (PHP built-in)
    similar_text($n1, $n2, $charPercent);
    
    // 2. Word-level Jaccard similarity
    $words1 = array_unique(explode(' ', $n1));
    $words2 = array_unique(explode(' ', $n2));
    $intersection = count(array_intersect($words1, $words2));
    $union = count(array_unique(array_merge($words1, $words2)));
    $jaccard = $union > 0 ? ($intersection / $union) * 100 : 0;
    
    // Combined score (60% character, 40% word)
    return ($charPercent * 0.6) + ($jaccard * 0.4);
}

function findSimilarByName($productId, $productName, $allStockData, $masterProducts, $minScore = 70) {
    $results = [];
    
    foreach ($allStockData as $candidateId => $candidateStock) {
        if ($candidateId === $productId) continue;
        if ($candidateStock <= 0) continue; // Hanya bandingkan dengan yang punya stok
        
        $candidateName = $masterProducts[$candidateId]['product_name'] ?? '';
        if (empty($candidateName)) continue;
        
        $nameScore = calculateNameSimilarity($productName, $candidateName);
        
        if ($nameScore >= $minScore) {
            $results[] = [
                'id_product' => $candidateId,
                'product_name' => $candidateName,
                'packname' => $masterProducts[$candidateId]['packname'] ?? '-',
                'uom' => $masterProducts[$candidateId]['uom'] ?? '-',
                'stock_system' => $candidateStock,
                'name_score' => round($nameScore, 1)
            ];
        }
    }
    
    usort($results, fn($a, $b) => $b['name_score'] <=> $a['name_score']);
    return $results;
}

function buildComprehensiveAnalysis($stockSystemData, $countData, $masterProducts) {
    $analysis = [];
    
    foreach ($stockSystemData as $productId => $stockSystem) {
        if ($stockSystem != 0) continue;
        
        $teams = $countData[$productId] ?? [];
        $teamS = $teams['S'] ?? null;
        if (!$teamS || $teamS['final_qty'] <= 0) continue;
        
        $sourceName = $masterProducts[$productId]['product_name'] ?? 'Unknown';
        $sourcePackname = $masterProducts[$productId]['packname'] ?? '-';
        $sourceUom = $masterProducts[$productId]['uom'] ?? '-';
        
        $matches = [];
        $seenIds = [];
        
        // --- Layer 1: Code similarity ---
        $baseCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productId));
        
        foreach ($stockSystemData as $candidateId => $candidateStock) {
            if ($candidateId === $productId || $candidateStock <= 0) continue;
            
            $checkCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($candidateId));
            $codeDist = levenshtein($baseCode, $checkCode);
            
            if ($codeDist <= 5) {
                $candidateName = $masterProducts[$candidateId]['product_name'] ?? 'Unknown';
                $nameScore = calculateNameSimilarity($sourceName, $candidateName);
                $codeScore = max(0, 100 - ($codeDist * 15));
                
                $matchType = ($codeDist <= 3 && $nameScore >= 60) ? 'both' : 'code';
                $combinedScore = ($matchType === 'both') 
                    ? ($codeScore * 0.35) + ($nameScore * 0.65) 
                    : ($codeScore * 0.7) + ($nameScore * 0.3);
                
                if ($combinedScore >= 30) {
                    $matches[] = [
                        'id_product' => $candidateId,
                        'product_name' => $candidateName,
                        'packname' => $masterProducts[$candidateId]['packname'] ?? '-',
                        'uom' => $masterProducts[$candidateId]['uom'] ?? '-',
                        'stock_system' => $candidateStock,
                        'match_type' => $matchType,
                        'code_distance' => $codeDist,
                        'name_score' => round($nameScore, 1),
                        'code_score' => round($codeScore, 1),
                        'combined_score' => round($combinedScore, 1)
                    ];
                    $seenIds[$candidateId] = true;
                }
            }
        }
        
        // --- Layer 2: Name similarity (only for candidates not already found) ---
        foreach ($stockSystemData as $candidateId => $candidateStock) {
            if ($candidateId === $productId || $candidateStock <= 0) continue;
            if (isset($seenIds[$candidateId])) continue;
            
            $candidateName = $masterProducts[$candidateId]['product_name'] ?? '';
            if (empty($candidateName)) continue;
            
            $nameScore = calculateNameSimilarity($sourceName, $candidateName);
            
            if ($nameScore >= 65) {
                $checkCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($candidateId));
                $codeDist = levenshtein($baseCode, $checkCode);
                $codeScore = max(0, 100 - ($codeDist * 15));
                
                $combinedScore = ($nameScore * 0.75) + ($codeScore * 0.25);
                
                $matches[] = [
                    'id_product' => $candidateId,
                    'product_name' => $candidateName,
                    'packname' => $masterProducts[$candidateId]['packname'] ?? '-',
                    'uom' => $masterProducts[$candidateId]['uom'] ?? '-',
                    'stock_system' => $candidateStock,
                    'match_type' => 'name',
                    'code_distance' => $codeDist,
                    'name_score' => round($nameScore, 1),
                    'code_score' => round($codeScore, 1),
                    'combined_score' => round($combinedScore, 1)
                ];
            }
        }
        
        // Sort by combined score descending
        usort($matches, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);
        
        // Take top 10 only
        $matches = array_slice($matches, 0, 10);
        
        if (!empty($matches)) {
            // Determine confidence
            $best = $matches[0];
            if ($best['match_type'] === 'both' && $best['combined_score'] >= 70) {
                $confidence = 'Tinggi';
            } elseif ($best['combined_score'] >= 55) {
                $confidence = 'Sedang';
            } else {
                $confidence = 'Rendah';
            }
            
            $analysis[] = [
                'id_product' => $productId,
                'product_name' => $sourceName,
                'packname' => $sourcePackname,
                'uom' => $sourceUom,
                'stock_system' => $stockSystem,
                's_qty' => $teamS['final_qty'],
                's_karton' => $teamS['karton'],
                's_pcs' => $teamS['pcs'],
                'matches' => $matches,
                'match_count' => count($matches),
                'best_confidence' => $confidence,
                'best_match_type' => $best['match_type'],
                'best_score' => $best['combined_score']
            ];
        }
    }
    
    // Sort: Tinggi first, then Sedang, then Rendah
    usort($analysis, function($a, $b) {
        $order = ['Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 2];
        $diff = ($order[$a['best_confidence']] ?? 3) - ($order[$b['best_confidence']] ?? 3);
        return $diff !== 0 ? $diff : $b['best_score'] <=> $a['best_score'];
    });
    
    return $analysis;
}

// ============================================
// FUNCTION LOGIKA SINGLE
// ============================================
function calculateSingleResult($teamS, $stockSystem, $teamR1, $teamR2, $teamR3) {
    $sTotal = isset($teamS['final_qty']) ? $teamS['final_qty'] : null;
    $r1Total = isset($teamR1['final_qty']) ? $teamR1['final_qty'] : null;
    $r2Total = isset($teamR2['final_qty']) ? $teamR2['final_qty'] : null;
    $r3Total = isset($teamR3['final_qty']) ? $teamR3['final_qty'] : null;
    
    // Step 5: Jika ada R3 -> FINAL R3 (apapun hasilnya)
    if ($r3Total !== null) {
        return ['stage' => 'final', 'label' => 'FINAL R3', 'final_total' => $r3Total, 'can_assign' => false, 'round_to_assign' => null];
    }
    
    // Step 5b: Jika ada R2 -> FINAL R2
    if ($r2Total !== null) {
        return ['stage' => 'final', 'label' => 'FINAL R2', 'final_total' => $r2Total, 'can_assign' => false, 'round_to_assign' => null];
    }
    
    // Step 1: Jika S == stock_system -> MATCH
    if ($sTotal !== null && $sTotal == $stockSystem) {
        return ['stage' => 'final', 'label' => 'MATCH', 'final_total' => $sTotal, 'can_assign' => false, 'round_to_assign' => null];
    }
    
    // Step 3: Jika ada R1 dan R1 match dengan S atau stock_system -> FINAL R1
    if ($r1Total !== null) {
        if ($r1Total == $sTotal || $r1Total == $stockSystem) {
            return ['stage' => 'final', 'label' => 'FINAL R1', 'final_total' => $r1Total, 'can_assign' => false, 'round_to_assign' => null];
        }
        // Step 4: Jika R1 tidak match -> NEED R2
        return ['stage' => 'need_r2', 'label' => 'NEED R2', 'final_total' => null, 'can_assign' => true, 'round_to_assign' => 3];
    }
    
    // Step 2: Jika S ada tapi tidak match -> NEED R1
    if ($sTotal !== null && $sTotal != $stockSystem) {
        return ['stage' => 'need_r1', 'label' => 'NEED R1', 'final_total' => null, 'can_assign' => true, 'round_to_assign' => 2];
    }
    
    // Belum ada data
    return ['stage' => 'waiting', 'label' => 'WAITING', 'final_total' => null, 'can_assign' => false, 'round_to_assign' => null];
}

// ============================================
// BUILD DATA DENGAN ANALISA
// ============================================
$summaryData = [];
$index = 1;

foreach ($stockSystemData as $productId => $stockSystem) {
    $teams = isset($countData[$productId]) ? $countData[$productId] : [];
    
    $teamS = $teams['S'] ?? null;
    $teamR1 = $teams['R1'] ?? null;
    $teamR2 = $teams['R2'] ?? null;
    $teamR3 = $teams['R3'] ?? null;
    
    $result = calculateSingleResult($teamS, $stockSystem, $teamR1, $teamR2, $teamR3);
    
    $master = $masterProducts[$productId] ?? null;
    $productName = $master['product_name'] ?? 'Unknown';
    $uom = $master['uom'] ?? '-';
    $packname = $master['packname'] ?? '-';
    $principal = $master['principal'] ?? '-';
    
    // CEK KONDISI UNTUK ANALISA:
    // HANYA jika stock_system == 0 DAN ada input dari Team S (qty > 0)
    $hasInputS = ($teamS && $teamS['final_qty'] > 0);
    $analisis = null;
    
    if ($stockSystem == 0 && $hasInputS) {
        // Cari produk dengan kode mirip yang memiliki stock > 0
        $similar = findSimilarProducts($productId, $stockSystemData, $masterProducts);
        $similarProductsWithStock = [];
        
        foreach ($similar as $sim) {
            if ($sim['stock_system'] > 0) {
                $similarProductsWithStock[] = $sim;
            }
        }
        
        if (!empty($similarProductsWithStock)) {
            // Ada produk mirip dengan stock > 0 -> Kemungkinan besar salah kode
            $analisis = [
                'has_suggestion' => true,
                'message' => '⚠️ PERHATIAN: Stok sistem 0, tetapi tim opname menghitung ' . number_format($teamS['final_qty']) . ' unit. Kemungkinan kesalahan input kode produk!',
                'suggestions' => $similarProductsWithStock,
                'type' => 'possible_typo',
                's_qty' => $teamS['final_qty']
            ];
        } elseif (!empty($similar)) {
            // Ada produk mirip tapi stock 0 juga -> Informasi saja
            $analisis = [
                'has_suggestion' => true,
                'message' => 'ℹ️ INFO: Stok sistem 0, tetapi ada input dari Team S (' . number_format($teamS['final_qty']) . ' unit). Ditemukan kode produk mirip (stok 0 juga)',
                'suggestions' => $similar,
                'type' => 'similar_code',
                's_qty' => $teamS['final_qty']
            ];
        } else {
            // Tidak ada produk mirip, tapi tetap ada keanehan (stok 0 tapi dihitung)
            $analisis = [
                'has_suggestion' => true,
                'message' => '⚠️ ANOMALI: Stok sistem 0, tetapi tim opname menghitung ' . number_format($teamS['final_qty']) . ' unit. Tidak ditemukan kode produk mirip. Perlu investigasi fisik!',
                'suggestions' => [],
                'type' => 'anomaly',
                's_qty' => $teamS['final_qty']
            ];
        }
    }
    
    // Hitung stock_system karton dan pcs berdasarkan UOM
    $uomVal = (int)$uom;
    if ($uomVal > 0) {
        $ssKarton = intdiv($stockSystem, $uomVal);
        $ssPcs = $stockSystem % $uomVal;
    } else {
        $ssKarton = 0;
        $ssPcs = $stockSystem;
    }
    
    $summaryData[] = [
        'no' => $index++,
        'id_product' => $productId,
        'product_name' => $productName,
        'packname' => $packname,
        'uom' => $uom,
        'principal' => $principal,
        'stock_system' => $stockSystem,
        'stock_system_karton' => $ssKarton,
        'stock_system_pcs' => $ssPcs,
        
        's_karton' => $teamS ? $teamS['karton'] : '-',
        's_pcs' => $teamS ? $teamS['pcs'] : '-',
        's_total' => $teamS ? $teamS['final_qty'] : '-',
        
        'r1_karton' => $teamR1 ? $teamR1['karton'] : '-',
        'r1_pcs' => $teamR1 ? $teamR1['pcs'] : '-',
        'r1_total' => $teamR1 ? $teamR1['final_qty'] : '-',
        
        'r2_karton' => $teamR2 ? $teamR2['karton'] : '-',
        'r2_pcs' => $teamR2 ? $teamR2['pcs'] : '-',
        'r2_total' => $teamR2 ? $teamR2['final_qty'] : '-',
        
        'r3_karton' => $teamR3 ? $teamR3['karton'] : '-',
        'r3_pcs' => $teamR3 ? $teamR3['pcs'] : '-',
        'r3_total' => $teamR3 ? $teamR3['final_qty'] : '-',
        
        'result_stage' => $result['stage'],
        'result_label' => $result['label'],
        'result_total' => $result['final_total'],
        'can_assign' => $result['can_assign'],
        'round_to_assign' => $result['round_to_assign'],
        'analisis' => $analisis,
        'has_analisis' => $analisis !== null
    ];
}

// Cari daftar unique principal untuk dropdown filter
$principals = [];
foreach ($summaryData as $row) {
    if (!empty($row['principal']) && $row['principal'] !== '-') {
        $principals[] = $row['principal'];
    }
}
$principals = array_unique($principals);
sort($principals);

// Statistik
$totalData = count($summaryData);
$finalData = count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'final'));
$needR1 = count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'need_r1'));
$needR2 = count(array_filter($summaryData, fn($d) => $d['result_stage'] === 'need_r2'));
$hasAnalisa = count(array_filter($summaryData, fn($d) => $d['has_analisis'] === true));
$possibleTypo = count(array_filter($summaryData, fn($d) => $d['analisis'] && $d['analisis']['type'] === 'possible_typo'));
$anomalyCount = count(array_filter($summaryData, fn($d) => $d['analisis'] && $d['analisis']['type'] === 'anomaly'));

// ============================================
// BUILD COMPREHENSIVE ANALYSIS DATA
// ============================================
$comprehensiveAnalysis = buildComprehensiveAnalysis($stockSystemData, $countData, $masterProducts);
$totalAnalysis = count($comprehensiveAnalysis);
$highConfidence = count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Tinggi'));
$medConfidence = count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Sedang'));
$lowConfidence = count(array_filter($comprehensiveAnalysis, fn($a) => $a['best_confidence'] === 'Rendah'));
$codeMatches = count(array_filter($comprehensiveAnalysis, fn($a) => in_array($a['best_match_type'], ['code', 'both'])));
$nameMatches = count(array_filter($comprehensiveAnalysis, fn($a) => in_array($a['best_match_type'], ['name', 'both'])));

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Mode - <?php echo htmlspecialchars($activeSessionId); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .badge-final { background: #10B981; color: white; }
        .badge-need_r1 { background: #F59E0B; color: white; }
        .badge-need_r2 { background: #EF4444; color: white; }
        .badge-waiting { background: #9CA3AF; color: white; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .loading {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #10B981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Animasi untuk tooltip */
        .analisa-tooltip {
            transition: all 0.2s ease;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        /* Analysis Modal Styles */
        .analysis-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .analysis-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .analysis-card.confidence-tinggi { border-left-color: #EF4444; }
        .analysis-card.confidence-sedang { border-left-color: #F59E0B; }
        .analysis-card.confidence-rendah { border-left-color: #6B7280; }
        .match-badge-both { background: linear-gradient(135deg, #7C3AED, #2563EB); color: white; }
        .match-badge-code { background: #2563EB; color: white; }
        .match-badge-name { background: #7C3AED; color: white; }
        .confidence-badge-tinggi { background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; }
        .confidence-badge-sedang { background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; }
        .confidence-badge-rendah { background: #F3F4F6; color: #6B7280; border: 1px solid #E5E7EB; }
        .score-bar { height: 6px; border-radius: 3px; background: #E5E7EB; overflow: hidden; }
        .score-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
        .recommendation-row { transition: background 0.15s ease; }
        .recommendation-row:hover { background: #F0FDF4 !important; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .analysis-card-animate {
            animation: slideUp 0.3s ease forwards;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6 max-w-full">
        <!-- Header -->
        <div class="py-2 mb-4 text-slate-800">
            <div class="flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 text-[10px] font-bold tracking-wider rounded bg-emerald-100 text-emerald-800 uppercase">Single Mode</span>
                    <h1 class="text-base font-bold">Session: <span class="font-mono text-emerald-700"><?php echo htmlspecialchars($activeSessionId); ?></span></h1>
                    <div class="hidden md:flex items-center gap-3 text-xs text-slate-500 border-l border-gray-300 pl-3">
                        <span>Total SKU: <strong class="text-slate-700"><?php echo $totalData; ?></strong></span>
                        <span>Final: <strong class="text-emerald-600"><?php echo $finalData; ?></strong></span>
                        <span>Need R1: <strong class="text-amber-600"><?php echo $needR1; ?></strong></span>
                        <span>Need R2: <strong class="text-red-600"><?php echo $needR2; ?></strong></span>
                        <?php if ($hasAnalisa > 0): ?>
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                            Analisa: <strong class="text-orange-600"><?php echo $hasAnalisa; ?></strong>
                        </span>
                        <?php endif; ?>
                        <?php if ($possibleTypo > 0): ?>
                        <span class="text-red-600 font-bold">❗ Potensi Typo: <?php echo $possibleTypo; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex md:hidden items-center gap-2 text-[10px] text-slate-500 mr-2">
                        <span>SKU: <?php echo $totalData; ?></span> •
                        <span>F: <?php echo $finalData; ?></span> •
                        <span>R1: <?php echo $needR1; ?></span> •
                        <span>R2: <?php echo $needR2; ?></span>
                    </div>
                    <a href="index.php" class="px-3 py-1.5 border border-gray-300 hover:bg-gray-100 rounded-lg text-xs font-semibold text-slate-700 bg-white transition">← Pilih Session</a>
                </div>
            </div>
        </div>

        <!-- Actions & Filters Bar -->
        <div class="flex flex-wrap items-center gap-4 mb-4 text-sm bg-white p-3 rounded-lg border border-gray-200">
            <!-- Filter Principal -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-gray-500">Principal:</span>
                <select id="principalFilter" class="px-2 py-1 border border-gray-300 rounded-md bg-white text-xs min-w-[140px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Principal</option>
                    <?php foreach ($principals as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-gray-500">Status:</span>
                <select id="statusFilter" class="px-2 py-1 border border-gray-300 rounded-md bg-white text-xs min-w-[120px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="MATCH">MATCH</option>
                    <option value="NEED R1">NEED R1</option>
                    <option value="NEED R2">NEED R2</option>
                    <option value="WAITING">WAITING</option>
                    <option value="FINAL R1">FINAL R1</option>
                    <option value="FINAL R2">FINAL R2</option>
                    <option value="FINAL R3">FINAL R3</option>
                </select>
            </div>

            <!-- Filter Analisa -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-gray-500">Analisa AI:</span>
                <select id="analisaFilter" class="px-2 py-1 border border-gray-300 rounded-md bg-white text-xs min-w-[160px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua</option>
                    <option value="has_analisa">⚠️ Ada Anomali</option>
                    <option value="possible_typo">❗ Analisa</option>
                    <option value="anomaly">🔍 Perlu Investigasi</option>
                </select>
            </div>

            <!-- Vertical Divider -->
            <div class="h-5 w-px bg-gray-200 hidden md:block"></div>

            <!-- Assign Combobox + Button -->
            <div class="flex items-center gap-2">
                <select id="userSelect" class="px-2 py-1 border border-gray-300 rounded-md bg-white text-xs min-w-[140px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button id="assignBtn" class="px-4 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-md text-xs transition disabled:bg-emerald-300 disabled:cursor-not-allowed">
                    <span id="assignBtnText">Assign</span>
                    <span id="assignBtnLoading" class="hidden"><span class="loading"></span></span>
                </button>
            </div>

            <!-- Selected Counter -->
            <div class="text-xs text-gray-500">
                <span id="selectedCount" class="font-bold text-emerald-600">0</span> dipilih
            </div>

            <!-- Vertical Divider -->
            <div class="h-5 w-px bg-gray-200 hidden md:block"></div>

            <!-- Tombol Analisa Produk -->
            <?php if ($totalAnalysis > 0): ?>
            <div class="md:ml-auto">
                <button id="openAnalysisModal" class="relative inline-flex items-center gap-2 px-4 py-1.5 bg-gradient-to-r from-violet-600 to-blue-600 hover:from-violet-700 hover:to-blue-700 text-white font-semibold rounded-lg text-xs transition shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    🔍 Analisa Kemiripan Produk
                    <span class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-[10px] font-bold rounded-full bg-red-500 text-white shadow animate-pulse"><?php echo $totalAnalysis; ?></span>
                </button>
            </div>
            <?php else: ?>
            <div class="md:ml-auto">
                <span class="text-xs text-gray-400 italic">✅ Tidak ada anomali terdeteksi</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- DataTable -->
        <div class="bg-white rounded-xl shadow-lg p-6 overflow-x-auto">
            <table id="singleTable" class="display stripe hover" style="width:100%">
                <thead>
                    <tr class="bg-gray-50">
                        <th rowspan="2" width="40">Pilih</th>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Product ID</th>
                        <th rowspan="2">Principal</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">Packname</th>
                        <th rowspan="2">UOM</th>
                        <th colspan="3">Stock System</th>
                        <th colspan="3">Team S</th>
                        <th rowspan="2" width="180">Analisa AI</th>
                        <th colspan="3">Recount 1 (R1)</th>
                        <th colspan="3">Recount 2 (R2)</th>
                        <th colspan="3">Recount 3 (R3)</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Result</th>
                        
                        <th rowspan="2" width="50">Aksi</th>
                    </tr>
                    <tr class="bg-gray-100 text-xs">
                        <th>Krt</th><th>Pcs</th><th>Total</th>
                        <th>Krt</th><th>Pcs</th><th>Total</th>
                        <th>Krt</th><th>Pcs</th><th>Total</th>
                        <th>Krt</th><th>Pcs</th><th>Total</th>
                        <th>Krt</th><th>Pcs</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summaryData as $row): 
                        $checkboxValue = $row['id_product'] . '|' . $row['round_to_assign'];
                        $canAssign = $row['can_assign'] && $row['round_to_assign'] !== null;
                        $assignRound = $row['round_to_assign'] == 2 ? 'R1' : ($row['round_to_assign'] == 3 ? 'R2' : '');
                        $analisisType = $row['analisis']['type'] ?? '';
                        $hasInputS = ($row['s_total'] !== '-' && $row['s_total'] > 0);
                    ?>
                    <tr class="<?php echo strpos($row['result_stage'], 'final') === 0 ? 'bg-green-50' : ''; ?>
                               <?php echo ($row['stock_system'] == 0 && $hasInputS) ? 'border-l-4 border-orange-400' : ''; ?>"
                        data-stage="<?php echo $row['result_stage']; ?>"
                        data-can-assign="<?php echo $canAssign ? 'true' : 'false'; ?>"
                        data-analisa-type="<?php echo $analisisType; ?>"
                        data-stock-system="<?php echo $row['stock_system']; ?>"
                        data-s-total="<?php echo $row['s_total']; ?>">
                        
                        <!-- Checkbox -->
                        <td class="text-center">
                            <?php if ($canAssign): ?>
                                <input type="checkbox" 
                                       class="product-checkbox w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                       value="<?php echo htmlspecialchars($checkboxValue); ?>"
                                       data-product="<?php echo htmlspecialchars($row['id_product']); ?>"
                                       data-round="<?php echo $row['round_to_assign']; ?>">
                                <?php if ($assignRound): ?>
                                    <span class="text-[9px] text-gray-400 block">(<?php echo $assignRound; ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 bg-gray-100" disabled>
                            <?php endif; ?>
                        </td>

                        <td class="text-center"><?php echo $row['no']; ?></td>
                        <td class="font-medium <?php echo ($row['stock_system'] == 0 && $hasInputS) ? 'text-orange-700 font-bold' : ''; ?>">
                            <?php echo htmlspecialchars($row['id_product']); ?>
                        </td>
                        <td class="text-slate-600 font-medium"><?php echo htmlspecialchars($row['principal']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['product_name'], 0, 50)); ?></td>
                        <td><?php echo htmlspecialchars($row['packname']); ?></td>
                        <td class="text-center"><?php echo $row['uom']; ?></td>
                        <td class="text-right <?php echo $row['stock_system'] == 0 ? 'bg-red-50 text-red-400' : ''; ?>">
                            <?php echo number_format($row['stock_system_karton']); ?>
                        </td>
                        <td class="text-right <?php echo $row['stock_system'] == 0 ? 'bg-red-50 text-red-400' : ''; ?>">
                            <?php echo number_format($row['stock_system_pcs']); ?>
                        </td>
                        <td class="text-right font-medium <?php echo $row['stock_system'] == 0 ? 'bg-red-100 text-red-700 font-bold' : ''; ?>">
                            <?php echo number_format($row['stock_system']); ?>
                        </td>
                        
                        <td class="text-right"><?php echo $row['s_karton'] !== '-' ? number_format($row['s_karton']) : '-'; ?></td>
                        <td class="text-right"><?php echo $row['s_pcs'] !== '-' ? number_format($row['s_pcs']) : '-'; ?></td>
                        <td class="text-right font-medium <?php echo ($row['stock_system'] == 0 && $hasInputS) ? 'text-orange-600 font-bold' : ''; ?>">
                            <?php echo $row['s_total'] !== '-' ? number_format($row['s_total']) : '-'; ?>
                        </td>

                        <!-- Kolom Analisa AI -->
                        <td class="text-left">
                            <?php if ($row['has_analisis'] && $row['analisis']): ?>
                                <div class="relative inline-block w-full">
                                    <div class="analisa-trigger flex items-center gap-1 cursor-pointer 
                                        <?php 
                                        if ($row['analisis']['type'] == 'possible_typo') echo 'text-red-600 bg-red-50 border-red-200 animate-pulse-slow';
                                        elseif ($row['analisis']['type'] == 'anomaly') echo 'text-orange-600 bg-orange-50 border-orange-200';
                                        else echo 'text-yellow-600 bg-yellow-50 border-yellow-200';
                                        ?> 
                                        px-2 py-1 rounded text-xs font-semibold border w-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php if ($row['analisis']['type'] == 'possible_typo'): ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            <?php elseif ($row['analisis']['type'] == 'anomaly'): ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            <?php else: ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            <?php endif; ?>
                                        </svg>
                                        <span>
                                            <?php 
                                            if ($row['analisis']['type'] == 'possible_typo') echo '🔍 Analisa';
                                            elseif ($row['analisis']['type'] == 'anomaly') echo '⚠️ Anomali Stok';
                                            else echo 'ℹ️ Info Kode Mirip';
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Tooltip/Suggestion Popup -->
                                    <div class="analisa-tooltip hidden absolute z-50 bg-white border border-gray-200 rounded-lg shadow-xl p-3 min-w-[300px] left-0 top-full mt-1">
                                        <div class="flex items-start gap-2 mb-3">
                                            <div class="flex-shrink-0">
                                                <?php if ($row['analisis']['type'] == 'possible_typo'): ?>
                                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                    </div>
                                                <?php elseif ($row['analisis']['type'] == 'anomaly'): ?>
                                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-xs font-bold <?php 
                                                    if ($row['analisis']['type'] == 'possible_typo') echo 'text-red-600';
                                                    elseif ($row['analisis']['type'] == 'anomaly') echo 'text-orange-600';
                                                    else echo 'text-yellow-600';
                                                ?>">
                                                    <?php echo $row['analisis']['message']; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($row['analisis']['suggestions'])): ?>
                                            <div class="text-xs text-gray-600 mb-2 font-semibold">💡 Rekomendasi:</div>
                                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                                <?php foreach ($row['analisis']['suggestions'] as $suggestion): ?>
                                                    <div class="flex justify-between items-center text-xs p-2 hover:bg-gray-50 rounded border border-gray-100">
                                                        <div>
                                                            <code class="font-mono font-bold text-gray-800"><?php echo htmlspecialchars($suggestion['id_product']); ?></code>
                                                            <?php if (isset($suggestion['product_name'])): ?>
                                                                <div class="text-gray-500 text-[10px]"><?php echo htmlspecialchars(substr($suggestion['product_name'], 0, 40)); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-green-600 font-semibold">
                                                                Stock: <?php echo number_format($suggestion['stock_system']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3 pt-2 border-t border-gray-100 text-[11px] text-gray-500 flex items-start gap-1">
                                            <span>🤖</span>
                                            <span>Analisa AI: Terdeteksi anomali input. Harap verifikasi kebenaran kode produk yang diinput.</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
                                    // Hover effect untuk tooltip
                                    document.querySelectorAll('.analisa-trigger').forEach(trigger => {
                                        const tooltip = trigger.nextElementSibling;
                                        if (tooltip && tooltip.classList.contains('analisa-tooltip')) {
                                            trigger.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
                                            trigger.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
                                            tooltip.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
                                            tooltip.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
                                        }
                                    });
                                </script>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="text-right"><?php echo $row['r1_karton'] !== '-' ? number_format($row['r1_karton']) : '-'; ?></td>
                        <td class="text-right"><?php echo $row['r1_pcs'] !== '-' ? number_format($row['r1_pcs']) : '-'; ?></td>
                        <td class="text-right"><?php echo $row['r1_total'] !== '-' ? number_format($row['r1_total']) : '-'; ?></td>
                        
                        <td class="text-right"><?php echo $row['r2_karton'] !== '-' ? number_format($row['r2_karton']) : '-'; ?></td>
                        <td class="text-right"><?php echo $row['r2_pcs'] !== '-' ? number_format($row['r2_pcs']) : '-'; ?></td>
                        <td class="text-right font-medium"><?php echo $row['r2_total'] !== '-' ? number_format($row['r2_total']) : '-'; ?></td>
                        
                        <td class="text-right"><?php echo $row['r3_karton'] !== '-' ? number_format($row['r3_karton']) : '-'; ?></td>
                        <td class="text-right"><?php echo $row['r3_pcs'] !== '-' ? number_format($row['r3_pcs']) : '-'; ?></td>
                        <td class="text-right font-medium"><?php echo $row['r3_total'] !== '-' ? number_format($row['r3_total']) : '-'; ?></td>
                        
                        <td><span class="inline-block px-2 py-1 text-xs font-semibold rounded-full badge-<?php echo $row['result_stage']; ?>"><?php echo $row['result_label']; ?></span></td>
                        <td class="text-right font-bold <?php echo $row['result_total'] ? 'text-green-600' : 'text-gray-500'; ?>"><?php echo $row['result_total'] ? number_format($row['result_total']) : '-'; ?></td>
                        <!-- Eye details button -->
                        <td class="text-center">
                            <button type="button" class="btn-detail text-emerald-600 hover:text-emerald-800 transition" data-product="<?php echo htmlspecialchars($row['id_product']); ?>">
                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Histori Hitung -->
    <div id="detailModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl overflow-hidden mx-4 max-h-[85vh] flex flex-col">
            <div class="px-6 py-4 bg-emerald-900 text-white flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-xl">Histori Perhitungan Produk</h3>
                    <p class="text-sm text-emerald-200" id="modalSubTitle"></p>
                </div>
                <button id="closeModalBtn" class="text-white hover:text-emerald-200 font-bold text-2xl">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-12 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-100 text-sm">
                    <div class="col-span-1 sm:col-span-2">
                        <span class="text-slate-500 text-xs">Principal</span>
                        <p class="font-bold text-slate-800 text-base truncate" id="mPrincipal"></p>
                    </div>
                    <div class="col-span-2 sm:col-span-6">
                        <span class="text-slate-500 text-xs">Nama Produk</span>
                        <p class="font-bold text-slate-800 text-base truncate" id="mProdName" title=""></p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <span class="text-slate-500 text-xs">Packname</span>
                        <p class="font-bold text-slate-800 text-base truncate" id="mPackname"></p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <span class="text-slate-500 text-xs">UOM</span>
                        <p class="font-bold text-slate-800 text-base truncate" id="mUom"></p>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold text-sm text-slate-800 mb-2">Detail Hitung Sesi (Per Lokasi & Putaran)</h4>
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-sm text-left" id="modalTable">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3" width="180">Product ID</th>
                                    <th class="px-4 py-3">Lokasi</th>
                                    <th class="px-4 py-3">Tim/Putaran</th>
                                    <th class="px-4 py-3 text-right" width="100">Karton</th>
                                    <th class="px-4 py-3 text-right" width="100">Pcs</th>
                                    <th class="px-4 py-3 text-right" width="120">Total Qty (Pcs)</th>
                                    <th class="px-4 py-3">Waktu Hitung</th>
                                    <th class="px-4 py-3 text-center" width="140">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modalTableBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button id="closeModalBtn2" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL ANALISA KEMIRIPAN PRODUK (BESAR) -->
    <!-- ============================================ -->
    <div id="analysisModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center hidden">
        <div class="bg-gray-50 rounded-2xl shadow-2xl w-full max-w-7xl overflow-hidden mx-4 max-h-[92vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-violet-900 via-blue-900 to-indigo-900 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur">
                                <svg class="w-6 h-6 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl">Analisa Kemiripan Produk</h3>
                                <p class="text-sm text-blue-200">Rekomendasi koreksi berdasarkan kode & nama produk</p>
                            </div>
                        </div>
                    </div>
                    <button id="closeAnalysisBtn" class="text-white/70 hover:text-white transition text-3xl leading-none">&times;</button>
                </div>
            </div>
            
            <!-- Stats Bar -->
            <div class="px-6 py-3 bg-white border-b border-gray-200">
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        <span class="text-gray-500">Total Anomali:</span>
                        <span class="font-bold text-violet-700"><?php echo $totalAnalysis; ?></span>
                    </div>
                    <div class="h-4 w-px bg-gray-200"></div>
                    <?php if ($highConfidence > 0): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        <span class="text-gray-500">Confidence Tinggi:</span>
                        <span class="font-bold text-red-600"><?php echo $highConfidence; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($medConfidence > 0): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-gray-500">Confidence Sedang:</span>
                        <span class="font-bold text-amber-600"><?php echo $medConfidence; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($lowConfidence > 0): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        <span class="text-gray-500">Confidence Rendah:</span>
                        <span class="font-bold text-gray-600"><?php echo $lowConfidence; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="h-4 w-px bg-gray-200"></div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold match-badge-code">KODE</span>
                        <span class="text-gray-500"><?php echo $codeMatches; ?></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold match-badge-name">NAMA</span>
                        <span class="text-gray-500"><?php echo $nameMatches; ?></span>
                    </div>
                    
                    <!-- Filter -->
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-gray-400">Filter:</span>
                        <select id="analysisConfidenceFilter" class="px-2 py-1 border border-gray-300 rounded text-xs bg-white">
                            <option value="">Semua Level</option>
                            <option value="Tinggi">🔴 Tinggi</option>
                            <option value="Sedang">🟡 Sedang</option>
                            <option value="Rendah">⚪ Rendah</option>
                        </select>
                        <select id="analysisTypeFilter" class="px-2 py-1 border border-gray-300 rounded text-xs bg-white">
                            <option value="">Semua Tipe</option>
                            <option value="both">Kode + Nama</option>
                            <option value="code">Hanya Kode</option>
                            <option value="name">Hanya Nama</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="px-6 py-2 bg-blue-50 border-b border-blue-100">
                <div class="flex flex-wrap items-center gap-4 text-[11px] text-blue-700">
                    <span class="font-semibold">ℹ️ Cara Baca:</span>
                    <span>Produk dengan <strong>stok sistem = 0</strong> tetapi <strong>ada input fisik</strong> akan dicocokkan dengan produk lain yang memiliki kode atau nama mirip.</span>
                    <span>•</span>
                    <span><strong class="text-violet-600">NAMA</strong> = kecocokan nama produk &nbsp;|&nbsp; <strong class="text-blue-600">KODE</strong> = kecocokan kode produk &nbsp;|&nbsp; <strong class="bg-gradient-to-r from-violet-600 to-blue-600 bg-clip-text text-transparent">KODE+NAMA</strong> = keduanya cocok</span>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="analysisContent">
                <!-- Cards will be rendered by JavaScript -->
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
                <div class="text-xs text-gray-400 flex items-center gap-2">
                    <span>🤖</span>
                    <span>Analisa otomatis berdasarkan Levenshtein Distance (kode) + Similar Text & Jaccard Similarity (nama)</span>
                </div>
                <button id="closeAnalysisBtn2" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Data JSON untuk Analysis Modal -->
    <script>
        const analysisData = <?php echo json_encode($comprehensiveAnalysis, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <script>
        $(document).ready(function() {
            const sessionId = '<?php echo $activeSessionId; ?>';
            let currentUom = 1;
            let needsMainPageReload = false;

            var table = $('#singleTable').DataTable({
                pageLength: 25,
                scrollX: true,
                autoWidth: false,
                language: {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "zeroRecords": "Tidak ada data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                order: [[1, 'asc']],
                drawCallback: function() {
                    updateSelectedCount();
                    attachCheckboxEvents();
                }
            });

            // Filter
            $('#principalFilter').on('change', function() {
                const val = $(this).val();
                table.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', val ? true : false, false).draw();
            });

            $('#statusFilter').on('change', function() {
                const val = $(this).val();
                table.column(22).search(val ? $.fn.dataTable.util.escapeRegex(val) : '', val ? true : false, false).draw();
            });

            $('#analisaFilter').on('change', function() {
                const val = $(this).val();
                if (val === 'has_analisa') {
                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        const row = table.row(dataIndex).node();
                        const hasAnalisa = $(row).find('td:eq(13)').text().trim() !== '-';
                        return hasAnalisa;
                    });
                    table.draw();
                    $.fn.dataTable.ext.search.pop();
                } else if (val === 'possible_typo') {
                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        const row = table.row(dataIndex).node();
                        const analisaType = $(row).attr('data-analisa-type');
                        return analisaType === 'possible_typo';
                    });
                    table.draw();
                    $.fn.dataTable.ext.search.pop();
                } else if (val === 'anomaly') {
                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        const row = table.row(dataIndex).node();
                        const analisaType = $(row).attr('data-analisa-type');
                        return analisaType === 'anomaly';
                    });
                    table.draw();
                    $.fn.dataTable.ext.search.pop();
                } else {
                    table.draw();
                }
            });

            // Detail Modal Functions (sama seperti sebelumnya)
            $('#singleTable').on('click', '.btn-detail', function() {
                const productId = $(this).data('product');
                needsMainPageReload = false;
                loadModalHistory(productId);
                $('#detailModal').removeClass('hidden');
            });

            async function loadModalHistory(productId) {
                $('#modalTableBody').html('<tr><td colspan="8" class="text-center py-4 text-slate-400">Loading history...</td></tr>');
                try {
                    const response = await fetch(`ajax_product_detail.php?session_id=${encodeURIComponent(sessionId)}&id_product=${encodeURIComponent(productId)}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        currentUom = parseInt(data.product.uom, 10) || parseInt(data.product.carton_size, 10) || 1;
                        
                        $('#modalSubTitle').text(`Product ID: ${data.product.id_product}`);
                        $('#mPrincipal').text(data.product.principal);
                        $('#mProdName').text(data.product.product_name).attr('title', data.product.product_name);
                        $('#mPackname').text(data.product.packname);
                        $('#mUom').text(data.product.uom);
                        
                        let rows = '';
                        if (data.history.length === 0) {
                            rows = '<tr><td colspan="8" class="text-center py-4 text-slate-400">Belum ada perhitungan untuk produk ini</td></tr>';
                        } else {
                            data.history.forEach(h => {
                                rows += `
                                    <tr class="hover:bg-slate-50 text-sm" data-detail-id="${h.detail_id}" data-count-id="${h.count_id}" data-product-id="${h.id_product}" data-qty-karton="${h.qty_karton}" data-qty-pcs="${h.qty_pcs}" data-final-qty="${h.final_qty}">
                                        <td class="px-4 py-3 font-medium col-product-id text-slate-800">${h.id_product}</td>
                                        <td class="px-4 py-3 font-mono text-amber-700 col-location">${h.location}</td>
                                        <td class="px-4 py-3 col-team"><span class="px-2 py-0.5 rounded text-xs font-bold ${getTeamBadgeClass(h.team)}">${h.team}</span></td>
                                        <td class="px-4 py-3 text-right col-qty-karton text-slate-700">${formatNum(h.qty_karton)}</td>
                                        <td class="px-4 py-3 text-right col-qty-pcs text-slate-700">${formatNum(h.qty_pcs)}</td>
                                        <td class="px-4 py-3 text-right font-semibold col-final-qty text-slate-900">${formatNum(h.final_qty)}</td>
                                        <td class="px-4 py-3 text-slate-500 col-timestamp">${h.timestamp}</td>
                                        <td class="px-4 py-3 text-center col-action">
                                            <button type="button" class="btn-edit-row text-blue-600 hover:text-blue-800 font-bold mr-3 text-sm">Edit</button>
                                            <button type="button" class="btn-delete-row text-red-600 hover:text-red-800 font-bold text-sm">Hapus</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                        $('#modalTableBody').html(rows);
                    } else {
                        alert('Error: ' + (data.message || 'Gagal mengambil detail'));
                        $('#detailModal').addClass('hidden');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Terjadi kesalahan koneksi');
                    $('#detailModal').addClass('hidden');
                }
            }

            function getTeamBadgeClass(team) {
                const badges = {
                    'S': 'bg-emerald-100 text-emerald-800',
                    'R1': 'bg-blue-100 text-blue-800',
                    'R2': 'bg-purple-100 text-purple-800',
                    'R3': 'bg-rose-100 text-rose-800'
                };
                return badges[team] || 'bg-slate-100 text-slate-800';
            }

            function formatNum(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            // Edit/Delete functions (sama seperti sebelumnya)
            $('#modalTable').on('click', '.btn-edit-row', function() {
                const tr = $(this).closest('tr');
                const detailId = tr.attr('data-detail-id');
                const productId = tr.attr('data-product-id');
                const location = tr.find('.col-location').text();
                const team = tr.find('.col-team').html();
                const qtyKarton = tr.attr('data-qty-karton');
                const qtyPcs = tr.attr('data-qty-pcs');
                const finalQty = tr.attr('data-final-qty');
                const timestamp = tr.find('.col-timestamp').text();

                tr.html(`
                    <td class="px-2 py-2">
                        <input type="text" class="edit-id-product w-full px-3 py-1.5 border border-slate-300 rounded text-sm" value="${productId}">
                        <input type="hidden" class="edit-detail-id" value="${detailId}">
                    </td>
                    <td class="px-2 py-2 font-mono text-amber-700 text-sm align-middle">${location}</td>
                    <td class="px-2 py-2 text-sm align-middle">${team}</td>
                    <td class="px-2 py-2 align-middle"><input type="number" class="edit-qty-karton w-20 px-3 py-1.5 border border-slate-300 rounded text-right text-sm" value="${qtyKarton}"></td>
                    <td class="px-2 py-2 align-middle"><input type="number" class="edit-qty-pcs w-20 px-3 py-1.5 border border-slate-300 rounded text-right text-sm" value="${qtyPcs}"></td>
                    <td class="px-2 py-2 align-middle"><input type="number" class="edit-final-qty w-24 px-3 py-1.5 border border-slate-300 rounded text-right text-sm font-semibold" value="${finalQty}"></td>
                    <td class="px-2 py-2 text-slate-500 text-xs align-middle">${timestamp}</td>
                    <td class="px-2 py-2 text-center align-middle">
                        <button type="button" class="btn-save-row text-emerald-600 hover:text-emerald-800 font-bold mr-3 text-sm">Simpan</button>
                        <button type="button" class="btn-cancel-row text-slate-500 hover:text-slate-700 font-bold text-sm">Batal</button>
                    </td>
                `);
                tr.attr('data-original-product-id', productId);
            });

            $('#modalTable').on('input', '.edit-qty-karton, .edit-qty-pcs', function() {
                const tr = $(this).closest('tr');
                const karton = parseInt(tr.find('.edit-qty-karton').val()) || 0;
                const pcs = parseInt(tr.find('.edit-qty-pcs').val()) || 0;
                tr.find('.edit-final-qty').val(karton * currentUom + pcs);
            });

            $('#modalTable').on('click', '.btn-cancel-row', function() {
                const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
                loadModalHistory(currentModalProdId);
            });

            $('#modalTable').on('click', '.btn-save-row', async function() {
                const tr = $(this).closest('tr');
                const detailId = tr.find('.edit-detail-id').val();
                const oldProductId = tr.attr('data-original-product-id');
                const newProductId = tr.find('.edit-id-product').val().trim();
                const qtyKarton = parseInt(tr.find('.edit-qty-karton').val()) || 0;
                const qtyPcs = parseInt(tr.find('.edit-qty-pcs').val()) || 0;
                const finalQty = parseInt(tr.find('.edit-final-qty').val()) || 0;

                if (!newProductId) {
                    alert('Product ID tidak boleh kosong');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).text('...');

                try {
                    const formData = new FormData();
                    formData.append('session_id', sessionId);
                    formData.append('detail_id', detailId);
                    formData.append('old_id_product', oldProductId);
                    formData.append('new_id_product', newProductId);
                    formData.append('qty_karton', qtyKarton);
                    formData.append('qty_pcs', qtyPcs);
                    formData.append('final_qty', finalQty);

                    const response = await fetch('ajax_edit_history.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
                        loadModalHistory(currentModalProdId);
                        needsMainPageReload = true;
                    } else {
                        alert('Error: ' + data.message);
                        $btn.prop('disabled', false).text('Simpan');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Terjadi kesalahan koneksi');
                    $btn.prop('disabled', false).text('Simpan');
                }
            });

            $('#modalTable').on('click', '.btn-delete-row', async function() {
                const tr = $(this).closest('tr');
                const detailId = tr.attr('data-detail-id');
                const productId = tr.attr('data-product-id');

                if (!confirm(`Hapus record perhitungan produk "${productId}"?`)) return;

                const $btn = $(this);
                $btn.prop('disabled', true).text('...');

                try {
                    const formData = new FormData();
                    formData.append('session_id', sessionId);
                    formData.append('detail_id', detailId);

                    const response = await fetch('ajax_delete_history.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
                        loadModalHistory(currentModalProdId);
                        needsMainPageReload = true;
                    } else {
                        alert('Error: ' + data.message);
                        $btn.prop('disabled', false).text('Hapus');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Terjadi kesalahan koneksi');
                    $btn.prop('disabled', false).text('Hapus');
                }
            });

            $('#closeModalBtn, #closeModalBtn2').on('click', function() {
                $('#detailModal').addClass('hidden');
                if (needsMainPageReload) location.reload();
            });

            $('#detailModal').on('click', function(e) {
                if (e.target === this) {
                    $(this).addClass('hidden');
                    if (needsMainPageReload) location.reload();
                }
            });

            // Assign functions
            function attachCheckboxEvents() {
                $('.product-checkbox').off('change').on('change', function() {
                    updateSelectedCount();
                });
            }

            function updateSelectedCount() {
                const count = $('.product-checkbox:checked').length;
                const userSelected = $('#userSelect').val();
                $('#selectedCount').text(count);
                $('#assignBtn').prop('disabled', count === 0 || !userSelected);
            }

            function getSelectedProducts() {
                const products = [];
                $('.product-checkbox:checked').each(function() {
                    products.push($(this).val());
                });
                return products;
            }

            $('#userSelect').on('change', updateSelectedCount);

            $('#assignBtn').on('click', async function() {
                const selectedProducts = getSelectedProducts();
                const assignedTo = $('#userSelect').val();

                if (selectedProducts.length === 0 || !assignedTo) {
                    alert('Pilih produk dan user terlebih dahulu');
                    return;
                }

                if (!confirm(`Assign ${selectedProducts.length} produk?`)) return;

                const $btn = $(this);
                $btn.prop('disabled', true).find('#assignBtnLoading').removeClass('hidden');
                $btn.find('#assignBtnText').text('Loading');

                try {
                    const formData = new FormData();
                    formData.append('session_id', sessionId);
                    formData.append('assigned_to', assignedTo);
                    formData.append('selected_products', JSON.stringify(selectedProducts));
                    formData.append('assigned_by', '1');

                    const response = await fetch('ajax_assign_single.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan');
                } finally {
                    $btn.prop('disabled', false).find('#assignBtnLoading').addClass('hidden');
                    $btn.find('#assignBtnText').text('Assign');
                }
            });

            attachCheckboxEvents();
            updateSelectedCount();

            // ============================================
            // ANALYSIS MODAL LOGIC
            // ============================================
            function renderAnalysisCards(filterConfidence, filterType) {
                const container = $('#analysisContent');
                container.empty();

                const filtered = analysisData.filter(item => {
                    if (filterConfidence && item.best_confidence !== filterConfidence) return false;
                    if (filterType && item.best_match_type !== filterType) return false;
                    return true;
                });

                if (filtered.length === 0) {
                    container.html(`
                        <div class="text-center py-16 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-lg font-semibold">Tidak ada hasil</p>
                            <p class="text-sm">Coba ubah filter untuk melihat data lain</p>
                        </div>
                    `);
                    return;
                }

                filtered.forEach((item, idx) => {
                    const confidenceClass = item.best_confidence.toLowerCase();
                    const confidenceEmoji = item.best_confidence === 'Tinggi' ? '🔴' : (item.best_confidence === 'Sedang' ? '🟡' : '⚪');
                    
                    let matchesHtml = '';
                    item.matches.forEach((m, mIdx) => {
                        const matchBadgeClass = `match-badge-${m.match_type}`;
                        const matchLabel = m.match_type === 'both' ? 'KODE+NAMA' : (m.match_type === 'code' ? 'KODE' : 'NAMA');
                        
                        const nameBarColor = m.name_score >= 80 ? '#10B981' : (m.name_score >= 60 ? '#F59E0B' : '#EF4444');
                        const codeBarColor = m.code_score >= 70 ? '#10B981' : (m.code_score >= 40 ? '#F59E0B' : '#EF4444');
                        const combinedBarColor = m.combined_score >= 70 ? '#10B981' : (m.combined_score >= 50 ? '#F59E0B' : '#EF4444');
                        
                        matchesHtml += `
                            <tr class="recommendation-row ${mIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'}">
                                <td class="px-3 py-2.5 text-center text-xs text-gray-400">${mIdx + 1}</td>
                                <td class="px-3 py-2.5">
                                    <code class="text-xs font-mono font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded">${escHtml(m.id_product)}</code>
                                    <div class="text-[11px] text-gray-500 mt-0.5 max-w-[250px] truncate" title="${escHtml(m.product_name)}">${escHtml(m.product_name)}</div>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-gray-600">${escHtml(m.packname)}</td>
                                <td class="px-3 py-2.5 text-right text-xs font-semibold text-emerald-700">${formatNum(m.stock_system)}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${matchBadgeClass}">${matchLabel}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="score-bar flex-1" style="min-width:60px">
                                            <div class="score-bar-fill" style="width:${m.name_score}%; background:${nameBarColor}"></div>
                                        </div>
                                        <span class="text-xs font-mono font-semibold w-12 text-right" style="color:${nameBarColor}">${m.name_score}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="score-bar flex-1" style="min-width:60px">
                                            <div class="score-bar-fill" style="width:${m.code_score}%; background:${codeBarColor}"></div>
                                        </div>
                                        <span class="text-xs font-mono font-semibold w-12 text-right" style="color:${codeBarColor}">${m.code_score}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="score-bar flex-1" style="min-width:60px">
                                            <div class="score-bar-fill" style="width:${m.combined_score}%; background:${combinedBarColor}"></div>
                                        </div>
                                        <span class="text-xs font-mono font-bold w-12 text-right" style="color:${combinedBarColor}">${m.combined_score}%</span>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });

                    const cardHtml = `
                        <div class="analysis-card analysis-card-animate confidence-${confidenceClass} bg-white rounded-xl border border-gray-200 overflow-hidden" style="animation-delay: ${idx * 0.08}s">
                            <!-- Card Header: Produk Bermasalah -->
                            <div class="px-5 py-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold ${
                                            item.best_confidence === 'Tinggi' ? 'bg-red-100 text-red-700' : 
                                            item.best_confidence === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'
                                        }">${idx + 1}</div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <code class="text-sm font-mono font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200">${escHtml(item.id_product)}</code>
                                                <span class="text-xs text-gray-400">→ Stok Sistem: <strong class="text-red-600">0</strong></span>
                                                <span class="text-xs text-gray-400">|</span>
                                                <span class="text-xs text-gray-400">Input Team S: <strong class="text-blue-600">${formatNum(item.s_qty)} pcs</strong> (${formatNum(item.s_karton)} krt + ${formatNum(item.s_pcs)} pcs)</span>
                                            </div>
                                            <div class="text-sm text-slate-700 mt-1 truncate max-w-xl" title="${escHtml(item.product_name)}">
                                                ${escHtml(item.product_name)}
                                                <span class="text-gray-400 text-xs ml-1">${escHtml(item.packname)}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="confidence-badge-${confidenceClass} px-2.5 py-1 rounded-full text-[11px] font-bold">
                                            ${confidenceEmoji} ${item.best_confidence}
                                        </span>
                                        <span class="text-xs text-gray-400">${item.match_count} rekomendasi</span>
                                        <button class="toggle-matches text-gray-400 hover:text-gray-700 transition" data-target="matches-${idx}">
                                            <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recommendations Table (collapsible) -->
                            <div id="matches-${idx}" class="border-t border-gray-100">
                                <div class="px-5 py-2 bg-gradient-to-r from-slate-50 to-gray-50 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-slate-600">💡 Produk yang mungkin dimaksud:</span>
                                    <span class="text-[10px] text-gray-400">Urutkan berdasarkan skor tertinggi</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wider">
                                                <th class="px-3 py-2 text-center w-8">#</th>
                                                <th class="px-3 py-2 text-left">Kode & Nama Produk</th>
                                                <th class="px-3 py-2 text-left">Pack</th>
                                                <th class="px-3 py-2 text-right">Stok</th>
                                                <th class="px-3 py-2 text-center">Tipe</th>
                                                <th class="px-3 py-2 text-center" width="140">Skor Nama</th>
                                                <th class="px-3 py-2 text-center" width="140">Skor Kode</th>
                                                <th class="px-3 py-2 text-center" width="140">Skor Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>${matchesHtml}</tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    container.append(cardHtml);
                });
            }

            function escHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // Open/Close Analysis Modal
            $('#openAnalysisModal').on('click', function() {
                $('#analysisModal').removeClass('hidden');
                renderAnalysisCards('', '');
            });

            $('#closeAnalysisBtn, #closeAnalysisBtn2').on('click', function() {
                $('#analysisModal').addClass('hidden');
            });

            $('#analysisModal').on('click', function(e) {
                if (e.target === this) $(this).addClass('hidden');
            });

            // Filters
            $('#analysisConfidenceFilter, #analysisTypeFilter').on('change', function() {
                const conf = $('#analysisConfidenceFilter').val();
                const type = $('#analysisTypeFilter').val();
                renderAnalysisCards(conf, type);
            });

            // Toggle expand/collapse
            $(document).on('click', '.toggle-matches', function() {
                const targetId = $(this).data('target');
                const $target = $(`#${targetId}`);
                const $icon = $(this).find('svg');
                
                $target.slideToggle(200);
                $icon.toggleClass('rotate-180');
            });
        });
    </script>
</body>
</html>