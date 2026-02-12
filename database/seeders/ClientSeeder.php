<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = ['dev1', 'qa2', 'rutters'];

        foreach ($clients as $clientName) {
            Client::firstOrCreate([
                'name' => $clientName,
            ]);
        }
    }
}