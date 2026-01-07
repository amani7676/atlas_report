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
            $table->date('welcome_start_date')->nullable()->comment('تاریخ شروع ارسال پیام‌های خوش‌آمدگویی');
            $table->integer('welcome_check_interval_minutes')->default(1)->comment('فاصله زمانی بررسی پیام‌های خوش‌آمدگویی به دقیقه');
            $table->foreignId('welcome_report_id')->nullable()->comment('گزارش پیش‌فرض برای پیام خوش‌آمدگویی');
            $table->boolean('welcome_system_active')->default(false)->comment('آیا سیستم خوش‌آمدگویی فعال است؟');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_start_date',
                'welcome_check_interval_minutes', 
                'welcome_report_id',
                'welcome_system_active'
            ]);
        });
    }
};
