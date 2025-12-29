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
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // حذف foreign key و index قبلی
            $table->dropForeign(['sms_message_id']);
            $table->dropIndex(['sms_message_id', 'status']);
            
            // تغییر فیلد به nullable
            $table->unsignedBigInteger('sms_message_id')->nullable()->change();
            
            // اضافه کردن foreign key و index جدید
            $table->foreign('sms_message_id')->references('id')->on('sms_messages')->onDelete('cascade');
            $table->index(['sms_message_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // حذف foreign key و index
            $table->dropForeign(['sms_message_id']);
            $table->dropIndex(['sms_message_id', 'status']);
            
            // برگرداندن به حالت غیر nullable
            $table->unsignedBigInteger('sms_message_id')->nullable(false)->change();
            
            // اضافه کردن foreign key و index قبلی
            $table->foreign('sms_message_id')->references('id')->on('sms_messages')->onDelete('cascade');
            $table->index(['sms_message_id', 'status']);
        });
    }
};
