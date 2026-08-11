<?php
// index.php - Halaman utama pilih session
// Letakkan di: /api-csan/so/page/index.php

session_start();
require_once "db.php";

// Hapus session aktif jika ada parameter reset
if (isset($_GET['reset'])) {
    unset($_SESSION['active_session']);
}

// Ambil daftar session dari database
$sessions = [];

// Query untuk mengambil semua session
$sql = "SELECT 
            s.session_id,
            s.branch_id,
            s.warehouse_code,
            s.bin_location,
            s.periode_start,
            s.periode_end,
            s.mode,
            s.status,
            s.created_at,
            COALESCE(COUNT(DISTINCT h.location_code), 0) as locations_count
        FROM opname_sessions s
        LEFT JOIN opname_count_headers h ON s.session_id = h.session_id
        WHERE s.mode IN ('D', 'S', 'single')
        GROUP BY s.session_id
        ORDER BY s.created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Session - Recount Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mode-d {
            background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
        }
        .mode-s {
            background: linear-gradient(135deg, #065F46 0%, #047857 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="text-center sm:text-left flex-1">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Recount Summary</h1>
                <p class="text-gray-500">Pilih session untuk melihat detail recount perbandingan</p>
            </div>
            <a href="session_create.php"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm shadow transition whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Session
            </a>
        </div>

        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-800">
                        <strong>Mode Double (D):</strong> Membandingkan Team A vs Team B, dengan recount R1 dan R2 per lokasi.
                    </p>
                    <p class="text-sm text-blue-800 mt-1">
                        <strong>Mode Single (S):</strong> Membandingkan Team S vs Stock System, dengan recount R1, R2, R3.
                    </p>
                </div>
            </div>
        </div>

        <?php if (empty($sessions)): ?>
            <!-- Empty State -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Belum Ada Session</h3>
                <p class="text-yellow-600 text-sm">Belum ada session dengan mode Double (D) atau Single (S).</p>
                <a href="session_create.php"
                   class="inline-block mt-4 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm">
                    + Buat Session Pertama
                </a>
            </div>
        <?php else: ?>
            <!-- Grid Session Cards -->
            <div class="grid md:grid-cols-2 gap-6">
                <?php foreach ($sessions as $session): 
                    $isDouble = ($session['mode'] === 'D');
                    $bgClass = $isDouble ? 'mode-d' : 'mode-s';
                    $modeLabel = $isDouble ? 'Double / Team' : 'Single';
                    $routeFile = $isDouble ? 'recount_double.php' : 'recount_single.php';
                    $isDraft = ($session['status'] === 'draft');
                    $cardHref = $isDraft
                        ? 'session_create.php?session_id=' . urlencode($session['session_id'])
                        : $routeFile . '?session_id=' . urlencode($session['session_id']);
                ?>
                    <a href="<?php echo $cardHref; ?>" 
                       class="card-hover block rounded-xl overflow-hidden shadow-lg">
                        <div class="<?php echo $bgClass; ?> p-5 text-white">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-white/20 mb-2">
                                        <?php echo $modeLabel; ?>
                                    </span>
                                    <h2 class="font-mono text-xl font-bold"><?php echo htmlspecialchars($session['session_id']); ?></h2>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold"><?php echo $session['locations_count']; ?></div>
                                    <div class="text-xs opacity-80">Lokasi</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-400 text-xs">Branch</span>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($session['branch_id'] ?? '-'); ?></p>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs">Warehouse</span>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($session['warehouse_code'] ?? '-'); ?></p>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs">Bin Location</span>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($session['bin_location'] ?? '-'); ?></p>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs">Status</span>
                                    <p class="font-medium">
                                        <?php
                                        $statusClass = 'bg-yellow-100 text-yellow-700';
                                        if ($session['status'] === 'final' || $session['status'] === 'closed') {
                                            $statusClass = 'bg-green-100 text-green-700';
                                        } elseif ($session['status'] === 'draft') {
                                            $statusClass = 'bg-amber-100 text-amber-800';
                                        } elseif ($session['status'] === 'recount') {
                                            $statusClass = 'bg-red-100 text-red-700';
                                        }
                                        ?>
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($session['status'] ?? 'draft'); ?>
                                            <?php if ($isDraft): ?> · Upload produk<?php endif; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400">
                                📅 <?php echo date('d/m/Y H:i', strtotime($session['created_at'])); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-xs text-gray-400">
            <p>Recount Summary System - Perbandingan hasil counting dengan recount</p>
        </div>
    </div>
</body>
</html>