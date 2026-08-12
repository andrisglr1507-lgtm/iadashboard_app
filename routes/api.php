<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // TODO: Buat controller dan route untuk fitur lainnya
    Route::get('/sessions/active', function () { return response()->json(['data' => []]); });
    Route::get('/bins/{code}', function ($code) { return response()->json(['data' => null]); });
    Route::post('/products/scan', function () { return response()->json(['data' => null]); });
    Route::post('/opname/submit', function () { return response()->json(['message' => 'Success']); });
    Route::get('/opname/history', function () { return response()->json(['data' => []]); });
});
