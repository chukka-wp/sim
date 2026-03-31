<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('match_id')->constrained('matches');
            $table->unsignedInteger('sequence');
            $table->string('type');
            $table->unsignedSmallInteger('period');
            $table->unsignedSmallInteger('period_clock_seconds');
            $table->timestamp('recorded_at');
            $table->string('recorded_by')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'sequence']);
            $table->index(['match_id', 'type']);
        });
    }
};
