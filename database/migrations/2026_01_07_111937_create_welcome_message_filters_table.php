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
        Schema::create('welcome_message_filters', function (Blueprint $table) {
            $table->id();
            
            // ارتباط با پیام خوش‌آمدگویی
            $table->foreignId('welcome_message_id')->constrained()->onDelete('cascade');
            
            // فیلدهای فیلتر
            $table->string('field_name', 100)->comment('نام فیلد برای فیلتر');
            $table->string('operator', 20)->comment('عملگر (=, !=, like, >, <, >=, <=, in, not_in, is_null, is_not_null)');
            $table->text('value')->nullable()->comment('مقدار فیلتر');
            $table->string('logical_operator', 10)->default('and')->comment('عملگر منطقی (and/or)');
            $table->integer('priority')->default(0)->comment('اولویت فیلتر');
            
            // فیلدهای زمانی
            $table->timestamps();
            $table->softDeletes();
            
            // ایندکس‌ها
            $table->index(['welcome_message_id']);
            $table->index(['field_name']);
            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_message_filters');
    }
};
