<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_bundled')->default(false);

            $table->unsignedSmallInteger('periods')->default(4);
            $table->unsignedSmallInteger('period_duration_seconds')->default(480);
            $table->boolean('running_time')->default(false);
            $table->unsignedSmallInteger('interval_duration_seconds')->default(120);
            $table->unsignedSmallInteger('halftime_duration_seconds')->default(300);

            $table->boolean('possession_clock_enabled')->default(true);
            $table->unsignedSmallInteger('possession_time_seconds')->default(28);
            $table->unsignedSmallInteger('second_possession_time_seconds')->default(18);

            $table->unsignedSmallInteger('exclusion_duration_seconds')->default(20);
            $table->unsignedSmallInteger('violent_action_exclusion_duration_seconds')->default(240);

            $table->unsignedSmallInteger('personal_foul_limit')->default(3);
            $table->boolean('foul_limit_enforced')->default(true);

            $table->unsignedSmallInteger('timeouts_per_team')->default(2);
            $table->unsignedSmallInteger('timeout_duration_seconds')->default(60);
            $table->unsignedSmallInteger('overtime_period_duration_seconds')->default(180);

            $table->unsignedSmallInteger('players_per_team')->default(14);
            $table->unsignedSmallInteger('max_players_in_water')->default(7);
            $table->unsignedSmallInteger('max_goalkeepers')->default(2);

            $table->string('cap_number_scheme')->default('sequential');

            $table->timestamps();
        });
    }
};
