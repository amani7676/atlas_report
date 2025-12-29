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
        Schema::create('report_sms_message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('sms_message_id')->constrained()->onDelete('cascade');
            $table->enum('send_type', ['manual', 'automatic', 'group'])->default('manual')->comment('نوع ارسال: دستی، اتوماتیک، گروهی');
            $table->timestamps();
            
            // جلوگیری از تکرار
            $table->unique(['report_id', 'sms_message_id', 'send_type']);
            $table->index(['report_id', 'send_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_sms_message');
    }
};
