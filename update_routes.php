<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

// Extract the use statements
$useStatementsEnd = strpos($content, '// Master Product routes');
$uses = substr($content, 0, $useStatementsEnd);

// Extract the rest
$rest = substr($content, $useStatementsEnd);

// Remove the authentication routes from the rest
$authBlock = <<<PHP
// Authentication routes
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
PHP;

$rest = str_replace($authBlock, '', $rest);

// Remove the old empty auth middleware group
$oldAuthGroup = <<<PHP
// Optional: Protected routes with authentication
Route::middleware(['auth'])->group(function () {
    // Masukkan route yang memerlukan login di sini
});
PHP;

$rest = str_replace($oldAuthGroup, '', $rest);

$newContent = $uses . "\n" . $authBlock . "\n\nRoute::middleware(['auth'])->group(function () {\n" . trim($rest) . "\n});\n";

file_put_contents($file, $newContent);
echo "Routes updated successfully.\n";
