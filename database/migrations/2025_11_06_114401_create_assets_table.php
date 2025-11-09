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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // --- Foreign Keys (linking to other tables) ---
            
            // Link to the 'departments' table.
            $table->foreignId('department_id')->constrained('departments');

            // Link to the 'categories' table.
            $table->foreignId('category_id')->constrained('categories');
            
            // Link to the 'users' table (who created it).
            $table->foreignId('created_by_user_id')->constrained('users');

            // --- Asset ID & Status ---
            // We'll store the generated ID like 'EXT-00001'
            $table->string('asset_tag_id')->unique();
            
            // Status for approval workflow and decommissioning
            $table->enum('status', ['pending_approval', 'active', 'decommissioned'])->default('pending_approval');

            // --- Core Asset Details ---
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->text('documentation_link')->nullable();
            
            // --- Financial & Date Tracking ---
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiration_date')->nullable(); // Good to have!

            // --- Preventive Maintenance (PM) Fields ---
            $table->integer('pm_interval_value')->nullable(); // e.g., "6"
            $table->enum('pm_interval_unit', ['days', 'weeks', 'months', 'years'])->nullable(); // e.g., "Months"
            $table->date('next_pm_due_date')->nullable();
            $table->text('pm_procedure_notes')->nullable(); // For the simple checklist

            $table->timestamps(); // Adds 'created_at' and 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
