<?php

use App\Models\Part;
use App\Models\Subject;
use App\Models\Test;
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
        Schema::create('test_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Test::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Part::class)->nullable()->constrained()->nullOnDelete();
            $table->json('questions'); // [{detail_id, user_answers: [], is_right: null}]
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('max_score')->default(0);
            $table->timestamps();

            $table->index('test_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_subjects');
    }
};
