<?php

use App\Http\Middleware\ForceJsonResponseMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->throttleApi()
            ->api(
                prepend: ForceJsonResponseMiddleware::class
            );
    })
    ->withSchedule(function (Schedule $schedule) {
        // Schedule data fetching every 15 minutes
        $schedule->command('fetch:all --sync')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
