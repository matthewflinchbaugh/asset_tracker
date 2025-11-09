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
        Schema::table('assets', function (Blueprint $table) {
            // Self-referencing foreign key for parent/child hierarchy
            $table->foreignId('parent_asset_id')->nullable()->constrained('assets')->onDelete('set null')->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['parent_asset_id']);
            $table->dropColumn('parent_asset_id');
        });
    }
};
