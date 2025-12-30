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
            // تغییر field_type از enum به string
            $table->string('field_type', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_sms_conditions', function (Blueprint $table) {
            // برگرداندن به enum (فقط برای rollback)
            $table->enum('field_type', ['resident', 'resident_report', 'report'])->change();
        });
    }
};
