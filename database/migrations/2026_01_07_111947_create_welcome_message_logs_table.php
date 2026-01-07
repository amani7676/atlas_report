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
        Schema::create('welcome_message_logs', function (Blueprint $table) {
            $table->id();
            
            // ارتباط با پیام خوش‌آمدگویی
            $table->foreignId('welcome_message_id')->nullable()->constrained()->onDelete('set null');
            
            // اطلاعات اقامت‌گر
            $table->string('resident_id', 50)->comment('شناسه اقامت‌گر');
            $table->string('resident_name', 255)->comment('نام اقامت‌گر');
            $table->string('resident_phone', 20)->comment('تلفن اقامت‌گر');
            
            // وضعیت ارسال
            $table->string('status', 20)->default('pending')->comment('pending, sent, failed');
            $table->text('error_message')->nullable()->comment('پیام خطا در صورت شکست');
            
            // اطلاعات پیامک
            $table->string('rec_id', 50)->nullable()->comment('شناسه ارسال ملی پیامک');
            $table->string('response_code', 20)->nullable()->comment('کد پاسخ API');
            $table->json('api_response')->nullable()->comment('پاسخ کامل API');
            $table->text('raw_response')->nullable()->comment('پاسخ خام API');
            
            // فیلدهای زمانی
            $table->timestamp('sent_at')->nullable()->comment('زمان ارسال پیامک');
            $table->timestamps();
            $table->softDeletes();
            
            // ایندکس‌ها
            $table->index(['welcome_message_id']);
            $table->index(['resident_id']);
            $table->index(['status']);
            $table->index(['sent_at']);
            $table->index(['rec_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_message_logs');
    }
};
