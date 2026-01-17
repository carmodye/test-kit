<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->confirmed()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->visible(fn(string $context): bool => $context === 'create'),
                TextInput::make('password_confirmation')
                    ->required(fn(string $context): bool => $context === 'create')
                    ->password()
                    ->dehydrated(false)
                    ->visible(fn(string $context): bool => $context === 'create'),

                Select::make('roles')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->multiple()
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->hasRole('super_admin')) {
                            return \Spatie\Permission\Models\Role::pluck('name', 'name');
                        }
                        if ($user && $user->hasRole('admin')) {
                            return \Spatie\Permission\Models\Role::where('name', 'user')->pluck('name', 'name');
                        }
                        return collect();
                    })
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            $user = auth()->user();
                            if ($user && $user->hasRole('super_admin')) {
                                // Super admin can see all roles including panel_user
                                $component->state($record->roles->pluck('name')->toArray());
                            } else {
                                // Admin users can't see/modify panel_user role
                                $component->state($record->roles->where('name', '!=', 'panel_user')->pluck('name')->toArray());
                            }
                        }
                    })
                    ->label('Roles')
                    ->dehydrated(false),

                Select::make('clients')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->hasRole('super_admin')) {
                            return \App\Models\Client::pluck('name', 'name');
                        }
                        if ($user && $user->hasRole('admin')) {
                            return $user->clients()->pluck('name', 'name');
                        }
                        return collect();
                    })
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            $component->state($record->clients->pluck('name')->toArray());
                        }
                    })
                    ->label('Clients')
                    ->dehydrated(false),
            ]);
    }
}
