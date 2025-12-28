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
        Schema::table('blacklists', function (Blueprint $table) {
            if (!Schema::hasColumn('blacklists', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('blacklists', 'blacklist_id')) {
                $table->string('blacklist_id')->nullable()->comment('کد 5 رقمی لیست سیاه از ملی پیامک')->after('title');
            }
            if (!Schema::hasColumn('blacklists', 'description')) {
                $table->text('description')->nullable()->after('blacklist_id');
            }
            if (!Schema::hasColumn('blacklists', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('blacklists', 'api_response')) {
                $table->text('api_response')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('blacklists', 'http_status_code')) {
                $table->integer('http_status_code')->nullable()->after('api_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blacklists', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'blacklist_id',
                'description',
                'is_active',
                'api_response',
                'http_status_code',
            ]);
        });
    }
};
