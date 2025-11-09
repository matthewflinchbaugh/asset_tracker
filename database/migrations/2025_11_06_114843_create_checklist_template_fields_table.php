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
    Schema::create('checklist_template_fields', function (Blueprint $table) {
        $table->id();

        // Link to the template this field belongs to
        $table->foreignId('checklist_template_id')->constrained('checklist_templates')->onDelete('cascade');

        $table->string('label'); // e.g., "Amps L1", "Filter Check"

        // Defines what kind of form input to show
        $table->enum('field_type', [
            'numeric',  // For Amps, Volts, Resistance
            'text',     // For short notes
            'pass_fail',// For a Pass/Fail dropdown
            'checkbox'  // For "Bearings Greased"
        ]);

        $table->integer('display_order')->default(0); // To keep fields in order
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_template_fields');
    }
};
