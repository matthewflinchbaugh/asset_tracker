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
    Schema::create('checklist_templates', function (Blueprint $table) {
        $table->id();

        // Link to the asset this template is for.
        // This means each asset gets its own unique checklist.
        $table->foreignId('asset_id')->unique()->constrained('assets')->onDelete('cascade');

        $table->string('name'); // e.g., "Monthly Motor PM Checklist"
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
