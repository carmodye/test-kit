<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $data = $this->form->getState();

        static::getResource()::$tempData = [
            'roles' => $data['roles'] ?? [],
            'clients' => $data['clients'] ?? []
        ];

        unset($data['roles'], $data['clients']);

        $this->record->update($data);

        // Assign roles and clients
        $record = $this->record;
        $record->assignRole(static::getResource()::$tempData['roles'] ?? []);
        $clients = array_map('intval', static::getResource()::$tempData['clients'] ?? []);
        $record->clients()->sync($clients);
        static::getResource()::$tempData = [];

        $this->redirect($this->getRedirectUrl());
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_password')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('primary')
                ->form([
                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->confirmed()
                        ->helperText('Minimum 8 characters'),

                    TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'password' => Hash::make($data['password']),
                    ]);

                    Notification::make()
                        ->title('Password Updated')
                        ->body('The user\'s password has been successfully updated.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
