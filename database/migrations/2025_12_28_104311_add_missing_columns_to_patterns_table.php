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
        Schema::table('patterns', function (Blueprint $table) {
            if (!Schema::hasColumn('patterns', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('patterns', 'text')) {
                $table->text('text')->after('title');
            }
            if (!Schema::hasColumn('patterns', 'pattern_code')) {
                $table->string('pattern_code')->nullable()->comment('کد الگو از ملی پیامک')->after('text');
            }
            if (!Schema::hasColumn('patterns', 'blacklist_id')) {
                $table->string('blacklist_id')->comment('کد 5 رقمی لیست سیاه مرتبط')->after('pattern_code');
            }
            if (!Schema::hasColumn('patterns', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('blacklist_id');
            }
            if (!Schema::hasColumn('patterns', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('patterns', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('rejection_reason');
            }
            if (!Schema::hasColumn('patterns', 'api_response')) {
                $table->text('api_response')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('patterns', 'http_status_code')) {
                $table->integer('http_status_code')->nullable()->after('api_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'text',
                'pattern_code',
                'blacklist_id',
                'status',
                'rejection_reason',
                'is_active',
                'api_response',
                'http_status_code',
            ]);
        });
    }
};
