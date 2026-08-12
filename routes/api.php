<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Master Sync routes (placeholder for now, can point to actual master controllers later)
    Route::get('/master/products', function () { return response()->json(['data' => \App\Models\Product::all()]); });
    Route::get('/master/bins', function () { return response()->json(['data' => \App\Models\Bin::all()]); });

    // Opname Scanner routes
    Route::get('/sodc/my-tasks', [\App\Http\Controllers\Api\Sodc\TaskController::class, 'myTasks']);
    Route::post('/sodc/submit-count', [\App\Http\Controllers\Api\Sodc\CountController::class, 'submitCount']);
});
