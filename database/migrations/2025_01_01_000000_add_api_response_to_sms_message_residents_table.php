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
            $table->json('api_response')->nullable()->after('response_code')->comment('پاسخ کامل API ملی پیامک');
            $table->text('raw_response')->nullable()->after('api_response')->comment('پاسخ خام API');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropColumn(['api_response', 'raw_response']);
        });
    }
};

