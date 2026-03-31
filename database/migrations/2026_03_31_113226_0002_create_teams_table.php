<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('gender');
            $table->string('age_group')->nullable();
            $table->timestamps();
        });
    }
};
