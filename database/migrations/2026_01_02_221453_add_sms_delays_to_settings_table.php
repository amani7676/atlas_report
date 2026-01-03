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
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('sms_delay_before_start')->default(2)->after('api_url')->comment('تاخیر قبل از شروع ارسال پیامک (ثانیه)');
            $table->integer('sms_delay_between_messages')->default(200)->after('sms_delay_before_start')->comment('تاخیر بین هر پیامک (میلی‌ثانیه)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['sms_delay_before_start', 'sms_delay_between_messages']);
        });
    }
};
