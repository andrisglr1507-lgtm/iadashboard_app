<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign key constraint on assignment_id if exists
        try {
            Schema::table('opname_counts', function (Blueprint $table) {
                $table->dropForeign('fk_count_assignment');
            });
        } catch (\Throwable $e) {
            // Ignore if constraint name differs or already dropped
        }

        try {
            Schema::table('opname_counts', function (Blueprint $table) {
                $table->dropForeign(['assignment_id']);
            });
        } catch (\Throwable $e) {
            // Ignore if not exists
        }

        // 2. Modify assignment_id to nullable
        DB::statement('ALTER TABLE opname_counts MODIFY assignment_id BIGINT UNSIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE opname_counts MODIFY assignment_id BIGINT UNSIGNED NOT NULL;');
    }
};
