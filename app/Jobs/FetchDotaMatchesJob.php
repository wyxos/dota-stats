<?php

namespace App\Jobs;

use App\Models\DotaMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchDotaMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The player ID to fetch matches for.
     *
     * @var string
     */
    protected $playerId;

    /**
     * The offset for pagination.
     *
     * @var int
     */
    protected $offset;

    /**
     * The limit for pagination.
     *
     * @var int
     */
    protected $limit;

    /**
     * Create a new job instance.
     */
    public function __construct(string $playerId, int $offset, int $limit)
    {
        $this->playerId = $playerId;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fetch matches from OpenDota API
            $response = Http::get("https://api.opendota.com/api/players/{$this->playerId}/matches", [
                'offset' => $this->offset,
                'limit' => $this->limit,
            ]);

            if (!$response->successful()) {
                Log::error("Failed to fetch matches from OpenDota API", [
                    'player_id' => $this->playerId,
                    'offset' => $this->offset,
                    'limit' => $this->limit,
                    'status' => $response->status(),
                ]);
                return;
            }

            $matches = $response->json();

            // Store matches in the database
            foreach ($matches as $matchData) {
                DotaMatch::updateOrCreate(
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
            }

            Log::info("Successfully processed matches batch", [
                'player_id' => $this->playerId,
                'offset' => $this->offset,
                'limit' => $this->limit,
                'matches_count' => count($matches),
            ]);
        } catch (\Exception $e) {
            Log::error("Error processing matches batch", [
                'player_id' => $this->playerId,
                'offset' => $this->offset,
                'limit' => $this->limit,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
