<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$dbName = env('DB_DATABASE');
$key = 'Tables_in_' . $dbName;
$tables = DB::select('SHOW TABLES');

$fixed = 0;
foreach ($tables as $table) {
    $tableName = $table->$key;
    
    // Check if table has 'id' column
    try {
        $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = 'id'");
        
        if (!empty($columns)) {
            $type = $columns[0]->Type; // e.g. bigint unsigned
            $extra = $columns[0]->Extra;
            
            if (strpos(strtolower($extra), 'auto_increment') === false) {
                // Fix: First try to make it a Primary Key (if it lost its key status during export)
                try {
                    DB::statement("ALTER TABLE `{$tableName}` ADD PRIMARY KEY (`id`)");
                } catch (\Exception $e) {
                    // Ignore if primary key already exists
                }
                
                // Then add AUTO_INCREMENT
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                echo "✅ Fixed AUTO_INCREMENT for table: {$tableName}\n";
                $fixed++;
            }
        }
    } catch (\Exception $e) {
        // Ignore errors for views or tables without id
        echo "❌ Error on table {$tableName}: " . $e->getMessage() . "\n";
    }
}

if ($fixed == 0) {
    echo "👍 All tables are already using AUTO_INCREMENT perfectly!\n";
} else {
    echo "🎉 Successfully fixed {$fixed} tables!\n";
}
