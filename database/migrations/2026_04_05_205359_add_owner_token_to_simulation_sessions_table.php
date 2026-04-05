<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulation_sessions', function (Blueprint $table) {
            $table->text('owner_token')->nullable()->after('cloud_match_id');
        });
    }
};
