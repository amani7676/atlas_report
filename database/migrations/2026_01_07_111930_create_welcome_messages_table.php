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
        Schema::create('welcome_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('عنوان پیام خوش‌آمدگویی');
            $table->text('description')->nullable()->comment('توضیحات پیام');
            
            // کد الگوی پیامک
            $table->string('pattern_code', 50)->nullable()->comment('کد الگوی پیامک ملی پیامک');
            $table->text('pattern_text')->nullable()->comment('متن الگوی پیامک با متغیرها');
            
            // وضعیت فعال بودن
            $table->boolean('is_active')->default(true)->comment('آیا پیام فعال است؟');
            
            // تنظیمات ارسال
            $table->integer('send_delay_minutes')->default(0)->comment('تأخیر ارسال به دقیقه');
            $table->boolean('send_once_per_resident')->default(true)->comment('فقط یک بار برای هر اقامت‌گر');
            
            // فیلدهای زمانی
            $table->timestamps();
            $table->softDeletes();
            
            // ایندکس‌ها
            $table->index(['is_active']);
            $table->index(['pattern_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_messages');
    }
};
