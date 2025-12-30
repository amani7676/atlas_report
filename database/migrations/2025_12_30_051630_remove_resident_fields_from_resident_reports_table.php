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
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->dropColumn(['resident_name', 'unit_name', 'room_name', 'bed_name', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->string('resident_name')->nullable()->after('resident_id');
            $table->string('unit_name')->nullable()->after('unit_id');
            $table->string('room_name')->nullable()->after('room_id');
            $table->string('bed_name')->nullable()->after('bed_id');
            $table->string('phone')->nullable()->after('bed_name');
        });
    }
};
