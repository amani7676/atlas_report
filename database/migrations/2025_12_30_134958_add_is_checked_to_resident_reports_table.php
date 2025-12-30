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
            $table->boolean('is_checked')->default(false)->after('has_been_sent')->comment('وضعیت چک شده بودن گزارش');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->dropColumn('is_checked');
        });
    }
};
