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
        // ابتدا foreign key ها را drop کنیم
        // بررسی و drop کردن foreign key از sms_message_residents
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'sms_message_residents' 
                AND COLUMN_NAME = 'resident_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE sms_message_residents DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
            
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'resident_reports' 
                AND COLUMN_NAME = 'resident_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE resident_reports DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
            
            // بررسی و drop کردن foreign key از auto_sms_logs
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'auto_sms_logs' 
                AND COLUMN_NAME = 'resident_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE auto_sms_logs DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            // اگر خطا داد، ادامه بده (ممکن است foreign key وجود نداشته باشد)
        }

        // null کردن foreign key ها قبل از drop کردن جدول
        try {
            DB::statement('UPDATE sms_message_residents SET resident_id = NULL WHERE resident_id IS NOT NULL');
            DB::statement('UPDATE resident_reports SET resident_id = NULL WHERE resident_id IS NOT NULL');
            if (Schema::hasTable('auto_sms_logs')) {
                DB::statement('UPDATE auto_sms_logs SET resident_id = NULL WHERE resident_id IS NOT NULL');
            }
        } catch (\Exception $e) {
            // اگر خطا داد، ادامه بده
        }

        // Drop کردن جدول قدیمی
        Schema::dropIfExists('residents');
        
        // ایجاد جدول جدید با ساختار ساده
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API');
            
            // ذخیره تمام داده‌های API به صورت JSON
            // این فیلد شامل تمام فیلدهای موجود در API است
            $table->json('api_data')->nullable()->comment('تمام داده‌های API (resident, unit, room, bed, contract و ...)');
            
            // زمان آخرین همگام‌سازی
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // ایندکس‌ها
            $table->index('resident_id');
            $table->index('last_synced_at');
        });
        
        // اضافه کردن foreign key ها دوباره
        if (Schema::hasColumn('sms_message_residents', 'resident_id')) {
            Schema::table('sms_message_residents', function (Blueprint $table) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            });
        }
        
        if (Schema::hasColumn('resident_reports', 'resident_id')) {
            Schema::table('resident_reports', function (Blueprint $table) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            });
        }
        
        // اضافه کردن foreign key به auto_sms_logs
        if (Schema::hasTable('auto_sms_logs') && Schema::hasColumn('auto_sms_logs', 'resident_id')) {
            Schema::table('auto_sms_logs', function (Blueprint $table) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop کردن foreign key ها
        Schema::table('sms_message_residents', function (Blueprint $table) {
            if (Schema::hasColumn('sms_message_residents', 'resident_id')) {
                $table->dropForeign(['resident_id']);
            }
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            if (Schema::hasColumn('resident_reports', 'resident_id')) {
                $table->dropForeign(['resident_id']);
            }
        });

        // Drop کردن جدول جدید
        Schema::dropIfExists('residents');
        
        // ایجاد جدول قدیمی (ساختار قبلی)
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API');
            
            // اطلاعات اصلی اقامت‌گر
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable()->comment('کد ملی');
            $table->string('national_code')->nullable()->comment('کد ملی (نام دیگر)');
            
            // اطلاعات واحد
            $table->integer('unit_id')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('unit_code')->nullable();
            
            // اطلاعات اتاق
            $table->integer('room_id')->nullable();
            $table->string('room_name')->nullable();
            
            // اطلاعات تخت
            $table->integer('bed_id')->nullable();
            $table->string('bed_name')->nullable();
            
            // تاریخ‌های قرارداد
            $table->date('contract_start_date')->nullable()->comment('تاریخ شروع قرارداد');
            $table->date('contract_end_date')->nullable()->comment('تاریخ پایان قرارداد');
            $table->date('contract_expiry_date')->nullable()->comment('تاریخ انقضای قرارداد');
            
            // ذخیره تمام داده‌های خام API به صورت JSON
            $table->json('resident_data')->nullable()->comment('تمام داده‌های resident و contract از API');
            $table->json('unit_data')->nullable()->comment('تمام داده‌های unit از API');
            $table->json('room_data')->nullable()->comment('تمام داده‌های room از API');
            $table->json('bed_data')->nullable()->comment('تمام داده‌های bed از API');
            $table->json('extra_data')->nullable()->comment('سایر فیلدهای اضافی');
            
            // زمان آخرین همگام‌سازی
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // ایندکس‌ها
            $table->index('resident_id');
            $table->index('unit_id');
            $table->index('room_id');
            $table->index('bed_id');
            $table->index('phone');
            $table->index('last_synced_at');
        });
        
        // اضافه کردن foreign key ها دوباره
        Schema::table('sms_message_residents', function (Blueprint $table) {
            if (Schema::hasColumn('sms_message_residents', 'resident_id')) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            }
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            if (Schema::hasColumn('resident_reports', 'resident_id')) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            }
        });
    }
};