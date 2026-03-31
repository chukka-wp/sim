<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('match_id')->constrained('matches');
            $table->foreignUuid('player_id')->constrained('players');
            $table->foreignUuid('team_id')->constrained('teams');
            $table->unsignedSmallInteger('cap_number');
            $table->boolean('is_starting')->default(false);
            $table->string('role')->default('field_player');
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
            $table->unique(['match_id', 'team_id', 'cap_number']);
        });
    }
};
