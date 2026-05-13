<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Services\AdminManagementService;

class AdminManagementController extends Controller
{
    protected AdminManagementService $adminService;

    public function __construct(AdminManagementService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        try {
            $admins = $this->adminService->getAllAdmins();
            $data = [
                'current_page' => $admins->currentPage(),
                'data' => AdminResource::collection($admins),
                'first_page_url' => $admins->url(1),
                'from' => $admins->firstItem(),
                'last_page' => $admins->lastPage(),
                'last_page_url' => $admins->url($admins->lastPage()),
                'next_page_url' => $admins->nextPageUrl(),
                'per_page' => $admins->perPage(),
                'prev_page_url' => $admins->previousPageUrl(),
                'to' => $admins->lastItem(),
                'total' => $admins->total(),
            ];
            return $this->success('Admins fetched successfully', $data);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch admins: ' . $e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $admin = $this->adminService->getAdmin($id);
            
            if (!$admin) {
                return $this->error('Admin not found', 404);
            }

            return $this->success('Admin fetched successfully', new AdminResource($admin));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch admin: ' . $e->getMessage(), 500);
        }
    }

    public function store(AdminRequest $request)
    {
        try {
            $admin = $this->adminService->createAdmin($request->validated());
            return $this->success('Admin created successfully', new AdminResource($admin), 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create admin: ' . $e->getMessage(), 500);
        }
    }

    public function update(AdminRequest $request, string $id)
    {
        try {
            $admin = $this->adminService->updateAdmin($id, $request->validated());
            return $this->success('Admin updated successfully', new AdminResource($admin));
        } catch (\Exception $e) {
            return $this->error('Failed to update admin: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->adminService->deleteAdmin($id);
            return $this->success('Admin deleted successfully', null);
        } catch (\Exception $e) {
            return $this->error('Failed to delete admin: ' . $e->getMessage(), 500);
        }
    }
}
