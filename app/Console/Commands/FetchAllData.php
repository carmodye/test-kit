<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchSlidesJob;
use App\Jobs\FetchDeviceDataJob;

class FetchAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:all {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch both slides and device data from all clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching FetchSlidesJob...');

        if ($this->option('sync')) {
            FetchSlidesJob::dispatchSync();
            $this->info('FetchSlidesJob completed synchronously.');
        } else {
            FetchSlidesJob::dispatch();
            $this->info('FetchSlidesJob dispatched to queue.');
        }

        $this->info('Dispatching FetchDeviceDataJob...');

        if ($this->option('sync')) {
            FetchDeviceDataJob::dispatchSync();
            $this->info('FetchDeviceDataJob completed synchronously.');
        } else {
            FetchDeviceDataJob::dispatch();
            $this->info('FetchDeviceDataJob dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}