<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // حذف داده‌های قدیمی
        DB::table('resident_cards')->delete();
        
        // تغییر نوع داده resident_id به int برای سازگاری با residents.resident_id
        Schema::table('resident_cards', function (Blueprint $table) {
            $table->integer('resident_id')->unsigned()->change();
        });
        
        // فعلاً foreign key ایجاد نمی‌کنیم تا بعداً به صورت دستی مدیریت شود
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // حذف داده‌های قدیمی
        DB::table('resident_cards')->delete();
        
        // بازگردانی نوع داده به bigint unsigned
        Schema::table('resident_cards', function (Blueprint $table) {
            $table->bigInteger('resident_id')->unsigned()->change();
        });
    }
};
