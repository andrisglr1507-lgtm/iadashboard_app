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
        // Alter enum to include HEAD_OPNAME and TEAM_OPNAME
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','MANAGER','SUPERVISOR','WAREHOUSE_HEAD','AUDITOR','OPERATOR','HEAD_OPNAME','TEAM_OPNAME') NOT NULL DEFAULT 'OPERATOR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to original state if needed
        // Note: Reverting enum can fail if there are rows using the new values
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','MANAGER','SUPERVISOR','WAREHOUSE_HEAD','AUDITOR','OPERATOR') NOT NULL DEFAULT 'OPERATOR'");
    }
};
