<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'manage_users',
            'manage_competitions',
            'manage_events',
            'manage_registrations',
            'manage_divisions',
            'manage_admins',
            'scan_attendance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $super = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $super->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        $bph = Role::firstOrCreate(['name' => 'bph', 'guard_name' => 'admin']);
        $bphPermissions = Permission::where('guard_name', 'admin')
            ->whereIn('name', [
                'view_dashboard', 
                'manage_users', 
                'manage_competitions', 
                'manage_events', 
                'manage_registrations', 
                'scan_attendance'
            ])->get();
        $bph->givePermissionTo($bphPermissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminPermissions = Permission::where('guard_name', 'admin')
            ->whereIn('name', [
                'view_dashboard', 
                'manage_users', 
                'manage_registrations', 
                'scan_attendance'
            ])->get();
        $admin->givePermissionTo($adminPermissions);
    }
}