<?php

use App\Models\DotaMatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Create a mock config.json file
    Storage::fake('local');

    // Put the config file in both possible locations to ensure it's found
    Storage::put('private/config.json', json_encode(['playerId' => '12345678']));
    Storage::put('config.json', json_encode(['playerId' => '12345678']));

    // Run migrations to create the necessary tables
    $this->artisan('migrate:fresh');
});

test('command fetches recent matches and stores them in the database', function () {
    // Mock the HTTP response for the matches endpoint
    Http::fake([
        'api.opendota.com/api/players/12345678/matches*' => Http::response([
            [
                'match_id' => '6789012345',
                'player_slot' => 128,
                'radiant_win' => true,
                'duration' => 2400,
                'game_mode' => 22,
                'lobby_type' => 7,
                'hero_id' => 106,
                'start_time' => 1623456789,
                'kills' => 10,
                'deaths' => 5,
                'assists' => 15,
            ],
            [
                'match_id' => '6789012346',
                'player_slot' => 1,
                'radiant_win' => false,
                'duration' => 1800,
                'game_mode' => 22,
                'lobby_type' => 7,
                'hero_id' => 41,
                'start_time' => 1623456999,
                'kills' => 8,
                'deaths' => 3,
                'assists' => 12,
            ],
        ], 200),
    ]);

    // Run the command
    $this->artisan('app:check-recent-dota-matches')
         ->expectsOutput('Checking recent matches for player ID: 12345678')
         ->expectsOutput('Processed 20 recent matches. Found 2 new matches.')
         ->assertSuccessful();

    // Assert that the matches were stored in the database
    $this->assertDatabaseHas('dota_matches', [
        'match_id' => '6789012345',
        'hero_id' => 106,
        'kills' => 10,
        'deaths' => 5,
        'assists' => 15,
    ]);

    $this->assertDatabaseHas('dota_matches', [
        'match_id' => '6789012346',
        'hero_id' => 41,
        'kills' => 8,
        'deaths' => 3,
        'assists' => 12,
    ]);
});

test('command handles api error gracefully', function () {
    // Mock the HTTP response to simulate an API error
    Http::fake([
        'api.opendota.com/api/players/12345678/matches*' => Http::response([], 500),
    ]);

    // Run the command
    $this->artisan('app:check-recent-dota-matches')
         ->expectsOutput('Checking recent matches for player ID: 12345678')
         ->expectsOutput('Failed to fetch matches from OpenDota API')
         ->assertFailed();
});

test('command only inserts new matches', function () {
    // Create an existing match in the database
    DotaMatch::create([
        'match_id' => '6789012345',
        'player_slot' => 128,
        'radiant_win' => true,
        'duration' => 2400,
        'game_mode' => 22,
        'lobby_type' => 7,
        'hero_id' => 106,
        'start_time' => 1623456789,
        'kills' => 10,
        'deaths' => 5,
        'assists' => 15,
    ]);

    // Mock the HTTP response with one existing match and one new match
    Http::fake([
        'api.opendota.com/api/players/12345678/matches*' => Http::response([
            [
                'match_id' => '6789012345', // Existing match
                'player_slot' => 128,
                'radiant_win' => true,
                'duration' => 2400,
                'game_mode' => 22,
                'lobby_type' => 7,
                'hero_id' => 106,
                'start_time' => 1623456789,
                'kills' => 10,
                'deaths' => 5,
                'assists' => 15,
            ],
            [
                'match_id' => '6789012346', // New match
                'player_slot' => 1,
                'radiant_win' => false,
                'duration' => 1800,
                'game_mode' => 22,
                'lobby_type' => 7,
                'hero_id' => 41,
                'start_time' => 1623456999,
                'kills' => 8,
                'deaths' => 3,
                'assists' => 12,
            ],
        ], 200),
    ]);

    // Run the command
    $this->artisan('app:check-recent-dota-matches', ['--limit' => 2])
         ->expectsOutput('Checking recent matches for player ID: 12345678')
         ->expectsOutput('Processed 2 recent matches. Found 1 new matches.')
         ->assertSuccessful();

    // Assert that we have exactly 2 matches in the database
    $this->assertEquals(2, DotaMatch::count());
});
