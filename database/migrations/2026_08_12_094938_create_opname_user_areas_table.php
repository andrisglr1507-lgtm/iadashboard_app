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
        Schema::create('opname_user_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('warehouse_id'); // We'll map to bins.warehouse_id (which might be string or int in master, let's use string to be safe)
            $table->string('aisle')->nullable(); // Lorong
            $table->unsignedBigInteger('user_id');
            $table->string('team_role'); // TEAM_A, TEAM_B
            $table->timestamps();

            // Note: Since warehouses table uses string for warehouse_code, it might be better to store warehouse_code or ID.
            // Bins uses `warehouse_id` which might be the ID. We'll leave it as string for flexbility.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opname_user_areas');
    }
};
