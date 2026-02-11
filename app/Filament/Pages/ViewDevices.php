<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
//use Filament\Tables\Actions\Action;
use Filament\Actions\Action;
use App\Models\Client;
use BackedEnum;
use App\Models\Device;

class ViewDevices extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-computer-desktop';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_devices');
    }

    protected string $view = 'filament.pages.view-devices';

    public function table(Table $table): Table
    {
        return $table
            ->query(Device::query())
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                if ($user && !$user->hasRole('super_admin')) {
                    // Filter devices by user's associated clients
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

                Tables\Columns\TextColumn::make('device_name')
                    ->label('Device Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('display_id')
                    ->label('Display ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('Display Name')
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
                        $user = auth()->user();
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
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(25)
            ->actions([
                Action::make('view_other_data')
                    ->label('View Other Data')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Device Other Data')
                    ->modalContent(function (Device $record) {
                        return view('filament.modals.device-other-data', ['otherData' => $record->other_data]);
                    })
                    ->modalSubmitAction(false)
                    ->slideOver(),
            ]);
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