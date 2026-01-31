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
            // تغییر توضیح ستون resident_id برای مشخص شدن اینکه این ID از API است
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API (نه ID جدول residents)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // بازگرداندن به حالت قبلی
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API')->change();
        });
    }
};
