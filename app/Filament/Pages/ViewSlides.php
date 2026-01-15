<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\Slide;
use BackedEnum;

class ViewSlides extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected string $view = 'filament.pages.view-slides';

    public function table(Table $table): Table
    {
        return $table
            ->query(Slide::query()->active()->content()->orderBy('slide_id'))
            ->columns([
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