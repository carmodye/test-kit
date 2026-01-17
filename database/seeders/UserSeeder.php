<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main admin user
        $adminUser = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role
        $adminUser->assignRole('super_admin');

        // Get all clients
        $clients = Client::all();

        foreach ($clients as $client) {
            // Create admin user for this client
            $clientAdmin = User::firstOrCreate([
                'email' => $client->name . '_admin@example.com',
            ], [
                'name' => ucfirst($client->name) . ' Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign admin role
            $clientAdmin->assignRole('admin');

            // Associate with client
            $clientAdmin->clients()->syncWithoutDetaching([$client->id]);

            // Create regular user for this client
            $clientUser = User::firstOrCreate([
                'email' => $client->name . '_user@example.com',
            ], [
                'name' => ucfirst($client->name) . ' User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign user role
            $clientUser->assignRole('user');

            // Associate with client
            $clientUser->clients()->syncWithoutDetaching([$client->id]);
        }
    }
}