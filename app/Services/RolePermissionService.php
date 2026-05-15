<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    public function getAllRoles()
    {
        return Role::where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    public function getRole(int $id): ?Role
    {
        return Role::where('guard_name', 'admin')
            ->with('permissions')
            ->find($id);
    }

    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            return Role::create([
                'name' => $data['name'],
                'guard_name' => 'admin',
            ]);
        });
    }

    public function updateRole(int $id, array $data): Role
    {
        return DB::transaction(function () use ($id, $data) {
            $role = Role::where('guard_name', 'admin')->findOrFail($id);
            $role->update([
                'name' => $data['name'],
            ]);
            return $role->fresh('permissions');
        });
    }

    public function deleteRole(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $role = Role::where('guard_name', 'admin')->findOrFail($id);
            
            // Check if role is assigned to any admins
            if ($role->users()->count() > 0) {
                throw new \Exception('Cannot delete role that is assigned to admins');
            }
            
            // Remove all permissions
            $role->syncPermissions([]);
            
            return $role->delete();
        });
    }

    public function assignPermissionsToRole(int $roleId, array $permissionNames): Role
    {
        return DB::transaction(function () use ($roleId, $permissionNames) {
            $role = Role::where('guard_name', 'admin')->findOrFail($roleId);
            
            $permissions = Permission::where('guard_name', 'admin')
                ->whereIn('name', $permissionNames)
                ->get();
            
            $role->syncPermissions($permissions);
            
            return $role->fresh('permissions');
        });
    }

    public function getAllPermissions()
    {
        return Permission::where('guard_name', 'admin')
            ->orderBy('name')
            ->get();
    }

    public function getPermission(int $id): ?Permission
    {
        return Permission::where('guard_name', 'admin')->find($id);
    }

    public function createPermission(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            return Permission::create([
                'name' => $data['name'],
                'guard_name' => 'admin',
            ]);
        });
    }

    public function updatePermission(int $id, array $data): Permission
    {
        return DB::transaction(function () use ($id, $data) {
            $permission = Permission::where('guard_name', 'admin')->findOrFail($id);
            $permission->update([
                'name' => $data['name'],
            ]);
            return $permission->fresh();
        });
    }

    public function deletePermission(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $permission = Permission::where('guard_name', 'admin')->findOrFail($id);
            
            // Remove permission from all roles
            $permission->roles()->detach();
            
            return $permission->delete();
        });
    }
}
