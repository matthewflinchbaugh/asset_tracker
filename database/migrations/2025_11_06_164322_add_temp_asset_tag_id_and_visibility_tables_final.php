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
        // Add the temporary ID field to assets table
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'temp_asset_tag_id')) {
                $table->string('temp_asset_tag_id')->nullable()->unique()->after('asset_tag_id');
            }
        });

        // Create the user_category_visibility pivot table
        if (!Schema::hasTable('user_category_visibility')) {
            Schema::create('user_category_visibility', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->primary(['user_id', 'category_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'temp_asset_tag_id')) {
                $table->dropColumn('temp_asset_tag_id');
            }
        });
        Schema::dropIfExists('user_category_visibility');
    }
};
