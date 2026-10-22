<?php

namespace App\Http\Controllers\KonfirmasiFaktur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exports\KonfirmasiFaktur\CustomerExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected string $apiBaseUrl = 'https://wms.edrs.co.id/api/mileapp/customer';
    protected string $apiToken = '75|tuN21XD303rLCfLQhRkAEXixfWHwwDoZIrXYzDdd';

    /**
     * Display a listing of customers from MileApp API.
     */
    public function index(Request $request)
    {
        $branch = strtoupper(trim($request->get('branch', 'COGBGK')));
        $search = trim($request->get('search', ''));
        $type = $request->get('type', '');
        $gpsOnly = $request->get('gps_only', '');
        $refresh = $request->has('refresh');

        // Fetch customers list
        $result = $this->fetchFromApi($branch, $refresh);

        if (!$result['success']) {
            $errorMessage = $result['message'];
            return view('konfirmasi_faktur.customers.index', [
                'customers' => new LengthAwarePaginator([], 0, 25),
                'metrics' => [
                    'total' => 0,
                    'total_mt' => 0,
                    'total_gt' => 0,
                    'total_gps' => 0,
                ],
                'branch' => $branch,
                'search' => $search,
                'type' => $type,
                'gpsOnly' => $gpsOnly,
                'errorMessage' => $errorMessage,
                'branchesList' => $this->getAvailableBranches($branch)
            ]);
        }

        $allData = collect($result['data']);

        // Calculate summary metrics on full branch dataset
        $metrics = [
            'total' => $allData->count(),
            'total_mt' => $allData->where('mt', '1')->count(),
            'total_gt' => $allData->where('mt', '0')->count(),
            'total_gps' => $allData->filter(fn($item) => !empty($item['coordinate']))->count(),
        ];

        // Apply filters
        $filtered = $allData;

        if ($search !== '') {
            $keyword = strtolower($search);
            $filtered = $filtered->filter(function ($item) use ($keyword) {
                return str_contains(strtolower($item['partnerName'] ?? ''), $keyword)
                    || str_contains(strtolower($item['dataId'] ?? ''), $keyword)
                    || str_contains(strtolower($item['phoneNumber'] ?? ''), $keyword)
                    || str_contains(strtolower($item['address'] ?? ''), $keyword);
            });
        }

        if ($type === 'MT') {
            $filtered = $filtered->where('mt', '1');
        } elseif ($type === 'GT') {
            $filtered = $filtered->where('mt', '0');
        }

        if ($gpsOnly === '1') {
            $filtered = $filtered->filter(fn($item) => !empty($item['coordinate']));
        } elseif ($gpsOnly === '0') {
            $filtered = $filtered->filter(fn($item) => empty($item['coordinate']));
        }

        // Pagination with dynamic limit (default 25 per page to keep rendering lightweight)
        $limit = (int) $request->get('limit', 25);
        if (!in_array($limit, [10, 25, 50, 100])) {
            $limit = 25;
        }

        $perPage = $limit;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $filtered->count();
        $items = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        $customers = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => route('konfirmasi_faktur.customers.index'),
            'query' => $request->query(),
        ]);

        return view('konfirmasi_faktur.customers.index', [
            'customers' => $customers,
            'metrics' => $metrics,
            'branch' => $branch,
            'search' => $search,
            'type' => $type,
            'gpsOnly' => $gpsOnly,
            'limit' => $limit,
            'errorMessage' => null,
            'branchesList' => $this->getAvailableBranches($branch)
        ]);
    }

    /**
     * Export customer data to Excel.
     */
    public function export(Request $request)
    {
        $branch = strtoupper(trim($request->get('branch', 'COGBGK')));
        $search = trim($request->get('search', ''));
        $type = $request->get('type', '');
        $gpsOnly = $request->get('gps_only', '');

        $result = $this->fetchFromApi($branch);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Gagal mendownload data: ' . $result['message']);
        }

        $filtered = collect($result['data']);

        if ($search !== '') {
            $keyword = strtolower($search);
            $filtered = $filtered->filter(function ($item) use ($keyword) {
                return str_contains(strtolower($item['partnerName'] ?? ''), $keyword)
                    || str_contains(strtolower($item['dataId'] ?? ''), $keyword)
                    || str_contains(strtolower($item['phoneNumber'] ?? ''), $keyword)
                    || str_contains(strtolower($item['address'] ?? ''), $keyword);
            });
        }

        if ($type === 'MT') {
            $filtered = $filtered->where('mt', '1');
        } elseif ($type === 'GT') {
            $filtered = $filtered->where('mt', '0');
        }

        if ($gpsOnly === '1') {
            $filtered = $filtered->filter(fn($item) => !empty($item['coordinate']));
        } elseif ($gpsOnly === '0') {
            $filtered = $filtered->filter(fn($item) => empty($item['coordinate']));
        }

        $filename = 'Data_Pelanggan_' . $branch . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new CustomerExport($filtered), $filename);
    }

    /**
     * Fetch from external API with smart caching.
     */
    protected function fetchFromApi(string $branch, bool $forceRefresh = false): array
    {
        $cacheKey = "wms_mileapp_customer_{$branch}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 600, function () use ($branch) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->timeout(30)
                    ->withoutVerifying()
                    ->get("{$this->apiBaseUrl}/{$branch}");

                if ($response->successful()) {
                    $json = $response->json();
                    return [
                        'success' => true,
                        'data' => $json['data'] ?? [],
                        'message' => 'Success'
                    ];
                }

                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'API WMS merespons status: ' . $response->status() . ' (' . $response->body() . ')'
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'Gagal menghubungi server WMS: ' . $e->getMessage()
                ];
            }
        });
    }

    /**
     * Get branch suggestions list for dropdown/filter.
     */
    protected function getAvailableBranches(string $currentBranch): array
    {
        $branches = [];
        try {
            $dbBranches = DB::table('branches')->pluck('branch_name', 'branch_code')->toArray();
            $branches = $dbBranches;
        } catch (\Exception $e) {
            // fallback
        }

        // Ensure current branch and known branches exist in suggestions
        if (!isset($branches['COGBGK'])) {
            $branches['COGBGK'] = 'Bangka (COGBGK)';
        }
        if (!isset($branches['COGBYR'])) {
            $branches['COGBYR'] = 'DC Bayur (COGBYR)';
        }
        if (!isset($branches[$currentBranch])) {
            $branches[$currentBranch] = $currentBranch;
        }

        return $branches;
    }
}
