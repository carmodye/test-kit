<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchSlidesJob;

class FetchSlides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:slides {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch slides data from all clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching FetchSlidesJob...');

        if ($this->option('sync')) {
            // Run synchronously
            FetchSlidesJob::dispatchSync();
            $this->info('FetchSlidesJob completed synchronously.');
        } else {
            // Run asynchronously (queued)
            FetchSlidesJob::dispatch();
            $this->info('FetchSlidesJob dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}