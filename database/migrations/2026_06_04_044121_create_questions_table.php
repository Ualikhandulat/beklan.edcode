<?php

use App\Models\Subject;
use App\Models\Topic;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Subject::class)->constrained();
            $table->foreignIdFor(Topic::class)->constrained();
            $table->integer('count_variants');
            $table->integer('count_answers');
            $table->longText('text');
            $table->timestamps();
            $table->softDeletes();

            $table->index('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
