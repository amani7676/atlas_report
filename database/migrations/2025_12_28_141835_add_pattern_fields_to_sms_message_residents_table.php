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
            $table->foreignId('pattern_id')->nullable()->after('sms_message_id')->constrained('patterns')->onDelete('set null');
            $table->boolean('is_pattern')->default(false)->after('pattern_id')->comment('آیا این پیامک از طریق الگو ارسال شده است؟');
            $table->string('pattern_variables')->nullable()->after('is_pattern')->comment('متغیرهای ارسال شده برای الگو (با ; جدا شده)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropForeign(['pattern_id']);
            $table->dropColumn(['pattern_id', 'is_pattern', 'pattern_variables']);
        });
    }
};
