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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Who proposed it
            $table->string('asset_name');
            $table->text('reason');
            $table->string('estimated_cost')->nullable();
            $table->string('status')->default('pending'); // pending, approved, denied
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users'); // Admin who reviewed it
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
