<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        $record = static::getModel()::create($data);

        $this->record = $record;

        $this->redirect($this->getRedirectUrl());
    }
}
