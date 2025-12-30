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
        Schema::create('table_names', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام برای نمایش
            $table->string('table_name'); // نام جدول در دیتابیس
            $table->boolean('is_visible')->default(true); // قابلیت نمایش
            $table->timestamps();
            
            $table->unique('table_name'); // نام جدول باید یکتا باشد
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_names');
    }
};
