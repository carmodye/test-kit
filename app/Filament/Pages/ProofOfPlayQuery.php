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
use Filament\Actions\Action;
use BackedEnum;
use Filament\Schemas\Schema;

class ProofOfPlayQuery extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-play-circle';

    protected string $view = 'filament.pages.proof-of-play-query';

    public ?array $data = [];

    public $tableQuery = null;

    public function mount(): void
    {
        // No form to fill - form is in action modal
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
            ->query(Client::query()->whereRaw('1 = 0')) // Dummy query to satisfy Filament
            ->columns([
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Site Name'),
                Tables\Columns\TextColumn::make('slide_name')
                    ->label('Slide Name'),
                Tables\Columns\TextColumn::make('played_at')
                    ->label('Played At')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration'),
            ])
            ->emptyStateHeading('No results')
            ->emptyStateDescription('Run a query to see proof of play data.')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $this->getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runQuery')
                ->label('Run Query')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('mode')
                        ->label('Query Mode')
                        ->options(ProofOfPlayMode::options())
                        ->required()
                        ->default(ProofOfPlayMode::SitesBySlide->value),

                    Forms\Components\Select::make('client')
                        ->label('Client')
                        ->options(Client::pluck('name', 'name'))
                        ->required(),

                    Forms\Components\DatePicker::make('start')
                        ->label('Start Date')
                        ->required(),

                    Forms\Components\DatePicker::make('end')
                        ->label('End Date')
                        ->required(),

                    Forms\Components\Select::make('slideId')
                        ->label('Slide')
                        ->options(function ($get) {
                            $client = $get('client');
                            if (!$client)
                                return [];

                            return Slide::where('client', $client)
                                ->active()
                                ->content()
                                ->orderBy('slide_id')
                                ->get()
                                ->mapWithKeys(fn($slide) => [
                                    $slide->slide_id => "[{$slide->slide_id}] {$slide->name}"
                                ])
                                ->all();
                        })
                        ->required()
                        ->visible(fn($get) => $get('mode') === ProofOfPlayMode::SitesBySlide->value),

                    Forms\Components\Select::make('siteId')
                        ->label('Site')
                        ->options(function ($get) {
                            $client = $get('client');
                            if (!$client)
                                return [];

                            return Device::where('client', $client)
                                ->select(['site_id', 'site_name'])
                                ->distinct()
                                ->orderBy('site_name')
                                ->get()
                                ->mapWithKeys(fn($device) => [
                                    $device->site_id => "{$device->site_id} - {$device->site_name}"
                                ])
                                ->all();
                        })
                        ->required()
                        ->visible(fn($get) => $get('mode') === ProofOfPlayMode::SlidesBySite->value),
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
        $body = [
            'mode' => $data['mode'],
            'client' => $data['client'],
            'start' => $data['start'] . 'T00:00:00Z',
            'end' => $data['end'] . 'T00:00:00Z',
        ];

        if ($data['mode'] === ProofOfPlayMode::SitesBySlide->value) {
            $body['slideId'] = (int) $data['slideId'];
        }

        if ($data['mode'] === ProofOfPlayMode::SlidesBySite->value) {
            $body['siteId'] = (int) $data['siteId'];
        }

        try {
            $response = Http::withHeaders([
                'authorizationToken' => 'my-secret',
                'x-api-key' => 'my key',
                'Content-Type' => 'application/json',
            ])->post(config('services.api.url') . '/query', $body);

            if ($response->successful()) {
                $this->tableQuery = collect($response->json());

                Notification::make()
                    ->title('Query completed')
                    ->body($this->tableQuery->count() . ' records found')
                    ->success()
                    ->send();
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
        $csv->insertOne(['Site Name', 'Slide Name', 'Played At', 'Duration']);

        foreach ($records as $record) {
            $csv->insertOne([
                $record['site_name'] ?? '',
                $record['slide_name'] ?? '',
                $record['played_at'] ?? '',
                $record['duration'] ?? '',
            ]);
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