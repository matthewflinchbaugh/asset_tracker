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
    Schema::create('maintenance_logs', function (Blueprint $table) {
        $table->id();

        // --- Foreign Keys ---
        // Link to the asset this log belongs to
        $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');

        // Link to the user who submitted the log
        // Nullable, in case a 'contractor' (no login) submits it
        $table->foreignId('user_id')->nullable()->constrained('users');

        // --- Log Details ---
        $table->enum('event_type', [
            'commissioning',
            'scheduled_maintenance',
            'unscheduled_repair',
            'inspection',
            'decommissioning'
        ]);

        $table->text('description_of_work');
        $table->timestamp('service_date')->useCurrent(); // When the work was done

        // --- Cost Analysis Fields ---
        $table->decimal('parts_cost', 10, 2)->default(0.00);
        $table->decimal('labor_hours', 8, 2)->default(0.00);

        // --- Status Fields ---
        // For our 'Save as Draft' feature
        $table->boolean('is_draft')->default(false);

        // For the 'Contractor Secure Link' feature
        $table->string('secure_token')->nullable()->unique();
        $table->timestamp('token_expires_at')->nullable();

        $table->timestamps(); // 'created_at' and 'updated_at'
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
