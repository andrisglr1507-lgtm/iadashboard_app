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
        Schema::create('bad_stock_records', function (Blueprint $table) {
            $table->id();
            $table->string('sku_code', 50)->index();
            $table->integer('qty_carton')->default(0);
            $table->integer('qty_pcs')->default(0);
            $table->integer('total_qty')->default(0);
            $table->enum('item_status', ['SALEABLE', 'NON_SALEABLE'])->default('NON_SALEABLE');
            $table->enum('category', ['BAD_STOCK', 'EXPIRED_STOCK'])->default('BAD_STOCK');
            $table->string('batch_no', 100)->nullable();
            $table->date('expired_date')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_stock_records');
    }
};
