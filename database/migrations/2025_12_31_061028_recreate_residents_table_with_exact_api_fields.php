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
        
        // ایجاد جدول جدید با تمام فیلدهای دقیق API
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            
            // === فیلدهای اصلی ===
            $table->integer('resident_id')->unique()->comment('شناسه اقامت‌گر');
            $table->integer('contract_id')->nullable()->comment('شناسه قرارداد');
            
            // === فیلدهای unit (دقیقاً مثل API) ===
            $table->integer('unit_id')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('unit_code')->nullable();
            $table->text('unit_desc')->nullable();
            $table->timestamp('unit_created_at')->nullable();
            $table->timestamp('unit_updated_at')->nullable();
            
            // === فیلدهای room (دقیقاً مثل API) ===
            $table->integer('room_id')->nullable();
            $table->string('room_name')->nullable();
            $table->string('room_code')->nullable();
            $table->integer('room_unit_id')->nullable();
            $table->integer('room_bed_count')->nullable();
            $table->text('room_desc')->nullable();
            $table->string('room_type')->nullable();
            $table->timestamp('room_created_at')->nullable();
            $table->timestamp('room_updated_at')->nullable();
            
            // === فیلدهای bed (دقیقاً مثل API) ===
            $table->integer('bed_id')->nullable();
            $table->string('bed_name')->nullable();
            $table->string('bed_code')->nullable();
            $table->integer('bed_room_id')->nullable();
            $table->string('bed_state_ratio_resident')->nullable();
            $table->string('bed_state')->nullable();
            $table->text('bed_desc')->nullable();
            $table->timestamp('bed_created_at')->nullable();
            $table->timestamp('bed_updated_at')->nullable();
            
            // === فیلدهای contract (دقیقاً مثل API) ===
            $table->integer('contract_resident_id')->nullable();
            $table->timestamp('contract_payment_date')->nullable();
            $table->string('contract_payment_date_jalali')->nullable();
            $table->integer('contract_bed_id')->nullable();
            $table->string('contract_state')->nullable();
            $table->timestamp('contract_start_date')->nullable();
            $table->string('contract_start_date_jalali')->nullable();
            $table->timestamp('contract_end_date')->nullable();
            $table->string('contract_end_date_jalali')->nullable();
            $table->timestamp('contract_created_at')->nullable();
            $table->timestamp('contract_updated_at')->nullable();
            $table->timestamp('contract_deleted_at')->nullable();
            
            // === فیلدهای resident (دقیقاً مثل API) ===
            $table->string('resident_full_name')->nullable();
            $table->string('resident_phone')->nullable();
            $table->integer('resident_age')->nullable();
            $table->date('resident_birth_date')->nullable();
            $table->string('resident_job')->nullable();
            $table->string('resident_referral_source')->nullable();
            $table->boolean('resident_form')->nullable();
            $table->boolean('resident_document')->nullable();
            $table->boolean('resident_rent')->nullable();
            $table->boolean('resident_trust')->nullable();
            $table->timestamp('resident_created_at')->nullable();
            $table->timestamp('resident_updated_at')->nullable();
            $table->timestamp('resident_deleted_at')->nullable();
            
            // === فیلد notes (JSON) ===
            $table->json('notes')->nullable();
            
            // زمان آخرین همگام‌سازی
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // ایندکس‌ها
            $table->index('resident_id');
            $table->index('contract_id');
            $table->index('unit_id');
            $table->index('room_id');
            $table->index('bed_id');
            $table->index('resident_full_name');
            $table->index('resident_phone');
            $table->index('contract_start_date');
            $table->index('contract_end_date');
            $table->index('contract_payment_date');
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
