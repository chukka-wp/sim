<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rule_set_id')->constrained('rule_sets');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('venue')->nullable();
            $table->foreignUuid('home_team_id')->constrained('teams');
            $table->foreignUuid('away_team_id')->constrained('teams');
            $table->string('home_cap_colour')->nullable();
            $table->string('away_cap_colour')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('live_url')->nullable();
            $table->timestamps();
        });
    }
};
