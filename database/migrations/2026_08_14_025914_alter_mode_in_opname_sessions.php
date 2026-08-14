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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE opname_sessions MODIFY COLUMN mode VARCHAR(50) DEFAULT 'SINGLE'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
