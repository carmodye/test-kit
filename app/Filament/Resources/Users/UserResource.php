<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    // Static properties to store pending roles and clients during form processing
    protected static array $pendingRoles = [];
    protected static array $pendingClients = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }
        if ($user && $user->hasRole('admin')) {
            // Admin: only show users associated with the same clients
            $clientIds = $user->clients()->pluck('clients.id');
            return parent::getEloquentQuery()->whereHas('clients', function ($query) use ($clientIds) {
                $query->whereIn('clients.id', $clientIds);
            })->orWhere('id', $user->id); // Include themselves
        }
        // User role: no access to user management
        return parent::getEloquentQuery()->whereRaw('1 = 0'); // Return empty query
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle roles and clients assignment for new users
        $roles = $data['roles'] ?? [];
        $clients = $data['clients'] ?? [];

        // Remove from data array so they don't interfere with model creation
        unset($data['roles'], $data['clients']);

        // Store for after creation
        static::$pendingRoles = $roles;
        static::$pendingClients = $clients;

        return $data;
    }

    protected static function afterCreate($record): void
    {
        // Assign roles and clients after user creation
        if (isset(static::$pendingRoles) && static::$pendingRoles) {
            $record->assignRole(static::$pendingRoles);
        }

        if (isset(static::$pendingClients) && static::$pendingClients) {
            $clientIds = \App\Models\Client::whereIn('name', static::$pendingClients)->pluck('id');
            $record->clients()->sync($clientIds);
        }

        // Clean up
        unset(static::$pendingRoles, static::$pendingClients);
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Handle roles and clients for updates
        if (isset($data['roles'])) {
            static::$pendingRoles = $data['roles'];
            unset($data['roles']);
        }

        if (isset($data['clients'])) {
            static::$pendingClients = $data['clients'];
            unset($data['clients']);
        }

        // Handle password update from new_password field
        if (isset($data['new_password']) && filled($data['new_password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['new_password']);
        }

        // Remove new_password fields from data before saving
        unset($data['new_password'], $data['new_password_confirmation']);

        return $data;
    }

    protected static function afterSave($record): void
    {
        // Assign roles and clients after user update
        if (isset(static::$pendingRoles)) {
            // Preserve the panel_user role if it exists
            $currentRoles = $record->roles->pluck('name')->toArray();
            $hasPanelUser = in_array('panel_user', $currentRoles);

            $record->syncRoles(static::$pendingRoles);

            // Re-add panel_user role if it was present
            if ($hasPanelUser) {
                $record->assignRole('panel_user');
            }
        }

        if (isset(static::$pendingClients)) {
            $clientIds = \App\Models\Client::whereIn('name', static::$pendingClients)->pluck('id');
            $record->clients()->sync($clientIds);
        }

        // Clean up
        unset(static::$pendingRoles, static::$pendingClients);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
