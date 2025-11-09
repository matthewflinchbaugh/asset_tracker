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
            // We use ->nullable()->change() to alter the column definition.
            // This allows the asset to be created with a TEMP-ID while waiting for Admin to assign the permanent ID.
            $table->string('asset_tag_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Revert back to not nullable
            $table->string('asset_tag_id')->nullable(false)->change();
        });
    }
};
