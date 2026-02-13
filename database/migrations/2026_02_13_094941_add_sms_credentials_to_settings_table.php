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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('sms_username')->nullable()->after('sms_delay_between_messages')->comment('نام کاربری سامانه پیامک');
            $table->string('sms_password')->nullable()->after('sms_username')->comment('رمز عبور سامانه پیامک');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['sms_username', 'sms_password']);
        });
    }
};
