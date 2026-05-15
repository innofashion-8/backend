<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AssignPermissionsRequest;
use App\Http\Requests\Admin\PermissionRequest;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Services\RolePermissionService;

class RolePermissionController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    // ========== ROLES ==========
    public function indexRoles()
    {
        try {
            $roles = $this->rolePermissionService->getAllRoles();
            return $this->success('Roles fetched successfully', RoleResource::collection($roles));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch roles: ' . $e->getMessage(), 500);
        }
    }

    public function showRole(int $id)
    {
        try {
            $role = $this->rolePermissionService->getRole($id);
            
            if (!$role) {
                return $this->error('Role not found', 404);
            }

            return $this->success('Role fetched successfully', new RoleResource($role));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch role: ' . $e->getMessage(), 500);
        }
    }

    public function storeRole(RoleRequest $request)
    {
        try {
            $role = $this->rolePermissionService->createRole($request->validated());
            return $this->success('Role created successfully', new RoleResource($role), 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create role: ' . $e->getMessage(), 500);
        }
    }

    public function updateRole(RoleRequest $request, int $id)
    {
        try {
            $role = $this->rolePermissionService->updateRole($id, $request->validated());
            return $this->success('Role updated successfully', new RoleResource($role));
        } catch (\Exception $e) {
            return $this->error('Failed to update role: ' . $e->getMessage(), 500);
        }
    }

    public function destroyRole(int $id)
    {
        try {
            $this->rolePermissionService->deleteRole($id);
            return $this->success('Role deleted successfully', null);
        } catch (\Exception $e) {
            return $this->error('Failed to delete role: ' . $e->getMessage(), 500);
        }
    }

    public function assignPermissions(AssignPermissionsRequest $request, int $roleId)
    {
        try {
            $role = $this->rolePermissionService->assignPermissionsToRole($roleId, $request->validated()['permissions']);
            return $this->success('Permissions assigned successfully', new RoleResource($role));
        } catch (\Exception $e) {
            return $this->error('Failed to assign permissions: ' . $e->getMessage(), 500);
        }
    }

    // ========== PERMISSIONS ==========
    public function indexPermissions()
    {
        try {
            $permissions = $this->rolePermissionService->getAllPermissions();
            return $this->success('Permissions fetched successfully', PermissionResource::collection($permissions));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch permissions: ' . $e->getMessage(), 500);
        }
    }

    public function showPermission(int $id)
    {
        try {
            $permission = $this->rolePermissionService->getPermission($id);
            
            if (!$permission) {
                return $this->error('Permission not found', 404);
            }

            return $this->success('Permission fetched successfully', new PermissionResource($permission));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch permission: ' . $e->getMessage(), 500);
        }
    }

    public function storePermission(PermissionRequest $request)
    {
        try {
            $permission = $this->rolePermissionService->createPermission($request->validated());
            return $this->success('Permission created successfully', new PermissionResource($permission), 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create permission: ' . $e->getMessage(), 500);
        }
    }

    public function updatePermission(PermissionRequest $request, int $id)
    {
        try {
            $permission = $this->rolePermissionService->updatePermission($id, $request->validated());
            return $this->success('Permission updated successfully', new PermissionResource($permission));
        } catch (\Exception $e) {
            return $this->error('Failed to update permission: ' . $e->getMessage(), 500);
        }
    }

    public function destroyPermission(int $id)
    {
        try {
            $this->rolePermissionService->deletePermission($id);
            return $this->success('Permission deleted successfully', null);
        } catch (\Exception $e) {
            return $this->error('Failed to delete permission: ' . $e->getMessage(), 500);
        }
    }
}
