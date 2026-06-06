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
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempts_limit')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempts_limit')->default(0)->change();
        });
    }
};
