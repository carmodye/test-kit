<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use App\Models\JobRunTime;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class JobRunTimesWidget extends TableWidget
{
    protected function getTableQuery(): Builder
    {
        return JobRunTime::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID'),
            TextColumn::make('job_name')->label('Job Name')->searchable()->sortable(),
            TextColumn::make('last_run_at')->label('Last Run At')->dateTime(),
        ];
    }
}
