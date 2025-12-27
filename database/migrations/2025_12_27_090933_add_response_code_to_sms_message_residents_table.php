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
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->string('response_code')->nullable()->after('error_message')->comment('کد پاسخ از API ملی پیامک');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropColumn('response_code');
        });
    }
};
