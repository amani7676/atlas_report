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
        Schema::table('pattern_variables', function (Blueprint $table) {
            $table->renameColumn('table_field', 'pattern_code');
            $table->string('pattern_code')->nullable()->comment('کد الگوی متغیر')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pattern_variables', function (Blueprint $table) {
            $table->renameColumn('pattern_code', 'table_field');
            $table->string('table_field')->nullable()->comment('فیلد جدول')->change();
        });
    }
};
