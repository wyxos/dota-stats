<?php

use App\Jobs\ParseDotaMatchJob;
use App\Models\DotaMatch;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Run migrations to ensure tables exist
    $this->artisan('migrate:fresh');

    // Clear the database
    DotaMatch::query()->delete();
});

test('command dispatches jobs for unparsed matches', function () {
    // Create some unparsed matches in the database
    DotaMatch::factory()->count(5)->create([
        'is_parsed' => false
    ]);

    // Mock the queue to prevent actual job dispatching
    Queue::fake();

    // Run the command
    $this->artisan('app:parse-dota-matches')
         ->expectsOutput('Found 5 unparsed matches. Dispatching jobs to fetch detailed data...')
         ->expectsOutput('All jobs have been dispatched successfully')
         ->assertSuccessful();

    // Assert that the correct number of jobs were dispatched
    Queue::assertPushed(ParseDotaMatchJob::class, 5);

    // Assert that the jobs were dispatched with the correct parameters
    Queue::assertPushed(function (ParseDotaMatchJob $job) {
        $reflection = new ReflectionClass($job);

        $matchIdProperty = $reflection->getProperty('matchId');
        $matchIdProperty->setAccessible(true);
        $matchId = $matchIdProperty->getValue($job);

        // Check if the match ID corresponds to one of our created matches
        return DotaMatch::where('match_id', $matchId)->exists();
    });
});

test('command respects the limit parameter', function () {
    // Create some unparsed matches in the database
    DotaMatch::factory()->count(10)->create([
        'is_parsed' => false
    ]);

    // Mock the queue to prevent actual job dispatching
    Queue::fake();

    // Run the command with a limit of 3 matches
    $this->artisan('app:parse-dota-matches', ['--limit' => 3])
         ->expectsOutput('Found 3 unparsed matches. Dispatching jobs to fetch detailed data...')
         ->expectsOutput('All jobs have been dispatched successfully')
         ->assertSuccessful();

    // Assert that only 3 jobs were dispatched
    Queue::assertPushed(ParseDotaMatchJob::class, 3);
});

test('command handles no unparsed matches gracefully', function () {
    // Create some already parsed matches
    DotaMatch::factory()->count(3)->create([
        'is_parsed' => true
    ]);

    // Mock the queue to prevent actual job dispatching
    Queue::fake();

    // Run the command
    $this->artisan('app:parse-dota-matches')
         ->expectsOutput('No unparsed matches found in the database.')
         ->assertSuccessful();

    // Assert that no jobs were dispatched
    Queue::assertNothingPushed();
});
