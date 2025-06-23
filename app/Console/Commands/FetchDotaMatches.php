<?php

namespace App\Console\Commands;

use App\Jobs\FetchDotaMatchesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchDotaMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-dota-matches {--limit=100 : Number of matches to fetch per request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all Dota 2 matches for the player ID in config.json';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get player ID from config.json
        $config = json_decode(Storage::get('config.json'), true);
        $playerId = $config['playerId'];

        $this->info("Fetching matches for player ID: $playerId");

        // Get total matches count from OpenDota API
        $response = Http::get("https://api.opendota.com/api/players/{$playerId}/totals");

        if (!$response->successful()) {
            $this->error('Failed to fetch player totals from OpenDota API');
            return 1;
        }

        // Find the total matches from the response
        $totalMatches = 0;

        $responseData = $response->json();
        if (!empty($responseData) && isset($responseData[0]['n'])) {
            $totalMatches = $responseData[0]['n'];
        }

        if ($totalMatches === 0) {
            $this->error('Could not determine total matches count');
            return 1;
        }

        $this->info("Total matches found: $totalMatches");

        // Calculate cycles needed with offset
        $limit = $this->option('limit');
        $cycles = ceil($totalMatches / $limit);

        $this->info("Will fetch matches in $cycles batches with $limit matches per batch");

        // Dispatch jobs with 10 seconds delay between them
        $bar = $this->output->createProgressBar($cycles);
        $bar->start();

        for ($i = 0; $i < $cycles; $i++) {
            $offset = $i * $limit;

            // Dispatch job with delay
            FetchDotaMatchesJob::dispatch($playerId, $offset, $limit)
                ->delay(now()->addSeconds($i * 30));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All jobs have been dispatched successfully');

        return 0;
    }
}
