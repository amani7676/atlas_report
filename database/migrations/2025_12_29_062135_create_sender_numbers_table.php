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
        Schema::create('sender_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique()->comment('شماره فرستنده');
            $table->string('title')->comment('عنوان شماره');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->string('api_key')->nullable()->comment('API Key مرتبط با این شماره (اختیاری)');
            $table->boolean('is_active')->default(true)->comment('فعال/غیرفعال');
            $table->boolean('is_pattern')->default(false)->comment('آیا برای پیامک‌های الگویی استفاده می‌شود؟');
            $table->integer('priority')->default(0)->comment('اولویت (عدد بالاتر = اولویت بیشتر)');
            $table->timestamps();
            
            $table->index(['is_active', 'is_pattern']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sender_numbers');
    }
};
