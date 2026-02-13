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
            $table->string('rec_id')->nullable()->after('status')->comment('شناسه پیامک برای دریافت وضعیت دلیوری');
            $table->integer('delivery_status')->nullable()->after('rec_id')->comment('کد وضعیت دلیوری از وب سرویس');
            $table->boolean('delivery_checked')->default(false)->after('delivery_status')->comment('آیا وضعیت دلیوری بررسی شده است');
            $table->timestamp('delivery_checked_at')->nullable()->after('delivery_checked')->comment('زمان آخرین بررسی وضعیت دلیوری');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropColumn(['rec_id', 'delivery_status', 'delivery_checked', 'delivery_checked_at']);
        });
    }
};
