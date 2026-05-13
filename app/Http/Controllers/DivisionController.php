<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\DivisionRequest;
use App\Http\Resources\DivisionResource;
use App\Services\DivisionService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected DivisionService $divisionService;

    public function __construct(DivisionService $divisionService)
    {
        $this->divisionService = $divisionService;
    }

    public function index(Request $request)
    {
        try {
            // Check if request wants all divisions without pagination
            if ($request->query('all') === 'true') {
                $divisions = $this->divisionService->getAllDivisionsNoPagination();
                return $this->success('Divisions fetched successfully', DivisionResource::collection($divisions));
            }

            $divisions = $this->divisionService->getAllDivisions();
            $data = [
                'current_page' => $divisions->currentPage(),
                'data' => DivisionResource::collection($divisions),
                'first_page_url' => $divisions->url(1),
                'from' => $divisions->firstItem(),
                'last_page' => $divisions->lastPage(),
                'last_page_url' => $divisions->url($divisions->lastPage()),
                'next_page_url' => $divisions->nextPageUrl(),
                'per_page' => $divisions->perPage(),
                'prev_page_url' => $divisions->previousPageUrl(),
                'to' => $divisions->lastItem(),
                'total' => $divisions->total(),
            ];
            return $this->success('Divisions fetched successfully', $data);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch divisions: ' . $e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $division = $this->divisionService->getDivision($id);
            
            if (!$division) {
                return $this->error('Division not found', 404);
            }

            return $this->success('Division fetched successfully', new DivisionResource($division));
        } catch (\Exception $e) {
            return $this->error('Failed to fetch division: ' . $e->getMessage(), 500);
        }
    }

    public function store(DivisionRequest $request)
    {
        try {
            $division = $this->divisionService->createDivision($request->validated());
            return $this->success('Division created successfully', new DivisionResource($division), 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create division: ' . $e->getMessage(), 500);
        }
    }

    public function update(DivisionRequest $request, string $id)
    {
        try {
            $division = $this->divisionService->updateDivision($id, $request->validated());
            return $this->success('Division updated successfully', new DivisionResource($division));
        } catch (\Exception $e) {
            return $this->error('Failed to update division: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->divisionService->deleteDivision($id);
            return $this->success('Division deleted successfully', null);
        } catch (\Exception $e) {
            return $this->error('Failed to delete division: ' . $e->getMessage(), 500);
        }
    }
}
