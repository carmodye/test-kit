<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate shield permissions for the admin panel
        Artisan::call('shield:generate', [
            '--all' => true,
            '--ignore-existing-policies' => true,
            '--panel' => 'pop'
        ]);

        // Make the admin user a super admin for the panel
        $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            Artisan::call('shield:super-admin', [
                '--user' => $adminUser->id,
                '--panel' => 'pop'
            ]);
        }
    }
}