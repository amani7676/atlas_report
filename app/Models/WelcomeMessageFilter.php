<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WelcomeMessageFilter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'welcome_message_id',
        'table_name',
        'field_name',
        'operator',
        'value',
        'logical_operator',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * ارتباط با پیام خوش‌آمدگویی
     */
    public function welcomeMessage()
    {
        return $this->belongsTo(WelcomeMessage::class);
    }

    /**
     * دریافت لیست عملگرهای مجاز
     */
    public static function getOperators()
    {
        return [
            '=' => 'برابر با',
            '!=' => 'مساوی با نیست',
            '>' => 'بزرگتر از',
            '<' => 'کوچکتر از',
            '>=' => 'بزرگتر یا مساوی',
            '<=' => 'کوچکتر یا مساوی',
            'like' => 'شبیه (شامل)',
            'in' => 'در لیست',
            'not_in' => 'در لیست نیست',
            'is_null' => 'خالی است',
            'is_not_null' => 'خالی نیست',
        ];
    }

    /**
     * دریافت لیست عملگرهای منطقی
     */
    public static function getLogicalOperators()
    {
        return [
            'and' => 'و (AND)',
            'or' => 'یا (OR)',
        ];
    }

    /**
     * دریافت لیست فیلدهای قابل فیلتر
     */
    public static function getAvailableFields()
    {
        return [
            // فیلدهای اصلی اقامت‌گر
            'resident_id' => 'شناسه اقامت‌گر',
            'resident_full_name' => 'نام کامل اقامت‌گر',
            'resident_phone' => 'تلفن اقامت‌گر',
            'resident_age' => 'سن اقامت‌گر',
            'resident_job' => 'شغل اقامت‌گر',
            'resident_referral_source' => 'منبع ارجاع',
            'resident_form' => 'فرم',
            'resident_document' => 'مدارک',
            'resident_rent' => 'اجاره',
            'resident_trust' => 'اعتماد',
            
            // فیلدهای واحد
            'unit_id' => 'شناسه واحد',
            'unit_name' => 'نام واحد',
            'unit_code' => 'کد واحد',
            'unit_desc' => 'توضیحات واحد',
            
            // فیلدهای اتاق
            'room_id' => 'شناسه اتاق',
            'room_name' => 'نام اتاق',
            'room_code' => 'کد اتاق',
            'room_bed_count' => 'تعداد تخت',
            'room_type' => 'نوع اتاق',
            'room_desc' => 'توضیحات اتاق',
            
            // فیلدهای تخت
            'bed_id' => 'شناسه تخت',
            'bed_name' => 'نام تخت',
            'bed_code' => 'کد تخت',
            'bed_state' => 'وضعیت تخت',
            'bed_desc' => 'توضیحات تخت',
            
            // فیلدهای قرارداد
            'contract_id' => 'شناسه قرارداد',
            'contract_state' => 'وضعیت قرارداد',
            'contract_payment_date_jalali' => 'تاریخ پرداخت قرارداد',
            'contract_start_date_jalali' => 'تاریخ شروع قرارداد',
            'contract_end_date_jalali' => 'تاریخ پایان قرارداد',
            
            // فیلدهای زمانی
            'created_at' => 'تاریخ ایجاد',
            'updated_at' => 'تاریخ به‌روزرسانی',
            'last_synced_at' => 'آخرین همگام‌سازی',
        ];
    }
}
