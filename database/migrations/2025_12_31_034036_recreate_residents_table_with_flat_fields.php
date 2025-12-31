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
            // اگر خطا داد، ادامه بده
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
        
        // ایجاد جدول جدید با فیلدهای flat (تمام فیلدهای resident و contract به صورت یکسان)
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API');
            
            // === تمام فیلدهای resident از API (به صورت یکسان) ===
            $table->string('full_name')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->string('national_code')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamp('registered_at')->nullable();
            
            // === تمام فیلدهای contract از API (به صورت یکسان، بدون prefix) ===
            // توجه: فیلدهای contract با همان نام ذخیره می‌شوند (مثل start_date، end_date، نه contract_start_date)
            $table->date('start_date')->nullable()->comment('تاریخ شروع قرارداد');
            $table->date('end_date')->nullable()->comment('تاریخ پایان قرارداد');
            $table->date('expiry_date')->nullable()->comment('تاریخ انقضای قرارداد');
            $table->decimal('amount', 15, 2)->nullable()->comment('مبلغ قرارداد');
            $table->string('contract_type')->nullable()->comment('نوع قرارداد');
            $table->text('contract_notes')->nullable()->comment('یادداشت قرارداد');
            $table->boolean('contract_is_active')->nullable()->default(true)->comment('قرارداد فعال است');
            
            // === فیلدهای unit, room, bed برای ارتباط ===
            $table->integer('unit_id')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('unit_code')->nullable();
            $table->integer('room_id')->nullable();
            $table->string('room_name')->nullable();
            $table->integer('bed_id')->nullable();
            $table->string('bed_name')->nullable();
            
            // === فیلد JSON برای ذخیره فیلدهای اضافی که در API می‌آیند ===
            $table->json('extra_data')->nullable()->comment('فیلدهای اضافی از API');
            
            // زمان آخرین همگام‌سازی
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // ایندکس‌ها
            $table->index('resident_id');
            $table->index('full_name');
            $table->index('phone');
            $table->index('national_id');
            $table->index('unit_id');
            $table->index('room_id');
            $table->index('bed_id');
            $table->index('start_date');
            $table->index('end_date');
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
    }
};