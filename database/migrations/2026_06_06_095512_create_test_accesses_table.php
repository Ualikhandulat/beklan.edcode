<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_accesses', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['ent', 'subject']);

            // Target: one of user or group must be set
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();

            // Scope: subject + part
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->enum('part_type', ['topic', 'nusqa'])->nullable();
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();

            // Student-choice flags
            $table->boolean('student_chooses_subject')->default(false);
            $table->boolean('student_chooses_part')->default(false);

            // Question limit (0 = all)
            $table->unsignedSmallInteger('question_count')->default(0);

            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for the most common lookups
            $table->index(['user_id', 'type']);
            $table->index(['group_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_accesses');
    }
};
