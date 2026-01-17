<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use App\Models\Slide;
use BackedEnum;
use App\Models\Client;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;


class ViewSlides extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected string $view = 'filament.pages.view-slides';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_slides');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Slide::query())
            ->modifyQueryUsing(function ($query) {
                $query->active()->content()->orderBy('slide_id');

                $user = Auth::user();
                if ($user && !$user->hasRole('super_admin')) {
                    // Filter slides by user's associated clients
                    $clientNames = $user->clients()->pluck('name');
                    $query->whereIn('client', $clientNames);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('client')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slide_id')
                    ->label('Slide ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('client')
                    ->label('Client')
                    ->options(function () {
                        $user = Auth::user();
                        if ($user && $user->hasRole('super_admin')) {
                            return Client::pluck('name', 'name');
                        }
                        return $user ? $user->clients()->pluck('name', 'name') : collect();
                    })
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->where('client', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->label('View Slide')
                    ->modalHeading(fn($record) => "View Slide: {$record->name}")
                    ->modalContent(function ($record) {
                        $url = "https://{$record->client}.cms.ab-net.us/uploads/{$record->path}/{$record->name}";
                        $isVideo = in_array(strtolower(pathinfo($record->name, PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg']);
                        return view('filament.pages.slide-modal', compact('record', 'url', 'isVideo'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Exit')
                    ->slideOver(),
            ])
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public function getTitle(): string
    {
        return 'View Slides';
    }

    public static function getNavigationLabel(): string
    {
        return 'View Slides';
    }
}