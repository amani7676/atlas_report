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
            $table->enum('data_type', ['string', 'number', 'date', 'boolean'])->default('string')->after('field_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_sms_conditions', function (Blueprint $table) {
            $table->dropColumn('data_type');
        });
    }
};
