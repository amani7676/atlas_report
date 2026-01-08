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
        Schema::table('resident_reports', function (Blueprint $table) {
            // حذف foreign key و index فعلی
            $table->dropForeign(['resident_id']);
            $table->dropIndex(['resident_id', 'unit_id', 'room_id']);
            $table->dropColumn('resident_id');
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            // اضافه کردن resident_id به عنوان integer بدون foreign key
            $table->integer('resident_id')->nullable()->after('report_id')->comment('ID اقامتگر از API (resident_id)');
            $table->index(['resident_id', 'unit_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_reports', function (Blueprint $table) {
            // حذف resident_id فعلی
            $table->dropIndex(['resident_id', 'unit_id', 'room_id']);
            $table->dropColumn('resident_id');
        });
        
        Schema::table('resident_reports', function (Blueprint $table) {
            // بازگردانی foreign key
            $table->unsignedBigInteger('resident_id')->nullable()->after('report_id');
            $table->foreign('resident_id')
                ->references('id')
                ->on('residents')
                ->onDelete('set null');
            $table->index(['resident_id', 'unit_id', 'room_id']);
        });
    }
};
