<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cloud_match_id')->nullable();
            $table->text('scorer_token')->nullable();
            $table->foreignUuid('rule_set_id')->constrained('rule_sets');
            $table->string('scenario_preset')->nullable();
            $table->text('scenario_prompt');
            $table->string('model_name');
            $table->string('status')->default('pending');
            $table->decimal('speed_multiplier', 3, 1)->default(1.0);
            $table->json('generated_events')->nullable();
            $table->unsignedInteger('current_event_index')->default(0);
            $table->unsignedInteger('total_events')->default(0);
            $table->json('skipped_events')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
};
