<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterProductController;
use App\Http\Controllers\OpnameSessionController;


// Authentication routes
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
// Master Product routes
Route::get('/master/products', [MasterProductController::class, 'index'])->name('master.products.index');
Route::post('/master/products/import', [MasterProductController::class, 'import'])->name('master.products.import');
Route::get('/master/products/{principal}', [MasterProductController::class, 'show'])->name('master.products.show');

// Session Opname routes
Route::get('/sessions', [OpnameSessionController::class, 'index'])->name('sessions.index');
Route::get('/sessions/create', [OpnameSessionController::class, 'create'])->name('sessions.create');
Route::post('/sessions', [OpnameSessionController::class, 'store'])->name('sessions.store');
Route::get('/sessions/{session}/upload', [OpnameSessionController::class, 'uploadPage'])->name('sessions.upload');
Route::post('/sessions/{session}/upload', [OpnameSessionController::class, 'handleUpload'])->name('sessions.upload.submit');

// Single Mode routes
Route::get('/single-mode/records', [\App\Http\Controllers\SingleModeController::class, 'records'])->name('single_mode.records');
Route::get('/single-mode/recount', [\App\Http\Controllers\RecountSingleController::class, 'index'])->name('single_mode.recount');
Route::post('/single-mode/recount/detail', [\App\Http\Controllers\RecountSingleController::class, 'getProductDetail'])->name('single_mode.recount.detail');
Route::post('/single-mode/recount/assign', [\App\Http\Controllers\RecountSingleController::class, 'assignUsers'])->name('single_mode.recount.assign');
Route::post('/single-mode/recount/edit', [\App\Http\Controllers\RecountSingleController::class, 'editHistory'])->name('single_mode.recount.edit');
Route::post('/single-mode/recount/delete', [\App\Http\Controllers\RecountSingleController::class, 'deleteHistory'])->name('single_mode.recount.delete');

// Assignment routes
Route::get('/single-mode/assignments', [\App\Http\Controllers\AssignmentController::class, 'index'])->name('single_mode.assignments');

// Double Mode (A Vs B) routes
Route::get('/double-mode/recount', [\App\Http\Controllers\RecountDoubleController::class, 'index'])->name('double_mode.recount');
Route::post('/double-mode/recount/detail', [\App\Http\Controllers\RecountDoubleController::class, 'getProductDetail'])->name('double_mode.recount.detail');
Route::post('/double-mode/recount/assign', [\App\Http\Controllers\RecountDoubleController::class, 'assignUsers'])->name('double_mode.recount.assign');
Route::post('/double-mode/recount/edit', [\App\Http\Controllers\RecountDoubleController::class, 'editHistory'])->name('double_mode.recount.edit');
Route::post('/double-mode/recount/delete', [\App\Http\Controllers\RecountDoubleController::class, 'deleteHistory'])->name('double_mode.recount.delete');
Route::get('/double-mode/assignments', [\App\Http\Controllers\AssignmentDoubleController::class, 'index'])->name('double_mode.assignments');

// Global Context Route
Route::post('/set-global-session', [\App\Http\Controllers\GlobalContextController::class, 'setActiveSession'])->name('global.set_session');

// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics/traffic', [DashboardController::class, 'traffic'])->name('analytics.traffic');
Route::get('/reports/sales', [DashboardController::class, 'salesReport'])->name('reports.sales');
Route::get('/reports/users', [DashboardController::class, 'userReport'])->name('reports.users');
Route::get('/content/posts', [DashboardController::class, 'posts'])->name('content.posts');
Route::get('/media/images', [DashboardController::class, 'images'])->name('media.images');
Route::get('/media/videos', [DashboardController::class, 'videos'])->name('media.videos');
Route::get('/settings/profile', [DashboardController::class, 'profile'])->name('settings.profile');
Route::get('/settings/security', [DashboardController::class, 'security'])->name('settings.security');



// Users List routes
Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');



