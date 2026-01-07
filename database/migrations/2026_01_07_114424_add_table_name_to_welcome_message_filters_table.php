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
        Schema::table('welcome_message_filters', function (Blueprint $table) {
            $table->string('table_name')->after('welcome_message_id'); // اضافه کردن فیلد جدول
            $table->index('table_name'); // ایندکس برای جستجوی بهتر
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welcome_message_filters', function (Blueprint $table) {
            $table->dropIndex(['table_name']);
            $table->dropColumn('table_name');
        });
    }
};
