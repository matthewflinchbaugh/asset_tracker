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
    Schema::create('checklist_log_data', function (Blueprint $table) {
        $table->id();

        // Link to the specific maintenance log this data is for
        $table->foreignId('maintenance_log_id')->constrained('maintenance_logs')->onDelete('cascade');

        // Link to the "question" this is the "answer" for
        $table->foreignId('checklist_template_field_id')->constrained('checklist_template_fields')->onDelete('cascade');

        // The actual data saved. We use a 'text' field
        // so it can store "10.2", "Pass", or "true".
        $table->text('value')->nullable(); 

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_log_data');
    }
};
