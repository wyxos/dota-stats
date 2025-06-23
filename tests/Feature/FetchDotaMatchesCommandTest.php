<?php

use App\Jobs\FetchDotaMatchesJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Create a mock config.json file
    Storage::fake('local');
    Storage::put('config.json', json_encode(['playerId' => '12345678']));
});

test('command reads player id from config and dispatches jobs', function () {
    // Mock the HTTP response for the totals endpoint
    Http::fake([
        'api.opendota.com/api/players/12345678/totals' => Http::response([
            ['field' => 'n', 'sum' => 250],
        ], 200),
    ]);

    // Mock the queue to prevent actual job dispatching
    Queue::fake();

    // Run the command with a limit of 50 matches per batch
    $this->artisan('app:fetch-dota-matches', ['--limit' => 50])
         ->expectsOutput('Fetching matches for player ID: 12345678')
         ->expectsOutput('Total matches found: 250')
         ->expectsOutput('Will fetch matches in 5 batches with 50 matches per batch')
         ->expectsOutput('All jobs have been dispatched successfully')
         ->assertSuccessful();

    // Assert that the correct number of jobs were dispatched
    Queue::assertPushed(FetchDotaMatchesJob::class, 5);

    // Assert that the jobs were dispatched with the correct parameters
    Queue::assertPushed(function (FetchDotaMatchesJob $job) {
        $reflection = new ReflectionClass($job);

        $playerIdProperty = $reflection->getProperty('playerId');
        $playerIdProperty->setAccessible(true);
        $playerId = $playerIdProperty->getValue($job);

        $offsetProperty = $reflection->getProperty('offset');
        $offsetProperty->setAccessible(true);
        $offset = $offsetProperty->getValue($job);

        $limitProperty = $reflection->getProperty('limit');
        $limitProperty->setAccessible(true);
        $limit = $limitProperty->getValue($job);

        return $playerId === '12345678' &&
               $offset >= 0 &&
               $offset < 250 &&
               $limit === 50;
    });
});

test('command handles api error gracefully', function () {
    // Mock the HTTP response to simulate an API error
    Http::fake([
        'api.opendota.com/api/players/12345678/totals' => Http::response([], 500),
    ]);

    // Run the command
    $this->artisan('app:fetch-dota-matches')
         ->expectsOutput('Fetching matches for player ID: 12345678')
         ->expectsOutput('Failed to fetch player totals from OpenDota API')
         ->assertFailed();
});

test('command handles empty response gracefully', function () {
    // Mock the HTTP response to simulate an empty response
    Http::fake([
        'api.opendota.com/api/players/12345678/totals' => Http::response([], 200),
    ]);

    // Run the command
    $this->artisan('app:fetch-dota-matches')
         ->expectsOutput('Fetching matches for player ID: 12345678')
         ->expectsOutput('Could not determine total matches count')
         ->assertFailed();
});
