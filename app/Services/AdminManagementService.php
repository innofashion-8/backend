<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminManagementService
{
    public function getAllAdmins(): LengthAwarePaginator
    {
        $paginator = Admin::with(['division', 'roles'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Ensure existing admins without assigned roles are automatically synchronized
        foreach ($paginator->items() as $admin) {
            if ($admin->roles->isEmpty() && $admin->division_id) {
                $admin->syncRoleByDivision();
                $admin->load('roles');
            }
        }

        return $paginator;
    }

    public function getAdmin(string $id): ?Admin
    {
        return Admin::with(['division', 'roles', 'permissions'])->find($id);
    }

    public function createAdmin(array $data): Admin
    {
        return DB::transaction(function () use ($data) {
            $admin = Admin::create([
                'name' => $data['name'],
                'nrp' => $data['nrp'],
                'email' => $data['email'],
                'division_id' => $data['division_id'],
            ]);

            // Assign role if provided
            if (!empty($data['role'])) {
                $role = Role::where('name', $data['role'])
                    ->where('guard_name', 'admin')
                    ->first();
                
                if ($role) {
                    $admin->assignRole($role);
                }
            }

            return $admin->load(['division', 'roles']);
        });
    }

    public function updateAdmin(string $id, array $data): Admin
    {
        return DB::transaction(function () use ($id, $data) {
            $admin = Admin::findOrFail($id);
            
            $admin->update([
                'name' => $data['name'],
                'nrp' => $data['nrp'],
                'email' => $data['email'],
                'division_id' => $data['division_id'],
            ]);

            // Update role if provided
            if (isset($data['role'])) {
                if (!empty($data['role'])) {
                    $role = Role::where('name', $data['role'])
                        ->where('guard_name', 'admin')
                        ->first();
                    
                    if ($role) {
                        $admin->syncRoles([$role]);
                    }
                } else {
                    // Remove all roles if empty
                    $admin->syncRoles([]);
                }
            }

            return $admin->fresh(['division', 'roles']);
        });
    }

    public function deleteAdmin(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $admin = Admin::findOrFail($id);
            
            // Remove all roles and permissions
            $admin->syncRoles([]);
            $admin->syncPermissions([]);
            
            return $admin->delete();
        });
    }
}
