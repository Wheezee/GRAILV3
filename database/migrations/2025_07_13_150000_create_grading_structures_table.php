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
        Schema::create('grading_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['balanced', 'custom']); // balanced = 50/50, custom = user-defined
            $table->decimal('midterm_weight', 5, 2)->default(50.00); // percentage
            $table->decimal('final_weight', 5, 2)->default(50.00); // percentage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_structures');
    }
}; 