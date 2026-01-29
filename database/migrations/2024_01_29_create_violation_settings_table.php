<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('violation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // کلید تنظیم
            $table->text('value'); // مقدار تنظیم
            $table->string('description')->nullable(); // توضیحات
            $table->string('type')->default('text'); // نوع: text, number, boolean
            $table->timestamps();
            
            // اضافه کردن ایندکس برای جستجوی سریع
            $table->index('key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('violation_settings');
    }
};
