<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stock Opname Branch (SOB).
     */
    public function up(): void
    {
        // 1. Sesi Opname Cabang
        Schema::create('sob_opname_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('branch_code', 50)->index();
            $table->string('session_code', 50)->unique();
            $table->string('session_name', 150);
            $table->date('session_date');
            $table->enum('count_mode', ['SINGLE', 'DOUBLE'])->default('DOUBLE');
            $table->enum('status', ['DRAFT', 'ACTIVE', 'RECOUNTING', 'CLOSED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Master Area / Zona Fisik Cabang
        Schema::create('sob_zones', function (Blueprint $table) {
            $table->id();
            $table->string('branch_code', 50)->index();
            $table->string('zone_code', 50)->index();
            $table->string('zone_name', 100);
            $table->enum('zone_type', ['GUDANG', 'DISPLAY', 'RETUR_BS', 'TRANSIT', 'OTHER'])->default('GUDANG');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Penugasan User & Tim
        Schema::create('sob_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('team_role', ['SINGLE', 'TEAM_A', 'TEAM_B', 'RECOUNT']);
            $table->enum('status', ['ASSIGNED', 'IN_PROGRESS', 'COMPLETED'])->default('ASSIGNED');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
            $table->foreign('zone_id')->references('id')->on('sob_zones')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 4. Header Titik Sisir / No Stiker Tag
        Schema::create('sob_sweep_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('zone_id');
            $table->string('tag_number', 50)->index();
            $table->string('location_marker', 150)->nullable();
            $table->unsignedInteger('sweep_sequence')->default(1);
            $table->enum('status_team_a', ['PENDING', 'COMPLETED'])->default('PENDING');
            $table->unsignedBigInteger('team_a_user_id')->nullable();
            $table->timestamp('team_a_at')->nullable();
            $table->enum('status_team_b', ['PENDING', 'COMPLETED'])->default('PENDING');
            $table->unsignedBigInteger('team_b_user_id')->nullable();
            $table->timestamp('team_b_at')->nullable();
            $table->enum('comparison_status', ['PENDING', 'MATCH', 'MISMATCH'])->default('PENDING');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
            $table->foreign('zone_id')->references('id')->on('sob_zones')->onDelete('cascade');
            $table->foreign('team_a_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('team_b_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['session_id', 'tag_number'], 'uk_sob_session_tag');
        });

        // 5. Transaksi Scan Fisik Hasil Sisir
        Schema::create('sob_sweep_counts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('header_id');
            $table->string('tag_number', 50)->index();
            $table->enum('team_role', ['SINGLE', 'TEAM_A', 'TEAM_B', 'RECOUNT'])->index();
            $table->unsignedBigInteger('counted_by');
            $table->string('product_id', 50)->index();
            $table->string('scanned_barcode', 100)->nullable();
            $table->enum('stock_condition', ['GOOD', 'BAD', 'EXPIRED'])->default('GOOD');
            $table->integer('qty_karton')->default(0);
            $table->integer('qty_pcs')->default(0);
            $table->integer('uom')->default(1);
            $table->integer('final_qty')->default(0);
            $table->integer('round_number')->default(1);
            $table->string('notes', 255)->nullable();
            $table->timestamp('offline_created_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
            $table->foreign('header_id')->references('id')->on('sob_sweep_headers')->onDelete('cascade');
            $table->foreign('counted_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tag_number', 'team_role'], 'idx_sob_tag_team');
        });

        // 6. Hasil Komparasi per Tag (Team A vs Team B)
        Schema::create('sob_tag_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('header_id');
            $table->string('tag_number', 50)->index();
            $table->string('product_id', 50)->index();
            $table->enum('stock_condition', ['GOOD', 'BAD', 'EXPIRED'])->default('GOOD');
            $table->integer('single_qty')->nullable();
            $table->integer('team_a_qty')->nullable();
            $table->integer('team_b_qty')->nullable();
            $table->integer('diff_qty')->nullable();
            $table->boolean('is_match')->default(false);
            $table->integer('recount_qty')->nullable();
            $table->unsignedBigInteger('recounted_by')->nullable();
            $table->integer('final_approved_qty')->default(0);
            $table->string('approval_notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
            $table->foreign('header_id')->references('id')->on('sob_sweep_headers')->onDelete('cascade');
            $table->foreign('recounted_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['session_id', 'tag_number', 'product_id', 'stock_condition'], 'uk_sob_tag_product');
        });

        // 7. Snapshot Saldo Buku Sistem Cabang
        Schema::create('sob_stock_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('branch_code', 50)->index();
            $table->string('product_id', 50)->index();
            $table->integer('system_qty')->default(0);
            $table->decimal('cogs_unit_price', 15, 2)->default(0.00);
            $table->timestamp('snapshot_at')->useCurrent();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
        });

        // 8. Rekonsiliasi Akhir Selisih Stok Cabang
        Schema::create('sob_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('product_id', 50)->index();
            $table->integer('system_qty')->default(0);
            $table->integer('physical_qty')->default(0);
            $table->integer('diff_qty')->default(0);
            $table->enum('variance_status', ['MATCH', 'OVER', 'SHORT'])->default('MATCH');
            $table->decimal('cogs_unit_price', 15, 2)->default(0.00);
            $table->decimal('total_diff_value', 18, 2)->default(0.00);
            $table->enum('status', ['DRAFT', 'APPROVED', 'ADJUSTED'])->default('DRAFT');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('sob_opname_sessions')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['session_id', 'product_id'], 'uk_sob_rec_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sob_reconciliations');
        Schema::dropIfExists('sob_stock_snapshots');
        Schema::dropIfExists('sob_tag_results');
        Schema::dropIfExists('sob_sweep_counts');
        Schema::dropIfExists('sob_sweep_headers');
        Schema::dropIfExists('sob_assignments');
        Schema::dropIfExists('sob_zones');
        Schema::dropIfExists('sob_opname_sessions');
    }
};
