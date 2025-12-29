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
        Schema::create('auto_sms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text'); // متن پیام
            $table->enum('send_type', ['immediate', 'scheduled'])->default('immediate');
            $table->dateTime('scheduled_at')->nullable(); // برای ارسال زمان‌دار
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_checked_at')->nullable(); // آخرین بررسی شرط
            $table->dateTime('last_sent_at')->nullable(); // آخرین ارسال
            $table->integer('total_sent')->default(0); // تعداد کل ارسال شده
            $table->text('description')->nullable(); // توضیحات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_sms');
    }
};
