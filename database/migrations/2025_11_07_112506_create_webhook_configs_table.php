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
        Schema::create('webhook_configs', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            // Event types: ASSET_CREATED, LOG_ADDED, ASSET_UPDATED, etc.
            $table->string('event_type'); 
            // JSON column to store array of fields to include in the payload
            $table->json('fields_to_include'); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_configs');
    }
};
