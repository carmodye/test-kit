<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\Client;

use App\Models\JobRunTime;

class FetchDeviceDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('FetchDeviceDataJob started at ' . now()->toDateTimeString());
        // Log the job run time
        JobRunTime::updateOrCreate(
            ['job_name' => 'FetchDeviceDataJob'],
            ['last_run_at' => now()]
        );
        $clients = Client::all();

        foreach ($clients as $client) {
            $url = "https://{$client->name}.cms.ab-net.us/api/dumpdata";

            try {
                $response = Http::timeout(60)->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $devices = $data['devices'] ?? [];

                    Device::where('client', $client->name)->delete(); // Clear old data

                    foreach ($devices as $device) {
                        Device::create([
                            'client' => $client->name,
                            'device_id' => $device['device_id'],
                            'display_id' => $device['display_id'],
                            'display_name' => $device['display_name'] ?? null,
                            'device_name' => $device['device_name'] ?? null,
                            'site_name' => $device['site_name'],
                            'app_name' => $device['app_name'],
                            'site_id' => $device['site_id'],
                            'other_data' => json_encode($device), // All other fields for admin
                        ]);
                    }

                    Log::info('Fetched ' . count($devices) . ' devices for client ' . $client->name);
                } else {
                    Log::error('API error for client ' . $client->name . ': ' . $response->status());
                }
            } catch (\Exception $e) {
                Log::error('Fetch failed for client ' . $client->name . ': ' . $e->getMessage());
            }
        }
    }
}