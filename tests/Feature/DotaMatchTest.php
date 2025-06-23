<?php

use App\Models\DotaMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create and retrieve a dota match', function () {
    // Arrange
    $matchData = [
        'match_id' => 8346876031,
        'player_slot' => 131,
        'radiant_win' => true,
        'duration' => 2752,
        'game_mode' => 22,
        'lobby_type' => 7,
        'hero_id' => 52,
        'start_time' => 1750694661,
        'version' => null,
        'kills' => 17,
        'deaths' => 5,
        'assists' => 10,
        'average_rank' => 64,
        'leaver_status' => 0,
        'party_size' => null,
        'hero_variant' => 2
    ];

    // Act
    DotaMatch::create($matchData);
    $retrievedMatch = DotaMatch::where('match_id', 8346876031)->first();

    // Assert
    expect($retrievedMatch)->not->toBeNull();
    expect($retrievedMatch->match_id)->toBe(8346876031);
    expect($retrievedMatch->player_slot)->toBe(131);
    expect($retrievedMatch->radiant_win)->toBeTrue();
    expect($retrievedMatch->duration)->toBe(2752);
    expect($retrievedMatch->game_mode)->toBe(22);
    expect($retrievedMatch->lobby_type)->toBe(7);
    expect($retrievedMatch->hero_id)->toBe(52);
    expect($retrievedMatch->start_time)->toBe(1750694661);
    expect($retrievedMatch->version)->toBeNull();
    expect($retrievedMatch->kills)->toBe(17);
    expect($retrievedMatch->deaths)->toBe(5);
    expect($retrievedMatch->assists)->toBe(10);
    expect($retrievedMatch->average_rank)->toBe(64);
    expect($retrievedMatch->leaver_status)->toBe(0);
    expect($retrievedMatch->party_size)->toBeNull();
    expect($retrievedMatch->hero_variant)->toBe(2);
});
