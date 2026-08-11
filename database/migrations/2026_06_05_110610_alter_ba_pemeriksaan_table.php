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
        Schema::table('ba_pemeriksaan', function (Blueprint $table) {
            if (Schema::hasColumn('ba_pemeriksaan', 'periode')) {
                $table->dropColumn('periode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ba_pemeriksaan', function (Blueprint $table) {
            if (!Schema::hasColumn('ba_pemeriksaan', 'periode')) {
                $table->string('periode', 20)->nullable();
            }
        });
    }
};
