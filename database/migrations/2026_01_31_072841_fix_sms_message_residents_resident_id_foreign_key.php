<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // حذف foreign key ها با استفاده از دستورات SQL مستقیم
        try {
            DB::statement('ALTER TABLE sms_message_residents DROP FOREIGN KEY sms_message_residents_resident_id_foreign');
        } catch (\Exception $e) {
            // اگر foreign key وجود نداشت، نادیده بگیر
        }
        
        try {
            DB::statement('ALTER TABLE resident_reports DROP FOREIGN KEY resident_reports_resident_id_foreign');
        } catch (\Exception $e) {
            // اگر foreign key وجود نداشت، نادیده بگیر
        }
        
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // تغییر resident_id به integer برای API ID
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API (نه ID جدول residents)')->change();
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            // تغییر resident_id به integer برای API ID
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API (نه ID جدول residents)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // در صورت نیاز می‌توان foreign key را دوباره اضافه کرد
            // اما فعلاً فقط نوع ستون را تغییر می‌دهیم
            $table->unsignedBigInteger('resident_id')->nullable()->change();
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id')->nullable()->change();
        });
    }
};
