<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ActivityPolicy;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    protected array $policies = [
        Activity::class => ActivityPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configurePolicies();

        $this->configureDB();

        $this->configureModels();

        $this->configureFilament();

        User::created(function ($user) {
            $user->sendEmailVerificationNotification();
        });
    }

    private function configurePolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    private function configureDB(): void
    {
        DB::prohibitDestructiveCommands($this->app->environment('production'));
    }

    private function configureModels(): void
    {
        Model::preventAccessingMissingAttributes();

        Model::unguard();
    }

    private function configureFilament(): void
    {
        FilamentShield::prohibitDestructiveCommands($this->app->environment('production'));

        Column::configureUsing(fn(Column $column) => $column->toggleable());

        Table::configureUsing(
            fn(Table $table) => $table
                ->reorderableColumns()
                ->deferColumnManager(false)
                ->deferFilters(false)
                ->paginationPageOptions([10, 25, 50])
        );
    }
}
