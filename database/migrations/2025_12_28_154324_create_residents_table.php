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
            
            // تاریخ‌های قرارداد (با نام یکسان در تمام فیلدها)
            $table->date('contract_start_date')->nullable()->comment('تاریخ شروع قرارداد');
            $table->date('contract_end_date')->nullable()->comment('تاریخ پایان قرارداد');
            $table->date('contract_expiry_date')->nullable()->comment('تاریخ انقضای قرارداد');
            
            // ذخیره تمام داده‌های خام API به صورت JSON
            // توجه: resident_data شامل تمام فیلدهای resident و contract با نام یکسان است
            // فیلدهای contract با prefix contract_ ذخیره می‌شوند (مثل contract_start_date، contract_end_date و غیره)
            $table->json('resident_data')->nullable()->comment('تمام داده‌های resident و contract از API با نام یکسان');
            $table->json('unit_data')->nullable()->comment('تمام داده‌های unit از API');
            $table->json('room_data')->nullable()->comment('تمام داده‌های room از API');
            $table->json('bed_data')->nullable()->comment('تمام داده‌های bed از API');
            
            // فیلدهای اضافی
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
