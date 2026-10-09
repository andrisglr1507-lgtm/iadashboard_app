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
        Schema::table('opname_results', function (Blueprint $table) {
            $table->decimal('recount3_qty', 10, 2)->nullable()->after('recount2_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_results', function (Blueprint $table) {
            $table->dropColumn('recount3_qty');
        });
    }
};
