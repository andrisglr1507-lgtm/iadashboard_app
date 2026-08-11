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
        Schema::create('ba_pemeriksaan_headers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_ba')->unique();
            $table->string('periode')->nullable();
            $table->string('pic_pemeriksaan')->nullable();
            $table->enum('status', ['draft', 'done'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ba_pemeriksaan_headers');
    }
};
