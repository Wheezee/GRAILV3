<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('teacher_id'); // e.g., 2025-2026
            $table->enum('semester', ['1','2','S'])->nullable()->after('academic_year');
            $table->index(['academic_year','semester']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['academic_year','semester']);
            $table->dropColumn(['academic_year','semester']);
        });
    }
};


