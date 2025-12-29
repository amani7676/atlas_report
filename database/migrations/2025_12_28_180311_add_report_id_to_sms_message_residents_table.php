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
            $table->foreignId('report_id')->nullable()->after('sms_message_id')->constrained()->onDelete('set null');
            $table->index('report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
            $table->dropIndex(['report_id']);
            $table->dropColumn('report_id');
        });
    }
};
