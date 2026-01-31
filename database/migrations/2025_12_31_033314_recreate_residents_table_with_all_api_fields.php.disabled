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
        
        // ایجاد جدول جدید با تمام فیلدهای API به صورت ستون جداگانه
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API');
            
            // === فیلدهای اصلی resident از API ===
            $table->string('full_name')->nullable()->comment('نام کامل');
            $table->string('name')->nullable()->comment('نام (نام دیگر)');
            $table->string('phone')->nullable()->comment('شماره تلفن');
            $table->string('national_id')->nullable()->comment('کد ملی');
            $table->string('national_code')->nullable()->comment('کد ملی (نام دیگر)');
            $table->string('email')->nullable()->comment('ایمیل');
            $table->date('birth_date')->nullable()->comment('تاریخ تولد');
            $table->string('gender')->nullable()->comment('جنسیت');
            $table->text('address')->nullable()->comment('آدرس');
            $table->string('postal_code')->nullable()->comment('کد پستی');
            $table->string('city')->nullable()->comment('شهر');
            $table->string('province')->nullable()->comment('استان');
            $table->string('country')->nullable()->comment('کشور');
            $table->text('notes')->nullable()->comment('یادداشت');
            $table->boolean('is_active')->nullable()->default(true)->comment('فعال/غیرفعال');
            $table->timestamp('registered_at')->nullable()->comment('تاریخ ثبت‌نام');
            
            // === فیلدهای contract از API ===
            $table->date('contract_start_date')->nullable()->comment('تاریخ شروع قرارداد');
            $table->date('contract_end_date')->nullable()->comment('تاریخ پایان قرارداد');
            $table->date('contract_expiry_date')->nullable()->comment('تاریخ انقضای قرارداد');
            $table->decimal('contract_amount', 15, 2)->nullable()->comment('مبلغ قرارداد');
            $table->string('contract_type')->nullable()->comment('نوع قرارداد');
            $table->text('contract_notes')->nullable()->comment('یادداشت قرارداد');
            $table->boolean('contract_is_active')->nullable()->default(true)->comment('قرارداد فعال است');
            
            // === فیلدهای unit از API ===
            $table->integer('unit_id')->nullable()->comment('شناسه واحد');
            $table->string('unit_name')->nullable()->comment('نام واحد');
            $table->string('unit_code')->nullable()->comment('کد واحد');
            $table->text('unit_description')->nullable()->comment('توضیحات واحد');
            $table->integer('unit_capacity')->nullable()->comment('ظرفیت واحد');
            $table->boolean('unit_is_active')->nullable()->default(true)->comment('واحد فعال است');
            
            // === فیلدهای room از API ===
            $table->integer('room_id')->nullable()->comment('شناسه اتاق');
            $table->string('room_name')->nullable()->comment('نام اتاق');
            $table->string('room_code')->nullable()->comment('کد اتاق');
            $table->text('room_description')->nullable()->comment('توضیحات اتاق');
            $table->integer('room_capacity')->nullable()->comment('ظرفیت اتاق');
            $table->boolean('room_is_active')->nullable()->default(true)->comment('اتاق فعال است');
            
            // === فیلدهای bed از API ===
            $table->integer('bed_id')->nullable()->comment('شناسه تخت');
            $table->string('bed_name')->nullable()->comment('نام تخت');
            $table->string('bed_code')->nullable()->comment('کد تخت');
            $table->text('bed_description')->nullable()->comment('توضیحات تخت');
            $table->boolean('bed_is_active')->nullable()->default(true)->comment('تخت فعال است');
            
            // === فیلد JSON برای ذخیره فیلدهای اضافی که در API می‌آیند اما در جدول نیستند ===
            $table->json('extra_data')->nullable()->comment('فیلدهای اضافی از API که در جدول تعریف نشده‌اند');
            
            // زمان آخرین همگام‌سازی
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // ایندکس‌ها
            $table->index('resident_id');
            $table->index('full_name');
            $table->index('phone');
            $table->index('national_id');
            $table->index('national_code');
            $table->index('unit_id');
            $table->index('room_id');
            $table->index('bed_id');
            $table->index('contract_start_date');
            $table->index('contract_end_date');
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
            
            if (Schema::hasTable('auto_sms_logs')) {
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
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            // ادامه بده
        }

        // Drop کردن جدول جدید
        Schema::dropIfExists('residents');
        
        // ایجاد جدول قبلی (با ساختار JSON)
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API');
            $table->json('api_data')->nullable()->comment('تمام داده‌های API');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
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
        
        if (Schema::hasTable('auto_sms_logs') && Schema::hasColumn('auto_sms_logs', 'resident_id')) {
            Schema::table('auto_sms_logs', function (Blueprint $table) {
                $table->foreign('resident_id')
                    ->references('id')
                    ->on('residents')
                    ->onDelete('set null');
            });
        }
    }
};