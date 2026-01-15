<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\Device;
use BackedEnum;

class ViewDevices extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected string $view = 'filament.pages.view-devices';

    public function table(Table $table): Table
    {
        return $table
            ->query(Device::query())
            ->columns([
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Site Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('app_name')
                    ->label('App Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('site_id')
                    ->label('Site ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('display_id')
                    ->label('Display ID')
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
        return 'View Devices';
    }

    public static function getNavigationLabel(): string
    {
        return 'View Devices';
    }
}