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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('blacklist_id')->nullable()->comment('کد 5 رقمی لیست سیاه از ملی پیامک');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('api_response')->nullable(); // برای ذخیره پاسخ کامل API
            $table->integer('http_status_code')->nullable(); // برای ذخیره کد وضعیت HTTP
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
