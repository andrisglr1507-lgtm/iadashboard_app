<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gunakan raw DB statement agar lebih aman mengubah ENUM ke VARCHAR di MySQL
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE opname_results MODIFY COLUMN result_status VARCHAR(50) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opsional: kembalikan ke ENUM jika diperlukan, 
        // tapi VARCHAR lebih fleksibel jadi biarkan saja atau set ke VARCHAR.
    }
};
