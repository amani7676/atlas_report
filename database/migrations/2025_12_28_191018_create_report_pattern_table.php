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
        Schema::create('report_pattern', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('pattern_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0)->comment('ترتیب نمایش');
            $table->boolean('is_active')->default(true)->comment('فعال/غیرفعال');
            $table->timestamps();
            
            // جلوگیری از تکرار
            $table->unique(['report_id', 'pattern_id']);
            $table->index(['report_id', 'is_active']);
            $table->index(['pattern_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_pattern');
    }
};
