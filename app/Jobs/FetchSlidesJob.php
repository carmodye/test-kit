<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Slide;

use App\Models\JobRunTime;

class FetchSlidesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('FetchSlidesJob started at ' . now()->toDateTimeString());
        // Log the job run time
        JobRunTime::updateOrCreate(
            ['job_name' => 'FetchSlidesJob'],
            ['last_run_at' => now()]
        );
        $clients = Client::all();

        foreach ($clients as $client) {
            $url = "https://{$client->name}.cms.ab-net.us/api/getslides";
            Log::info("Processing client: {$client->name}", ['url' => $url]);

            try {
                $response = Http::timeout(60)->get($url);

                if ($response->successful()) {
                    $slides = $response->json();

                    // Clear old slides for this client
                    Slide::where('client', $client->name)->delete();

                    foreach ($slides as $item) {
                        Slide::create([
                            'client' => $client->name,
                            'slide_id' => $item['id'],
                            'name' => $item['name'],
                            'path' => $item['path'],
                            'type' => $item['type'],
                            'duration' => $item['duration'] ?? 0,
                            'hold' => $item['hold'] ?? 0,
                            'notbefore' => $item['notbefore'] ?? null,
                            'notafter' => $item['notafter'] ?? null,
                            'deleted' => $item['deleted'] ?? false,
                            'raw_data' => $item,
                        ]);
                    }

                    Log::info("Fetched " . count($slides) . " slides for client {$client->name}");
                } else {
                    Log::error("Slides API error for {$client->name}: " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("Slides fetch failed for {$client->name}: " . $e->getMessage());
            }
        }
    }
}