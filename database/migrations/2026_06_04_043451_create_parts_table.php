<?php

use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // PartType enum value: 'topic' | 'nusqa'
            $table->timestamps();
            $table->softDeletes();

            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
