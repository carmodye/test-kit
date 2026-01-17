<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetAndSeedDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-seed {--fresh : Drop all tables and re-run all migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and seed the database with test data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting and seeding database...');

        if ($this->option('fresh')) {
            $this->info('Running fresh migrations...');
            Artisan::call('migrate:fresh', [], $this->getOutput());
        } else {
            $this->info('Running migrations...');
            Artisan::call('migrate', [], $this->getOutput());
        }

        $this->info('Seeding database...');
        Artisan::call('db:seed', [], $this->getOutput());

        $this->info('Database reset and seeded successfully!');
        $this->info('');
        $this->info('Created users:');
        $this->info('- admin@example.com (password: password) - super_admin');
        $this->info('- dev1_admin@example.com (password: password) - admin for dev1 client');
        $this->info('- dev1_user@example.com (password: password) - user for dev1 client');
        $this->info('- qa2_admin@example.com (password: password) - admin for qa2 client');
        $this->info('- qa2_user@example.com (password: password) - user for qa2 client');
    }
}
