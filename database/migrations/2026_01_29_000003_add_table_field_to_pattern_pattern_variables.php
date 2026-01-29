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
        Schema::table('pattern_pattern_variables', function (Blueprint $table) {
            $table->string('table_field')->nullable()->comment('فیلد جدول مربوط به کد متغیر');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pattern_pattern_variables', function (Blueprint $table) {
            $table->dropColumn('table_field');
        });
    }
};
