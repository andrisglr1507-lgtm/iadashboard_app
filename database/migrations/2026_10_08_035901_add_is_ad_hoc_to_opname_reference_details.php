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
        Schema::table('opname_reference_details', function (Blueprint $table) {
            $table->boolean('is_ad_hoc')->default(false)->after('stock_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_reference_details', function (Blueprint $table) {
            $table->dropColumn('is_ad_hoc');
        });
    }
};
