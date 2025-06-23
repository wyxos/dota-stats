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
        Schema::create('dota_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id');
            $table->integer('player_slot');
            $table->boolean('radiant_win');
            $table->integer('duration');
            $table->integer('game_mode');
            $table->integer('lobby_type');
            $table->integer('hero_id');
            $table->unsignedBigInteger('start_time');
            $table->string('version')->nullable();
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('average_rank');
            $table->integer('leaver_status');
            $table->integer('party_size')->nullable();
            $table->integer('hero_variant');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dota_matches');
    }
};
