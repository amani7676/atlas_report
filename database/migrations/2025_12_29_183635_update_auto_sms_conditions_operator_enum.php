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
        // تغییر enum operator برای اضافه کردن days_after و days_before
        DB::statement("ALTER TABLE `auto_sms_conditions` MODIFY COLUMN `operator` ENUM('>', '<', '=', '>=', '<=', 'contains', 'not_contains', '!=', 'days_after', 'days_before') DEFAULT '='");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // برگشت به حالت قبلی
        DB::statement("ALTER TABLE `auto_sms_conditions` MODIFY COLUMN `operator` ENUM('>', '<', '=', '>=', '<=', 'contains', 'not_contains', '!=') DEFAULT '='");
    }
};
