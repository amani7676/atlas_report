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
            
            // بررسی و drop کردن foreign key از resident_grants
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'resident_grants' 
                AND COLUMN_NAME = 'resident_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE resident_grants DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
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
        
        // ایجاد جدول جدید با تمام فیلدهای API
        // توجه: فیلدها دقیقاً مثل API هستند - بدون تغییر نام
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر از API (resident.id)');
            
            // === فیلدهای resident از API (دقیقاً مثل API) ===
            // تمام فیلدهای موجود در $resident از API
            $table->string('full_name')->nullable()->comment('نام کامل از API');
            $table->string('name')->nullable()->comment('نام از API');
            $table->string('phone')->nullable()->comment('تلفن از API');
            $table->string('national_id')->nullable()->comment('کد ملی از API');
            $table->string('national_code')->nullable()->comment('کد ملی (نام دیگر) از API');
            $table->string('email')->nullable()->comment('ایمیل از API');
            $table->date('birth_date')->nullable()->comment('تاریخ تولد از API (birth_date یا birthday)');
            $table->string('gender')->nullable()->comment('جنسیت از API');
            $table->text('address')->nullable()->comment('آدرس از API');
            $table->string('postal_code')->nullable()->comment('کد پستی از API');
            $table->string('city')->nullable()->comment('شهر از API');
            $table->string('province')->nullable()->comment('استان از API');
            $table->string('country')->nullable()->comment('کشور از API');
            $table->text('notes')->nullable()->comment('یادداشت از API');
            $table->boolean('is_active')->nullable()->default(true)->comment('فعال/غیرفعال از API (is_active یا active)');
            $table->timestamp('registered_at')->nullable()->comment('تاریخ ثبت از API (registered_at یا created_at)');
            
            // === فیلدهای contract از API (دقیقاً مثل API - از $contract) ===
            // تمام فیلدهای موجود در $contract از API - بدون prefix
            $table->date('start_date')->nullable()->comment('تاریخ شروع قرارداد از API (contract.start_date یا resident.start_date)');
            $table->date('end_date')->nullable()->comment('تاریخ پایان قرارداد از API (contract.end_date یا resident.end_date)');
            $table->date('expiry_date')->nullable()->comment('تاریخ انقضای قرارداد از API (contract.expiry_date یا resident.expiry_date)');
            $table->decimal('amount', 15, 2)->nullable()->comment('مبلغ قرارداد از API (contract.amount یا resident.amount)');
            $table->string('contract_type')->nullable()->comment('نوع قرارداد از API (contract.type یا resident.contract_type)');
            $table->text('contract_notes')->nullable()->comment('یادداشت قرارداد از API (contract.notes)');
            $table->boolean('contract_is_active')->nullable()->default(true)->comment('وضعیت قرارداد از API (contract.is_active یا contract.active)');
            
            // === فیلدهای unit از API (دقیقاً مثل API - از $unitData) ===
            // تمام فیلدهای موجود در $unitData از API
            $table->integer('unit_id')->nullable()->comment('شناسه واحد از API (unit.id)');
            $table->string('unit_name')->nullable()->comment('نام واحد از API (unit.name)');
            $table->string('unit_code')->nullable()->comment('کد واحد از API (unit.code)');
            
            // === فیلدهای room از API (دقیقاً مثل API - از $roomData) ===
            // تمام فیلدهای موجود در $roomData از API
            $table->integer('room_id')->nullable()->comment('شناسه اتاق از API (room.id)');
            $table->string('room_name')->nullable()->comment('نام اتاق از API (room.name)');
            
            // === فیلدهای bed از API (دقیقاً مثل API - از $bedData) ===
            // تمام فیلدهای موجود در $bedData از API
            $table->integer('bed_id')->nullable()->comment('شناسه تخت از API (bed.id)');
            $table->string('bed_name')->nullable()->comment('نام تخت از API (bed.name)');
            
            // === فیلد JSON برای ذخیره تمام فیلدهای اضافی از API ===
            // این فیلد شامل تمام فیلدهایی است که در API هستند اما به صورت column تعریف نشده‌اند
            $table->json('api_data')->nullable()->comment('تمام داده‌های خام از API (resident, contract, unit, room, bed با تمام فیلدهایشان)');
            
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
            $table->index('expiry_date');
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
        
        // اضافه کردن foreign key به resident_grants
        // چون جدول residents دوباره ساخته شده، ابتدا تمام داده‌های نامعتبر resident_grants را پاک می‌کنیم
        if (Schema::hasTable('resident_grants') && Schema::hasColumn('resident_grants', 'resident_id')) {
            try {
                // اگر جدول residents خالی است یا ردیف‌هایی در resident_grants هستند که در residents جدید وجود ندارند، آن‌ها را پاک می‌کنیم
                $existingResidentIds = DB::table('residents')->pluck('resident_id')->toArray();
                if (empty($existingResidentIds)) {
                    // اگر residents خالی است، تمام داده‌های resident_grants را پاک می‌کنیم
                    DB::table('resident_grants')->delete();
                } else {
                    // فقط ردیف‌هایی که resident_id آن‌ها در جدول جدید وجود ندارد را پاک می‌کنیم
                    DB::table('resident_grants')
                        ->whereNotIn('resident_id', $existingResidentIds)
                        ->delete();
                }
            } catch (\Exception $e) {
                // اگر خطا داد، ادامه بده
            }
            
            // حالا foreign key را اضافه می‌کنیم
            try {
                Schema::table('resident_grants', function (Blueprint $table) {
                    // بررسی می‌کنیم که آیا foreign key از قبل وجود دارد یا نه
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'resident_grants' 
                        AND COLUMN_NAME = 'resident_id'
                        AND REFERENCED_TABLE_NAME = 'residents'
                    ");
                    
                    if (empty($foreignKeys)) {
                        $table->foreign('resident_id')
                            ->references('resident_id')
                            ->on('residents')
                            ->onDelete('cascade');
                    }
                });
            } catch (\Exception $e) {
                // اگر foreign key از قبل وجود دارد یا خطا داد، skip می‌کنیم
            }
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
            
            if (Schema::hasTable('resident_grants')) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'resident_grants' 
                    AND COLUMN_NAME = 'resident_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE resident_grants DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
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
