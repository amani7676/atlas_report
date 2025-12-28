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
        Schema::create('pattern_variables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('کد متغیر (مثل {0}, {1})');
            $table->string('title')->comment('عنوان متغیر');
            $table->string('table_field')->comment('نام فیلد در جدول دیتابیس (مثل fullname, phone)');
            $table->string('table_name')->nullable()->comment('نام جدول (مثل residents, reports)');
            $table->enum('variable_type', ['user', 'report', 'general'])->default('user')->comment('نوع متغیر');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0)->comment('ترتیب نمایش');
            $table->timestamps();
            
            $table->index(['variable_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pattern_variables');
    }
};