// BA Pemeriksaan routes
Route::get('/ba-pemeriksaan', [\App\Http\Controllers\BaPemeriksaanController::class, 'index'])->name('ba_pemeriksaan.index');
Route::get('/ba-pemeriksaan/create', [\App\Http\Controllers\BaPemeriksaanController::class, 'create'])->name('ba_pemeriksaan.create');
Route::post('/ba-pemeriksaan/store', [\App\Http\Controllers\BaPemeriksaanController::class, 'storeHeader'])->name('ba_pemeriksaan.store');
Route::get('/ba-pemeriksaan/{id_ba}/upload', [\App\Http\Controllers\BaPemeriksaanController::class, 'uploadPage'])->name('ba_pemeriksaan.upload');
Route::post('/ba-pemeriksaan/{id_ba}/import', [\App\Http\Controllers\BaPemeriksaanController::class, 'import'])->name('ba_pemeriksaan.import');
Route::get('/ba-pemeriksaan/{id_ba}/detail', [\App\Http\Controllers\BaPemeriksaanController::class, 'detail'])->name('ba_pemeriksaan.detail');

// SO DC Routes
Route::prefix('sodc')->name('sodc.')->group(function () {
    // Master Data
    Route::get('bins/import', [\App\Http\Controllers\Sodc\Master\BinController::class, 'showImport'])->name('bins.import_page');
    Route::post('bins/import', [\App\Http\Controllers\Sodc\Master\BinController::class, 'import'])->name('bins.import');
    Route::get('bins/template', [\App\Http\Controllers\Sodc\Master\BinController::class, 'downloadTemplate'])->name('bins.template');
    Route::resource('branches', \App\Http\Controllers\Sodc\Master\BranchController::class);
    
    Route::get('warehouses/import', [\App\Http\Controllers\Sodc\Master\WarehouseController::class, 'showImport'])->name('warehouses.import_page');
    Route::post('warehouses/import', [\App\Http\Controllers\Sodc\Master\WarehouseController::class, 'import'])->name('warehouses.import');
    Route::get('warehouses/template', [\App\Http\Controllers\Sodc\Master\WarehouseController::class, 'downloadTemplate'])->name('warehouses.template');
    Route::resource('warehouses', \App\Http\Controllers\Sodc\Master\WarehouseController::class);
    Route::resource('bins', \App\Http\Controllers\Sodc\Master\BinController::class);
    
    Route::get('products/import', [\App\Http\Controllers\Sodc\Master\ProductController::class, 'showImport'])->name('products.import_page');
    Route::post('products/import', [\App\Http\Controllers\Sodc\Master\ProductController::class, 'import'])->name('products.import');
    Route::get('products/template', [\App\Http\Controllers\Sodc\Master\ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::resource('products', \App\Http\Controllers\Sodc\Master\ProductController::class);
    Route::resource('teams', \App\Http\Controllers\Sodc\Master\TeamController::class);
    
    // Opname Management
    Route::get('references/import', [\App\Http\Controllers\Sodc\Management\ReferenceController::class, 'showImport'])->name('references.import_page');
    Route::post('references/import', [\App\Http\Controllers\Sodc\Management\ReferenceController::class, 'import'])->name('references.import');
    Route::get('references/template', [\App\Http\Controllers\Sodc\Management\ReferenceController::class, 'downloadTemplate'])->name('references.template');
    Route::resource('references', \App\Http\Controllers\Sodc\Management\ReferenceController::class);
    Route::resource('sessions', \App\Http\Controllers\Sodc\Management\SessionController::class);
    Route::resource('assignments', \App\Http\Controllers\Sodc\Management\AssignmentController::class);
    
    // Opname Results (Placeholder)
    Route::get('/sync_logs', function() { return 'Sync Logs'; })->name('sync_logs.index');
    Route::get('/results', function() { return 'Results'; })->name('results.index');
    Route::get('/approvals', function() { return 'Approvals'; })->name('approvals.index');
});
});
