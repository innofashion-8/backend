<?php

namespace Database\Seeders;

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

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $bph = Role::firstOrCreate(['name' => 'bph', 'guard_name' => 'admin']);
        $lomba = Role::firstOrCreate(['name' => 'lomba', 'guard_name' => 'admin']);
        $sekret = Role::firstOrCreate(['name' => 'sekret', 'guard_name' => 'admin']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);

        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Akses BPH
        $bph->givePermissionTo([
            'view_dashboard', 
            'manage_users', 
            'manage_competitions', 
            'manage_events', 
            'manage_registrations', 
            'scan_attendance'
        ]);

        // Akses Divisi Lomba
        $lomba->givePermissionTo([
            'view_dashboard',
            'manage_users',
            'manage_competitions',
            'manage_registrations',
            'scan_attendance'
        ]);

        // Akses Sekretariat
        $sekret->givePermissionTo([
            'view_dashboard',
            'manage_users',
            'manage_competitions',
            'manage_events',
            'manage_registrations',
            'scan_attendance'
        ]);

        $admin->givePermissionTo([
            'view_dashboard', 
            'manage_users', 
            'manage_registrations', 
            'scan_attendance'
        ]);
    }
}