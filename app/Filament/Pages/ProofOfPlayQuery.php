<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use League\Csv\Writer;
use Illuminate\Support\Facades\Http;
use App\Models\Client;
use App\Enums\ProofOfPlayMode;
use App\Models\Device;
use App\Models\Slide;
use App\Models\ProofOfPlayResult;
use Filament\Support\ArrayRecord;
use Filament\Actions\Action;
use BackedEnum;
use Illuminate\Support\Facades\Log;

class ProofOfPlayQuery extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-play-circle';

    protected string $view = 'filament.pages.proof-of-play-query';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_proof_of_play');
    }

    public ?array $data = [];

    public $tableQuery = null;

    public $currentMode = null;

    public $lastQuery = null;

    public $fetchedSlides = [];

    public $fetchedSites = [];

    public $lastClient = null;

    public $lastStart = null;

    public $lastEnd = null;

    public $allSlides = [];

    public $allSites = [];

    public function mount(): void
    {
        ArrayRecord::keyName('key');
        $this->currentMode = ProofOfPlayMode::SitesBySlide->value;
    }

    public function getTitle(): string
    {
        return 'Proof of Play Query';
    }

    public static function getNavigationLabel(): string
    {
        return 'Proof of Play Query';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProofOfPlayResult::with(['slide']))
            ->columns($this->getTableColumns())
            ->actions([
                Action::make('view_slide')
                    ->icon('heroicon-o-eye')
                    ->label('View Slide')
                    ->fillForm(function ($record) {
                        // Manually load the slide relationship
                        $record->load('slide');
                        $slide = $record->slide;
                        
                        if (!$slide) return [];
                        
                        $url = "https://{$slide->client}.cms.ab-net.us/uploads/{$slide->path}/{$slide->name}";
                        $isVideo = in_array(strtolower(pathinfo($slide->name, PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg']);
                        
                        return [
                            'slide' => $slide,
                            'url' => $url,
                            'is_video' => $isVideo,
                        ];
                    })
                    ->form([
                        Forms\Components\Placeholder::make('preview')
                            ->label('')
                            ->content(function ($get) {
                                $slide = $get('slide');
                                $url = $get('url');
                                $isVideo = $get('is_video');
                                
                                if (!$slide) return new \Illuminate\Support\HtmlString('<p>Slide not found.</p>');
                                
                                $html = '<div class="space-y-4">';
                                $html .= '<div>';
                                $html .= '<h3 class="text-lg font-semibold mb-2">Preview</h3>';
                                $html .= '<div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800 flex justify-center">';
                                
                                if ($isVideo) {
                                    $html .= '<video controls class="max-w-full max-h-96 rounded">';
                                    $html .= '<source src="' . $url . '" type="video/' . strtolower(pathinfo($slide->name, PATHINFO_EXTENSION)) . '">';
                                    $html .= 'Your browser does not support the video tag.';
                                    $html .= '</video>';
                                } else {
                                    $html .= '<img src="' . $url . '" alt="' . $slide->name . '" class="max-w-full max-h-96 object-contain rounded">';
                                }
                                
                                $html .= '</div></div></div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->modalHeading(fn($record) => "View Slide: " . ($record->slide?->name ?? 'Unknown'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn($record) => $this->currentMode === ProofOfPlayMode::SlidesBySite->value && $record->slide),
            ])
            ->emptyStateHeading('No results')
            ->emptyStateDescription('Run a query to see proof of play data.')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $this->tableQuery ?? collect();
    }

    protected function getTableColumns(): array
    {
        if ($this->currentMode === ProofOfPlayMode::SlidesBySite->value) {
            return [
                Tables\Columns\TextColumn::make('slide_id')
                    ->label('Slide ID'),
                Tables\Columns\TextColumn::make('slide_name')
                    ->label('Slide Name'),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration (hours)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 3600, 2) : '0.00'),
                Tables\Columns\TextColumn::make('play_count')
                    ->label('Play Count'),
            ];
        }

        if ($this->currentMode === ProofOfPlayMode::SitesBySlide->value) {
            return [
                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID'),
                Tables\Columns\TextColumn::make('device_name')
                    ->label('Device Name'),
                Tables\Columns\TextColumn::make('display_id')
                    ->label('Display ID'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Display Name'),
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Site Name'),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration (hours)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 3600, 2) : '0.00'),
                Tables\Columns\TextColumn::make('play_count')
                    ->label('Play Count'),
            ];
        }

        return [
            Tables\Columns\TextColumn::make('site_name')
                ->label('Site Name'),
            Tables\Columns\TextColumn::make('slide_name')
                ->label('Slide Name'),
            Tables\Columns\TextColumn::make('played_at')
                ->label('Played At')
                ->dateTime(),
            Tables\Columns\TextColumn::make('duration')
                ->label('Duration (hours)')
                ->formatStateUsing(function ($state) {
                    if (!$state) return '0.00';
                    
                    // If it's already a formatted string, try to extract hours
                    if (is_string($state) && !is_numeric($state)) {
                        // Try to parse formatted duration like "2h 30m 15s"
                        if (preg_match('/(\d+)h/', $state, $matches)) {
                            $hours = (int)$matches[1];
                            if (preg_match('/(\d+)m/', $state, $matches)) {
                                $hours += $matches[1] / 60;
                            }
                            if (preg_match('/(\d+)s/', $state, $matches)) {
                                $hours += $matches[1] / 3600;
                            }
                            return number_format($hours, 2);
                        }
                        return $state; // Return as-is if can't parse
                    }
                    
                    // If it's numeric (seconds), convert to hours
                    return number_format($state / 3600, 2);
                }),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('runQuery')
                ->label('Run Query')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->form([
                        Forms\Components\DatePicker::make('start')
                            ->label('Start Date')
                            ->required()
                            ->live()
                            ->readonly(fn($get) => $get('client'))
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $end = $get('end');
                                if ($state && $end) {
                                    // fetch data for dropdown options
                                    $body = [
                                        'mode' => 'playedSlides',
                                        'start' => $state . 'T00:00:00Z',
                                        'end' => $end . 'T23:59:00Z',
                                    ];
                                    Log::info('Making API call for dropdown data with body: ' . json_encode($body));
                                    if ($this->shouldShowDebugNotifications()) {
                                        Notification::make()
                                            ->title('API Call Debug')
                                            ->body('Request body: ' . json_encode($body))
                                            ->info()
                                            ->send();
                                    }
                                    try {
                                        $response = Http::withHeaders([
                                            'authorizationToken' => 'my-secret',
                                            'x-api-key' => 'my key',
                                            'Content-Type' => 'application/json',
                                        ])->post(config('services.api.url') . '/queryv3', $body);

                                        if ($response->successful()) {
                                            $data = $response->json();
                                            Log::info('Dropdown data API response successful, data count: ' . count($data));
                                            $this->allSlides = collect($data)->unique('slide_id')->mapWithKeys(fn($item) => [
                                                $item['slide_id'] => $item
                                            ])->all();
                                            $this->allSites = collect($data)->unique('site_id')->mapWithKeys(fn($item) => [
                                                $item['site_id'] => $item
                                            ])->all();
                                            Log::info('Fetched slides: ' . count($this->allSlides) . ', sites: ' . count($this->allSites));
                                        } else {
                                            Log::info('Dropdown data API response failed, status: ' . $response->status() . ', body: ' . $response->body());
                                        }
                                    } catch (\Exception $e) {
                                        Log::info('Exception fetching dropdown data: ' . $e->getMessage());
                                    }
                                    $this->lastStart = $state;
                                    $this->lastEnd = $end;
                                }
                            }),

                        Forms\Components\DatePicker::make('end')
                            ->label('End Date')
                            ->required()
                            ->live()
                            ->readonly(fn($get) => $get('client'))
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $start = $get('start');
                                if ($start && $state) {
                                    // fetch data for dropdown options
                                    $body = [
                                        'mode' => 'playedSlides',
                                        'start' => $start . 'T00:00:00Z',
                                        'end' => $state . 'T23:59:00Z',
                                    ];
                                    Log::info('Making API call for dropdown data with body: ' . json_encode($body));
                                    if ($this->shouldShowDebugNotifications()) {
                                        Notification::make()
                                            ->title('API Call Debug')
                                            ->body('Request body: ' . json_encode($body))
                                            ->info()
                                            ->send();
                                    }
                                    try {
                                        $response = Http::withHeaders([
                                            'authorizationToken' => 'my-secret',
                                            'x-api-key' => 'my key',
                                            'Content-Type' => 'application/json',
                                        ])->post(config('services.api.url') . '/queryv3', $body);

                                        if ($response->successful()) {
                                            $data = $response->json();
                                            Log::info('Dropdown data API response successful, data count: ' . count($data));
                                            $this->allSlides = collect($data)->unique('slide_id')->mapWithKeys(fn($item) => [
                                                $item['slide_id'] => $item
                                            ])->all();
                                            $this->allSites = collect($data)->unique('site_id')->mapWithKeys(fn($item) => [
                                                $item['site_id'] => $item
                                            ])->all();
                                            Log::info('Fetched slides: ' . count($this->allSlides) . ', sites: ' . count($this->allSites));
                                        } else {
                                            Log::info('Dropdown data API response failed, status: ' . $response->status() . ', body: ' . $response->body());
                                        }
                                    } catch (\Exception $e) {
                                        Log::info('Exception fetching dropdown data: ' . $e->getMessage());
                                    }
                                    $this->lastStart = $start;
                                    $this->lastEnd = $state;
                                }
                            }),

                    Forms\Components\Select::make('client')
                        ->label('Client')
                        ->options(function ($get) {
                            $user = auth()->user();
                            $options = $user && ($user->hasRole('super_admin') || $user->hasRole('admin')) ? Client::pluck('name', 'name') : ($user ? $user->clients()->pluck('name', 'name') : collect());

                            return $options;
                        })
                        ->required()
                        ->live()
                        ->visible(fn($get) => $get('start') && $get('end'))
                        ->afterStateUpdated(function ($state, $set, $get) {
                            // Ensure allSites is populated when client changes
                            $start = $get('start');
                            $end = $get('end');
                            if ($start && $end && empty($this->allSlides)) {
                                // fetch data for dropdown options
                                $body = [
                                    'mode' => 'playedSlides',
                                    'start' => $start . 'T00:00:00Z',
                                    'end' => $end . 'T23:59:00Z',
                                ];
                                Log::info('Making API call for dropdown data on client change with body: ' . json_encode($body));
                                if ($this->shouldShowDebugNotifications()) {
                                    Notification::make()
                                        ->title('API Call Debug')
                                        ->body('Request body: ' . json_encode($body))
                                        ->info()
                                        ->send();
                                }
                                try {
                                    $response = Http::withHeaders([
                                        'authorizationToken' => 'my-secret',
                                        'x-api-key' => 'my key',
                                        'Content-Type' => 'application/json',
                                    ])->post(config('services.api.url') . '/queryv3', $body);

                                    if ($response->successful()) {
                                        $data = $response->json();
                                        Log::info('Dropdown data API response successful, data count: ' . count($data));
                                        $this->allSlides = collect($data)->unique('slide_id')->mapWithKeys(fn($item) => [
                                            $item['slide_id'] => $item
                                        ])->all();
                                        $this->allSites = collect($data)->unique('site_id')->mapWithKeys(fn($item) => [
                                            $item['site_id'] => $item
                                        ])->all();
                                        Log::info('Fetched slides: ' . count($this->allSlides) . ', sites: ' . count($this->allSites));
                                    } else {
                                        Log::info('Dropdown data API response failed, status: ' . $response->status() . ', body: ' . $response->body());
                                    }
                                } catch (\Exception $e) {
                                    Log::info('Exception fetching dropdown data: ' . $e->getMessage());
                                }
                                $this->lastStart = $start;
                                $this->lastEnd = $end;
                            }
                        }),

                    Forms\Components\Select::make('mode')
                        ->label('Query Mode')
                        ->options(ProofOfPlayMode::options())
                        ->required()
                        ->default(ProofOfPlayMode::SitesBySlide->value)
                        ->live()
                        ->visible(fn($get) => $get('start') && $get('end') && $get('client')),

                    Forms\Components\Select::make('slideId')
                        ->label('Slide')
                        ->options(function ($get) {
                            $client = $get('client');
                            $start = $get('start');
                            $end = $get('end');
                            if (!$start || !$end)
                                return [];

                            if (!$client)
                                return [];
                            return collect($this->allSlides)->where('client', $client)->mapWithKeys(fn($item) => [
                                $item['slide_id'] => "[{$item['slide_id']}] {$item['slide_name']}"
                            ])->all();
                        })
                        ->searchable()
                        ->required()
                        ->visible(fn($get) => $get('mode') === ProofOfPlayMode::SitesBySlide->value && $get('start') && $get('end') && $get('client')),

                    Forms\Components\Select::make('siteId')
                        ->label('Site')
                        ->options(function ($get) {
                            $client = $get('client');
                            $start = $get('start');
                            $end = $get('end');
                            Log::info('Fetching sites options called with client: ' . $client . ', start: ' . $start . ', end: ' . $end);
                            Log::info('allSites data sample: ' . json_encode(array_slice($this->allSites, 0, 2)));
                            if (!$start || !$end)
                                return [];

                            if (!$client)
                                return [];
                            $filteredSites = collect($this->allSites)->where('client', $client);
                            Log::info('Filtered sites for client ' . $client . ': ' . $filteredSites->count() . ' sites');
                            return $filteredSites->mapWithKeys(fn($item) => [
                                $item['site_id'] => $item['site_id']
                            ])->all();
                        })
                        ->searchable()
                        ->required()
                        ->visible(fn($get) => $get('mode') === ProofOfPlayMode::SlidesBySite->value && $get('start') && $get('end') && $get('client')),
                ])
                ->action(function (array $data): void {
                    $this->runQuery($data);
                }),

            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('exportCsv')
                ->visible(fn() => $this->tableQuery && $this->tableQuery->count() > 0),
        ];
    }

    public function runQuery(array $data): void
    {
        $this->currentMode = $data['mode'];
        $this->lastQuery = $data;

        $body = [
            'mode' => $data['mode'],
            'client' => $data['client'],
            'start' => $data['start'] . 'T00:00:00Z',
            'end' => $data['end'] . 'T23:59:00Z',
        ];

        if ($data['mode'] === ProofOfPlayMode::SitesBySlide->value) {
            $body['slideId'] = (int) $data['slideId'];
        }

        if ($data['mode'] === ProofOfPlayMode::SlidesBySite->value) {
            $body['siteId'] = (int) $data['siteId'];
        }

        Log::info('Making API call for query with body: ' . json_encode($body));
        if ($this->shouldShowDebugNotifications()) {
            Notification::make()
                ->title('API Call Debug')
                ->body('Request body: ' . json_encode($body))
                ->info()
                ->send();
        }
        try {
            $response = Http::withHeaders([
                'authorizationToken' => 'my-secret',
                'x-api-key' => 'my key',
                'Content-Type' => 'application/json',
            ])->post(config('services.api.url') . '/queryv3', $body);

            if ($response->successful()) {
                $apiResults = collect($response->json());
                
                // Clear previous results
                ProofOfPlayResult::truncate();
                
                // Store results with enriched data
                $results = $apiResults->map(function ($item) use ($data) {
                    // Get device info for site_name
                    $device = null;
                    if (isset($item['device_id']) && isset($item['display_id'])) {
                        $device = Device::where('client', $data['client'])
                            ->where('device_id', $item['device_id'])
                            ->where('display_id', $item['display_id'])
                            ->first();
                    }
                    
                    // Get slide info
                    $slide = null;
                    if (isset($item['slide_id'])) {
                        $slide = Slide::where('client', $data['client'])
                            ->where('slide_id', $item['slide_id'])
                            ->first();
                    }
                    
                    return [
                        'client' => $data['client'],
                        'slide_id' => $item['slide_id'] ?? null,
                        'slide_name' => $slide ? $slide->name : ($item['slide_name'] ?? null),
                        'device_id' => $item['device_id'] ?? null,
                        'display_id' => $item['display_id'] ?? null,
                        'display_name' => $device ? $device->display_name : null,
                        'device_name' => $device ? $device->device_name : null,
                        'site_id' => $item['site_id'] ?? null,
                        'site_name' => $device ? $device->site_name : ($item['site_name'] ?? null),
                        'duration_seconds' => $item['duration_seconds'] ?? null,
                        'play_count' => $item['play_count'] ?? null,
                        'played_at' => isset($item['played_at']) ? $item['played_at'] : null,
                        'duration' => $item['duration'] ?? null,
                    ];
                });
                
                // Bulk insert the results
                ProofOfPlayResult::insert($results->toArray());
                
                $this->tableQuery = ProofOfPlayResult::with(['device', 'slide'])->get();

                Notification::make()
                    ->title('Query completed')
                    ->body($this->tableQuery->count() . ' records found')
                    ->success()
                    ->send();

                $this->dispatch('$refresh');
            } else {
                Notification::make()
                    ->title('API Error')
                    ->body('Failed to fetch data from API: ' . $response->status())
                    ->danger()
                    ->send();

                $this->tableQuery = collect([]);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->tableQuery = collect([]);
        }
    }

    protected function shouldShowDebugNotifications(): bool
    {
        $logLevel = env('LOG_LEVEL', 'debug');
        return in_array(strtolower($logLevel), ['debug', 'info']);
    }

    public function exportCsv()
    {
        $records = $this->tableQuery;

        if (!$records || $records->isEmpty()) {
            Notification::make()
                ->title('No data to export')
                ->danger()
                ->send();
            return;
        }

        $csv = Writer::createFromString('');

        if ($this->currentMode === ProofOfPlayMode::SlidesBySite->value) {
            $csv->insertOne(['Slide ID', 'Slide Name', 'Duration (hours)', 'Play Count']);

            foreach ($records as $record) {
                $csv->insertOne([
                    $record->slide_id ?? '',
                    $record->slide_name ?? '',
                    $record->duration_seconds ? number_format($record->duration_seconds / 3600, 2) : '0.00',
                    $record->play_count ?? '',
                ]);
            }
        } elseif ($this->currentMode === ProofOfPlayMode::SitesBySlide->value) {
            $csv->insertOne(['Device ID', 'Device Name', 'Display ID', 'Display Name', 'Site Name', 'Duration (hours)', 'Play Count']);

            foreach ($records as $record) {
                $csv->insertOne([
                    $record->device_id ?? '',
                    $record->device_name ?? '',
                    $record->display_id ?? '',
                    $record->display_name ?? '',
                    $record->site_name ?? '',
                    $record->duration_seconds ? number_format($record->duration_seconds / 3600, 2) : '0.00',
                    $record->play_count ?? '',
                ]);
            }
        } else {
            $csv->insertOne(['Site Name', 'Slide Name', 'Played At', 'Duration (hours)']);

            foreach ($records as $record) {
                $durationHours = '0.00';
                if ($record->duration) {
                    // If it's already a formatted string, try to extract hours
                    if (is_string($record->duration) && !is_numeric($record->duration)) {
                        // Try to parse formatted duration like "2h 30m 15s"
                        if (preg_match('/(\d+)h/', $record->duration, $matches)) {
                            $hours = (int)$matches[1];
                            if (preg_match('/(\d+)m/', $record->duration, $matches)) {
                                $hours += $matches[1] / 60;
                            }
                            if (preg_match('/(\d+)s/', $record->duration, $matches)) {
                                $hours += $matches[1] / 3600;
                            }
                            $durationHours = number_format($hours, 2);
                        } else {
                            $durationHours = $record->duration; // Keep as-is if can't parse
                        }
                    } else {
                        // If it's numeric (seconds), convert to hours
                        $durationHours = number_format($record->duration / 3600, 2);
                    }
                }
                
                $csv->insertOne([
                    $record->site_name ?? '',
                    $record->slide_name ?? '',
                    $record->played_at ?? '',
                    $durationHours,
                ]);
            }
        }

        $filename = 'proof-of-play-' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv->toString();
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }
}