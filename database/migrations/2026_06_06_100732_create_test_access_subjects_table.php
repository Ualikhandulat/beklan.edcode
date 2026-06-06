<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_access_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_access_id')->constrained('test_accesses')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('part_type', ['topic', 'nusqa'])->nullable();
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();
            $table->boolean('student_chooses_part')->default(false);
            $table->timestamps();

            $table->index('test_access_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_access_subjects');
    }
};
