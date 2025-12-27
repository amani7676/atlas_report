<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API');
            $table->string('resident_name')->nullable();
            $table->integer('unit_id')->nullable();
            $table->string('unit_name')->nullable();
            $table->integer('room_id')->nullable();
            $table->string('room_name')->nullable();
            $table->integer('bed_id')->nullable();
            $table->string('bed_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['resident_id', 'unit_id', 'room_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resident_reports');
    }
};
