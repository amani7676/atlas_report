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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_name')->unique()->comment('نام کلید (مثلاً: console_api_key, api_key)');
            $table->text('key_value')->comment('مقدار API Key');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->boolean('is_active')->default(true)->comment('فعال/غیرفعال');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
