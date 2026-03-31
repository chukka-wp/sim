<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('player_id')->constrained('players');
            $table->foreignUuid('team_id')->constrained('teams');
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'team_id', 'left_at']);
        });
    }
};
