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
        // 1. ASSETS table: Drop and re-add created_by_user_id as nullable and SET NULL on delete
        Schema::table('assets', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign('assets_created_by_user_id_foreign');
            // Re-add with SET NULL
            $table->foreignId('created_by_user_id')->nullable()->change()->constrained('users')->onDelete('set null');
        });

        // 2. MAINTENANCE_LOGS table: Drop and re-add user_id as nullable and SET NULL on delete
        Schema::table('maintenance_logs', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign('maintenance_logs_user_id_foreign');
            // Re-add with SET NULL
            $table->foreignId('user_id')->nullable()->change()->constrained('users')->onDelete('set null');
        });

        // 3. PROPOSALS table: Drop and re-add user_id and reviewed_by_user_id as nullable and SET NULL on delete
        Schema::table('proposals', function (Blueprint $table) {
            // Drop old foreign key constraints
            $table->dropForeign('proposals_user_id_foreign');
            $table->dropForeign('proposals_reviewed_by_user_id_foreign');
            
            // Re-add user_id with SET NULL
            $table->foreignId('user_id')->nullable()->change()->constrained('users')->onDelete('set null');
            
            // Re-add reviewed_by_user_id with SET NULL
            $table->foreignId('reviewed_by_user_id')->nullable()->change()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original behavior (ON DELETE RESTRICT/CASCADE)
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign('assets_created_by_user_id_foreign');
            $table->foreignId('created_by_user_id')->nullable(false)->change()->constrained('users');
        });
        Schema::table('maintenance_logs', function (Blueprint $table) {
            $table->dropForeign('maintenance_logs_user_id_foreign');
            $table->foreignId('user_id')->nullable(false)->change()->constrained('users');
        });
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign('proposals_user_id_foreign');
            $table->dropForeign('proposals_reviewed_by_user_id_foreign');
            $table->foreignId('user_id')->nullable(false)->change()->constrained('users');
            $table->foreignId('reviewed_by_user_id')->nullable(false)->change()->constrained('users');
        });
    }
};
