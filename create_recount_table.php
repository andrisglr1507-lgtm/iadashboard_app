<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Creating opname_recount_assignments...\n";

if (!Schema::hasTable('opname_recount_assignments')) {
    Schema::create('opname_recount_assignments', function (Blueprint $table) {
        $table->id('assignment_id');
        $table->unsignedBigInteger('session_id');
        $table->string('location_code', 50);
        $table->string('id_product', 50);
        $table->integer('round_number')->default(1);
        $table->unsignedBigInteger('assigned_to')->comment('User ID dari tim Recount');
        $table->unsignedBigInteger('assigned_by');
        $table->enum('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('PENDING');
        $table->timestamp('assigned_at')->useCurrent();
        $table->timestamp('submitted_at')->nullable();
        $table->boolean('is_final')->default(false);
        $table->timestamps();

        // Foreign keys - comment out if not needed strict
        // $table->foreign('session_id')->references('id')->on('opname_sessions')->onDelete('cascade');
        // $table->foreign('assigned_to')->references('id')->on('users');
    });
    echo "Table created successfully!\n";
} else {
    echo "Table already exists!\n";
}

$recounts = \App\Models\OpnameResult::where('result_status', 'RECOUNT')->get();
if ($recounts->count() > 0) {
    echo "Found " . $recounts->count() . " RECOUNT results, running runReconciliation to trigger Auto-Dispatch...\n";
    $controller = new \App\Http\Controllers\Sodc\Result\CountResultController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('runReconciliation');
    $method->setAccessible(true);
    $method->invoke($controller, $recounts->first()->session_id);
    echo "Auto-Dispatch triggered.\n";
}
