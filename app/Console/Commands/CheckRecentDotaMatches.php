<?php

namespace App\Console\Commands;

use App\Models\DotaMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CheckRecentDotaMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-recent-dota-matches {--limit=20 : Number of recent matches to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the most recent Dota 2 matches for the player and insert new ones into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get player ID from config.json
            $configPath = 'private/config.json';
            if (!Storage::exists($configPath)) {
                $configPath = 'config.json';
            }

            $config = json_decode(Storage::get($configPath), true);
            $playerId = $config['playerId'];

            $this->info("Checking recent matches for player ID: $playerId");

            // Get the limit from the command option
            $limit = $this->option('limit');

            // Fetch matches from OpenDota API (first page only)
            $response = Http::get("https://api.opendota.com/api/players/{$playerId}/matches", [
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                $this->error('Failed to fetch matches from OpenDota API');
                Log::error("Failed to fetch matches from OpenDota API", [
                    'player_id' => $playerId,
                    'status' => $response->status(),
                ]);
                return 1;
            }

            $matches = $response->json();
            $newMatchesCount = 0;

            // Store matches in the database
            foreach ($matches as $matchData) {
                $match = DotaMatch::updateOrCreate(
                    ['match_id' => $matchData['match_id']],
                    [
                        'player_slot' => $matchData['player_slot'] ?? null,
                        'radiant_win' => $matchData['radiant_win'] ?? false,
                        'duration' => $matchData['duration'] ?? null,
                        'game_mode' => $matchData['game_mode'] ?? null,
                        'lobby_type' => $matchData['lobby_type'] ?? null,
                        'hero_id' => $matchData['hero_id'] ?? null,
                        'start_time' => $matchData['start_time'] ?? null,
                        'version' => $matchData['version'] ?? null,
                        'kills' => $matchData['kills'] ?? null,
                        'deaths' => $matchData['deaths'] ?? null,
                        'assists' => $matchData['assists'] ?? null,
                        'average_rank' => $matchData['average_rank'] ?? null,
                        'leaver_status' => $matchData['leaver_status'] ?? null,
                        'party_size' => $matchData['party_size'] ?? null,
                        'hero_variant' => $matchData['hero_variant'] ?? null,
                    ]
                );

                // Check if this was a new match
                if ($match->wasRecentlyCreated) {
                    $newMatchesCount++;
                }
            }

            $this->info("Processed {$limit} recent matches. Found {$newMatchesCount} new matches.");
            Log::info("Successfully processed recent matches", [
                'player_id' => $playerId,
                'matches_checked' => count($matches),
                'new_matches' => $newMatchesCount,
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error("Error checking recent matches: {$e->getMessage()}");
            Log::error("Error checking recent matches", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
