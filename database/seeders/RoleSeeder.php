<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create page permissions
        $pagePermissions = [
            'view_proof_of_play',
            'view_devices',
            'view_slides',
        ];

        foreach ($pagePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        // Super admin gets all permissions (will be handled by ShieldSeeder)
        // Admin gets client and user permissions plus page permissions
        $adminPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%client%')
                ->orWhere('name', 'like', '%user%');
        })->orWhereIn('name', $pagePermissions)->get();

        $adminRole->syncPermissions($adminPermissions);

        // User gets only page permissions
        $userPermissions = Permission::whereIn('name', $pagePermissions)->get();
        $userRole->syncPermissions($userPermissions);
    }
}