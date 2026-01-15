<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchDeviceDataJob;

class FetchDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:devices {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch device data from all clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching FetchDeviceDataJob...');

        if ($this->option('sync')) {
            // Run synchronously
            FetchDeviceDataJob::dispatchSync();
            $this->info('FetchDeviceDataJob completed synchronously.');
        } else {
            // Run asynchronously (queued)
            FetchDeviceDataJob::dispatch();
            $this->info('FetchDeviceDataJob dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}