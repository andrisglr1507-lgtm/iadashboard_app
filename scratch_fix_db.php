<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::table('opname_counts', function (Blueprint $table) {
        $table->dropForeign('fk_count_team');
    });
    echo "Foreign key dropped.\n";
} catch (\Exception $e) {
    echo "Drop FK error: " . $e->getMessage() . "\n";
}

try {
    DB::statement("ALTER TABLE opname_counts MODIFY team_id VARCHAR(50) NOT NULL;");
    echo "Column type modified.\n";
} catch (\Exception $e) {
    echo "Modify column error: " . $e->getMessage() . "\n";
}
