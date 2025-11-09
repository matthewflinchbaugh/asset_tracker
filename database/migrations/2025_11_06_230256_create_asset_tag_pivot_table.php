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
        // Many-to-Many relationship between assets and tags
        Schema::create('asset_tag', function (Blueprint $table) {
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Keeping old table name for constraint
            $table->primary(['asset_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_tag');
    }
};
