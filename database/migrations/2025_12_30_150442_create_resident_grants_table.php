<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resident_grants', function (Blueprint $table) {
            $table->id();
            $table->integer('resident_id')->nullable()->comment('ID اقامتگر از API');
            $table->decimal('amount', 15, 2)->comment('مقدار بخشودگی');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->date('grant_date')->nullable()->comment('تاریخ بخشودگی');
            $table->timestamps();

            $table->index('resident_id');
            $table->index('grant_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('resident_grants');
    }
};
