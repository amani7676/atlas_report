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
        Schema::create('pattern_pattern_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pattern_id')->constrained('patterns')->onDelete('cascade');
            $table->foreignId('pattern_variable_id')->constrained('pattern_variables')->onDelete('cascade');
            $table->string('variable_code')->comment('کد متغیر برای این الگو (مثل {0}, {1})');
            $table->integer('sort_order')->default(0)->comment('ترتیب متغیر در الگو');
            $table->timestamps();
            
            // هر الگو نمی‌تواند دو متغیر با کد تکراری داشته باشد
            $table->unique(['pattern_id', 'variable_code']);
            $table->index(['pattern_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pattern_pattern_variables');
    }
};
