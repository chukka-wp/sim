<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs');
            $table->string('name');
            $table->string('preferred_name')->nullable();
            $table->unsignedSmallInteger('preferred_cap_number')->nullable();
            $table->boolean('is_goalkeeper')->default(false);
            $table->timestamps();
        });
    }
};
