<?php

namespace App\Console\Commands;

use App\Jobs\ParseDotaMatchJob;
use App\Models\DotaMatch;
use Illuminate\Console\Command;

class ParseDotaMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-dota-matches {--limit=0 : Maximum number of matches to process (0 for all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch detailed data for matches and mark them as parsed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get unparsed matches from the database
        $query = DotaMatch::where('is_parsed', false);

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $matches = $query->get();
        $totalMatches = $matches->count();

        if ($totalMatches === 0) {
            $this->info('No unparsed matches found in the database.');
            return 0;
        }

        $this->info("Found {$totalMatches} unparsed matches. Dispatching jobs to fetch detailed data...");

        // Create a progress bar
        $bar = $this->output->createProgressBar($totalMatches);
        $bar->start();

        // Dispatch jobs with 10 seconds delay between them
        foreach ($matches as $index => $match) {
            ParseDotaMatchJob::dispatch($match->match_id)
                ->delay(now()->addSeconds($index * 10));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All jobs have been dispatched successfully');

        return 0;
    }
}
