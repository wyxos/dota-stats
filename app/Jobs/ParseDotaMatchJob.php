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

class ParseDotaMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The match ID to fetch details for.
     *
     * @var int
     */
    protected $matchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $matchId)
    {
        $this->matchId = $matchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the match in the database
            $match = DotaMatch::where('match_id', $this->matchId)->first();

            if (!$match) {
                Log::error("Match not found in database", [
                    'match_id' => $this->matchId,
                ]);
                return;
            }

            // Fetch match details from OpenDota API
            $response = Http::get("https://api.opendota.com/api/matches/{$this->matchId}");

            if (!$response->successful()) {
                Log::error("Failed to fetch match details from OpenDota API", [
                    'match_id' => $this->matchId,
                    'status' => $response->status(),
                ]);
                return;
            }

            $matchDetails = $response->json();

            // Update the match with detailed data
            $match->update([
                'details' => $matchDetails,
                'is_parsed' => true,
            ]);

            Log::info("Successfully parsed match details", [
                'match_id' => $this->matchId,
            ]);
        } catch (\Exception $e) {
            Log::error("Error parsing match details", [
                'match_id' => $this->matchId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
