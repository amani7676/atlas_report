<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneToResidentReportsTable extends Migration
{
    public function up()
    {
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('resident_name');
        });
    }

    public function down()
    {
        Schema::table('resident_reports', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
}
