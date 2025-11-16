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
		    $table->boolean('temporarily_out_of_service')
		          ->default(false)
		          ->after('status');
	    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
	    Schema::table('assets', function (Blueprint $table) {
    		$table->dropColumn('temporarily_out_of_service');
	    });

    }
};
