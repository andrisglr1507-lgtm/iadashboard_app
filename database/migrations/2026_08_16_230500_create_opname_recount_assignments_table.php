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
        Schema::create('opname_recount_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('previous_assignment_id')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->string('location_code', 100);
            $table->string('id_product', 100);
            $table->integer('round_number')->default(1);
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('assigned_by');
            $table->string('status', 50)->default('PENDING'); // PENDING, ASSIGNED, IN_PROGRESS, COMPLETED
            $table->boolean('is_final')->default(false);
            $table->text('evaluation_result')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('distributed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opname_recount_assignments');
    }
};
