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
            $table->unsignedBigInteger('welcome_pattern_id')->nullable()->after('sms_delay_between_messages')->comment('ID الگوی پیام خوش‌آمدگویی');
            $table->dateTime('welcome_start_datetime')->nullable()->after('welcome_pattern_id')->comment('تاریخ و زمان شروع ارسال پیام‌های خوش‌آمدگویی');
            
            $table->foreign('welcome_pattern_id')->references('id')->on('patterns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['welcome_pattern_id']);
            $table->dropColumn(['welcome_pattern_id', 'welcome_start_datetime']);
        });
    }
};
