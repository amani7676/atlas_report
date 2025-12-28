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
        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->string('pattern_code')->nullable()->comment('کد الگو از ملی پیامک');
            $table->string('blacklist_id')->comment('کد 5 رقمی لیست سیاه مرتبط');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('api_response')->nullable(); // برای ذخیره پاسخ کامل API
            $table->integer('http_status_code')->nullable(); // برای ذخیره کد وضعیت HTTP
            $table->timestamps();

            $table->index(['blacklist_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
