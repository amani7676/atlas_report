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
        Schema::table('resident_grants', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('grant_date')->comment('وضعیت فعال بودن بخشودگی');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_grants', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
