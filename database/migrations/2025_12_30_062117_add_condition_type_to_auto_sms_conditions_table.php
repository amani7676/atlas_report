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
        Schema::table('auto_sms_conditions', function (Blueprint $table) {
            $table->enum('condition_type', ['inter', 'check', 'change'])->default('inter')->after('auto_sms_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_sms_conditions', function (Blueprint $table) {
            $table->dropColumn('condition_type');
        });
    }
};
