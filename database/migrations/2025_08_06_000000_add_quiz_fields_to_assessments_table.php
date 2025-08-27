<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->boolean('is_quiz')->default(false);
            $table->string('unique_url')->nullable()->unique();
            $table->boolean('qr_code_enabled')->default(false);
            $table->boolean('auto_grade')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['is_quiz', 'unique_url', 'qr_code_enabled', 'auto_grade']);
        });
    }
};
