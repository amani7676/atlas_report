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
        Schema::create('sms_message_residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_message_id')->constrained()->onDelete('cascade');
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API');
            $table->string('resident_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['sms_message_id', 'status']);
            $table->index('resident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_message_residents');
    }
};
