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
            $table->integer('player_slot')->nullable();
            $table->boolean('radiant_win')->default(false);
            $table->integer('duration')->nullable();
            $table->integer('game_mode')->nullable();
            $table->integer('lobby_type')->nullable();
            $table->integer('hero_id')->nullable();
            $table->unsignedBigInteger('start_time')->nullable();
            $table->string('version')->nullable();
            $table->integer('kills')->nullable();
            $table->integer('deaths')->nullable();
            $table->integer('assists')->nullable();
            $table->integer('average_rank')->nullable();
            $table->integer('leaver_status')->nullable();
            $table->integer('party_size')->nullable();
            $table->integer('hero_variant')->nullable();
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
