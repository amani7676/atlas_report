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
        Schema::create('auto_sms_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_sms_id')->constrained('auto_sms')->onDelete('cascade');
            $table->enum('field_type', ['resident', 'resident_report', 'report']); // نوع جدول
            $table->string('field_name'); // نام فیلد (مثلاً: full_name, phone, report_count, total_score)
            $table->enum('operator', ['>', '<', '=', '>=', '<=', 'contains', 'not_contains', '!='])->default('=');
            $table->string('value'); // مقدار مقایسه
            $table->enum('logical_operator', ['AND', 'OR'])->default('AND'); // برای ترکیب با شرط قبلی
            $table->integer('order')->default(0); // ترتیب شرط
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_sms_conditions');
    }
};
