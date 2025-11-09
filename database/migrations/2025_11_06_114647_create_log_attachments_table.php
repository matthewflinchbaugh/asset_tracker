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
    Schema::create('log_attachments', function (Blueprint $table) {
        $table->id();

        // Link to the maintenance log this file belongs to
        $table->foreignId('maintenance_log_id')->constrained('maintenance_logs')->onDelete('cascade');

        $table->string('file_path'); // e.g., "attachments/log_123_photo.jpg"
        $table->string('original_file_name');
        $table->string('file_type')->nullable(); // e.g., 'image/jpeg', 'application/pdf'

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_attachments');
    }
};
