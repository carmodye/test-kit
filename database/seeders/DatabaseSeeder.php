<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Create clients
        $this->call([
            ClientSeeder::class,
        ]);

        // Create users
        $this->call([
            UserSeeder::class,
        ]);

        // Generate shield permissions and assign super admin
        $this->call([
            ShieldSeeder::class,
        ]);
    }
}
