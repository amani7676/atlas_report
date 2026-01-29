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
        Schema::table('pattern_variables', function (Blueprint $table) {
            // حذف محدودیت یکتایی کد تا بتوانیم کدهای تکراری برای الگوهای مختلف داشته باشیم
            $table->dropUnique('pattern_variables_code_unique');
            
            // اضافه کردن ایندکس برای جستجوی بهتر
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pattern_variables', function (Blueprint $table) {
            // بازگرداندن محدودیت یکتایی
            $table->unique('code');
            $table->dropIndex('pattern_variables_code_index');
        });
    }
};
