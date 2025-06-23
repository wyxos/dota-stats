<?php

use App\Jobs\FetchDotaMatchesJob;
use App\Models\DotaMatch;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Run migrations and clear the database before each test
    $this->artisan('migrate:fresh');
});

test('job fetches and stores matches', function () {
    // Sample match data from OpenDota API
    $matchesData = [
        [
            'match_id' => 123456789,
            'player_slot' => 0,
            'radiant_win' => true,
            'duration' => 2400,
            'game_mode' => 1,
            'lobby_type' => 0,
            'hero_id' => 1,
            'start_time' => 1622548800,
            'version' => '1',
            'kills' => 10,
            'deaths' => 5,
            'assists' => 15,
            'average_rank' => 50,
            'leaver_status' => 0,
            'party_size' => 1,
            'hero_variant' => null,
        ],
        [
            'match_id' => 987654321,
            'player_slot' => 1,
            'radiant_win' => false,
            'duration' => 3600,
            'game_mode' => 2,
            'lobby_type' => 1,
            'hero_id' => 2,
            'start_time' => 1622552400,
            'version' => '1',
            'kills' => 5,
            'deaths' => 10,
            'assists' => 5,
            'average_rank' => 50,
            'leaver_status' => 0,
            'party_size' => 2,
            'hero_variant' => null,
        ],
    ];

    // Mock the HTTP response
    Http::fake([
        'api.opendota.com/api/players/12345678/matches?offset=0&limit=100' => Http::response($matchesData, 200),
    ]);

    // Create and dispatch the job
    $job = new FetchDotaMatchesJob('12345678', 0, 100);
    $job->handle();

    // Assert that the matches were stored in the database
    $this->assertDatabaseCount('dota_matches', 2);
    $this->assertDatabaseHas('dota_matches', [
        'match_id' => 123456789,
        'player_slot' => 0,
        'radiant_win' => 1,
        'duration' => 2400,
        'game_mode' => 1,
        'lobby_type' => 0,
        'hero_id' => 1,
        'kills' => 10,
        'deaths' => 5,
        'assists' => 15,
    ]);
    $this->assertDatabaseHas('dota_matches', [
        'match_id' => 987654321,
        'player_slot' => 1,
        'radiant_win' => 0,
        'duration' => 3600,
        'game_mode' => 2,
        'lobby_type' => 1,
        'hero_id' => 2,
        'kills' => 5,
        'deaths' => 10,
        'assists' => 5,
    ]);
});

test('job handles api error gracefully', function () {
    // Mock the HTTP response to simulate an API error
    Http::fake([
        'api.opendota.com/api/players/12345678/matches?offset=0&limit=100' => Http::response([], 500),
    ]);

    // Create and dispatch the job
    $job = new FetchDotaMatchesJob('12345678', 0, 100);
    $job->handle();

    // Assert that no matches were stored in the database
    $this->assertDatabaseCount('dota_matches', 0);
});

test('job handles empty response gracefully', function () {
    // Mock the HTTP response to simulate an empty response
    Http::fake([
        'api.opendota.com/api/players/12345678/matches?offset=0&limit=100' => Http::response([], 200),
    ]);

    // Create and dispatch the job
    $job = new FetchDotaMatchesJob('12345678', 0, 100);
    $job->handle();

    // Assert that no matches were stored in the database
    $this->assertDatabaseCount('dota_matches', 0);
});
