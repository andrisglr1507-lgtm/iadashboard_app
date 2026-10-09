<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/master/bins', function () { return response()->json(['data' => \App\Models\Bin::all()]); });

    // Master Sync routes
    Route::get('/master/products', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\Product::query();
        if ($request->has('principal') && !empty($request->principal)) {
            $query->where('principal', $request->principal);
        }
        return response()->json(['data' => $query->get()]);
    });
    
    Route::get('/master/principals', function () {
        $principals = \App\Models\Product::distinct()->whereNotNull('principal')->where('principal', '!=', '')->pluck('principal');
        return response()->json(['data' => $principals]);
    });

    Route::post('/sodc/bad-stock', [\App\Http\Controllers\Api\Sodc\BadStockController::class, 'store'])->name('api.sodc.bad_stock.store');
    
    // Opname Scanner routes
    Route::get('/sodc/my-tasks', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'allBins']); // Backward compatibility for Flutter
    Route::get('/sodc/sessions/active', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'activeSessions']);
    Route::get('/sodc/dashboard/stats', [\App\Http\Controllers\Api\Sodc\DashboardController::class, 'getStats']);
    Route::get('/sodc/tasks', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'myTasks']);
    Route::get('/sodc/bins', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'allBins']);
    Route::get('/sodc/bins/{bin_code}', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'binDetails']);
    Route::post('/sodc/submit-count', [\App\Http\Controllers\Api\Sodc\CountController::class, 'submitCount']);
    Route::post('/sodc/complete-bin', [\App\Http\Controllers\Api\Sodc\CountController::class, 'completeBin']);
    Route::post('/sodc/delete-count', [\App\Http\Controllers\Api\Sodc\CountController::class, 'deleteCount']);
});
