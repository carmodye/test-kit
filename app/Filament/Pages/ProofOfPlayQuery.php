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
        ArrayRecord::keyName('key');

        return $table
            ->query(Client::query()->whereRaw('1 = 0')) // Dummy query to satisfy Filament
            ->columns($this->getTableColumns())
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
                    ->label('Duration (seconds)'),
                Tables\Columns\TextColumn::make('play_count')
                    ->label('Play Count'),
            ];
        }

        if ($this->currentMode === ProofOfPlayMode::SitesBySlide->value) {
            return [
                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID'),
                Tables\Columns\TextColumn::make('display_id')
                    ->label('Display ID'),
                Tables\Columns\TextColumn::make('site_id')
                    ->label('Site ID'),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration (seconds)'),
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
                ->label('Duration'),
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
                            ->readonly(fn($get) => $get('client')),

                        Forms\Components\DatePicker::make('end')
                            ->label('End Date')
                            ->required()
                            ->live()
                            ->readonly(fn($get) => $get('client')),

                    Forms\Components\Select::make('client')
                        ->label('Client')
                        ->options(function ($get) {
                            $user = auth()->user();
                            $options = $user && ($user->hasRole('super_admin') || $user->hasRole('admin')) ? Client::pluck('name', 'name') : ($user ? $user->clients()->pluck('name', 'name') : collect());

                            $start = $get('start');
                            $end = $get('end');
                            if ($start && $end && empty($this->allSlides)) {
                                // fetch data
                                $body = [
                                    'mode' => 'playedSlides',
                                    'start' => $start . 'T00:00:00Z',
                                    'end' => $end . 'T00:00:00Z',
                                ];
                                Log::info('Making API call for data with body: ' . json_encode($body));
                                try {
                                    $response = Http::withHeaders([
                                        'authorizationToken' => 'my-secret',
                                        'x-api-key' => 'my key',
                                        'Content-Type' => 'application/json',
                                    ])->post(config('services.api.url') . '/queryv3', $body);

                                    if ($response->successful()) {
                                        $data = $response->json();
                                        Log::info('Data API response successful, data count: ' . count($data));
                                        $this->allSlides = collect($data)->unique('slide_id')->mapWithKeys(fn($item) => [
                                            $item['slide_id'] => $item
                                        ])->all();
                                        $this->allSites = collect($data)->unique('site_id')->mapWithKeys(fn($item) => [
                                            $item['site_id'] => $item
                                        ])->all();
                                        Log::info('Fetched slides: ' . count($this->allSlides) . ', sites: ' . count($this->allSites));
                                    } else {
                                        Log::info('Data API response failed, status: ' . $response->status() . ', body: ' . $response->body());
                                    }
                                } catch (\Exception $e) {
                                    Log::info('Exception fetching data: ' . $e->getMessage());
                                }
                                $this->lastStart = $start;
                                $this->lastEnd = $end;
                            }

                            return $options;
                        })
                        ->required()
                        ->live()
                        ->visible(fn($get) => $get('start') && $get('end')),

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
                ->visible(fn() => $this->getTableQuery()->count() > 0),
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
        try {
            $response = Http::withHeaders([
                'authorizationToken' => 'my-secret',
                'x-api-key' => 'my key',
                'Content-Type' => 'application/json',
            ])->post(config('services.api.url') . '/queryv3', $body);

            if ($response->successful()) {
                $this->tableQuery = collect($response->json())->map(function ($item, $index) {
                    $item['key'] = 'record_' . ($index + 1);
                    return $item;
                });

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

    protected function getTableQuery()
    {
        return $this->tableQuery ?? collect([]);
    }

    public function exportCsv()
    {
        $records = $this->getTableQuery();

        if ($records->isEmpty()) {
            Notification::make()
                ->title('No data to export')
                ->danger()
                ->send();
            return;
        }

        $csv = Writer::createFromString('');

        if ($this->currentMode === ProofOfPlayMode::SlidesBySite->value) {
            $csv->insertOne(['Slide ID', 'Slide Name', 'Duration (seconds)', 'Play Count']);

            foreach ($records as $record) {
                $csv->insertOne([
                    $record['slide_id'] ?? '',
                    $record['slide_name'] ?? '',
                    $record['duration_seconds'] ?? '',
                    $record['play_count'] ?? '',
                ]);
            }
        } elseif ($this->currentMode === ProofOfPlayMode::SitesBySlide->value) {
            $csv->insertOne(['Device ID', 'Display ID', 'Site ID', 'Duration (seconds)', 'Play Count']);

            foreach ($records as $record) {
                $csv->insertOne([
                    $record['device_id'] ?? '',
                    $record['display_id'] ?? '',
                    $record['site_id'] ?? '',
                    $record['duration_seconds'] ?? '',
                    $record['play_count'] ?? '',
                ]);
            }
        } else {
            $csv->insertOne(['Site Name', 'Slide Name', 'Played At', 'Duration']);

            foreach ($records as $record) {
                $csv->insertOne([
                    $record['site_name'] ?? '',
                    $record['slide_name'] ?? '',
                    $record['played_at'] ?? '',
                    $record['duration'] ?? '',
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