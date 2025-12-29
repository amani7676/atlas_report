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
        Schema::table('auto_sms', function (Blueprint $table) {
            $table->foreignId('pattern_id')->nullable()->after('text')->constrained('patterns')->onDelete('set null');
            $table->text('text')->nullable()->change(); // متن را nullable می‌کنیم چون از pattern استفاده می‌شود
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_sms', function (Blueprint $table) {
            $table->dropForeign(['pattern_id']);
            $table->dropColumn('pattern_id');
            $table->text('text')->nullable(false)->change();
        });
    }
};
