<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asset_checklist_template')) {
        Schema::create('asset_checklist_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->onDelete('cascade');
            // Allows distinguishing multiple uses of the same template on one asset (e.g., Motor 1, Motor 2).
            $table->string('component_name')->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_checklist_template');
    }
};
