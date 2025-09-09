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
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('class_section_id')->nullable()->after('assessment_type_id');
            $table->foreign('class_section_id')
                ->references('id')
                ->on('class_sections')
                ->onDelete('cascade');
            $table->index(['class_section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['class_section_id']);
            $table->dropIndex(['class_section_id']);
            $table->dropColumn('class_section_id');
        });
    }
};
