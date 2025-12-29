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
        // اضافه کردن foreign key به sms_message_residents
        Schema::table('sms_message_residents', function (Blueprint $table) {
            // تغییر resident_id از integer به foreignId
            if (Schema::hasColumn('sms_message_residents', 'resident_id')) {
                $table->dropIndex(['resident_id']);
                $table->dropColumn('resident_id');
            }
        });
        
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id')->nullable()->after('sms_message_id');
            $table->foreign('resident_id')
                ->references('id')
                ->on('residents')
                ->onDelete('set null');
            $table->index('resident_id');
        });
        
        // اضافه کردن foreign key به resident_reports
        Schema::table('resident_reports', function (Blueprint $table) {
            // تغییر resident_id از integer به foreignId
            if (Schema::hasColumn('resident_reports', 'resident_id')) {
                $table->dropIndex(['resident_id', 'unit_id', 'room_id']);
                $table->dropColumn('resident_id');
            }
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id')->nullable()->after('report_id');
            $table->foreign('resident_id')
                ->references('id')
                ->on('residents')
                ->onDelete('set null');
            $table->index(['resident_id', 'unit_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف foreign key از sms_message_residents
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
            $table->dropIndex(['resident_id']);
            $table->dropColumn('resident_id');
        });
        
        Schema::table('sms_message_residents', function (Blueprint $table) {
            $table->integer('resident_id')->nullable()->after('sms_message_id');
            $table->index('resident_id');
        });
        
        // حذف foreign key از resident_reports
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->dropIndex(['resident_id', 'unit_id', 'room_id']);
            $table->dropForeign(['resident_id']);
            $table->dropColumn('resident_id');
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->integer('resident_id')->nullable()->after('report_id');
            $table->index(['resident_id', 'unit_id', 'room_id']);
        });
    }
};
