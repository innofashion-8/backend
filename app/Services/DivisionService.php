<?php

namespace App\Services;

use App\Models\Division;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DivisionService
{
    public function getAllDivisions(): LengthAwarePaginator
    {
        return Division::withCount('admins')
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function getAllDivisionsNoPagination()
    {
        return Division::orderBy('name')->get();
    }

    public function getDivision(string $id): ?Division
    {
        return Division::withCount('admins')->find($id);
    }

    public function createDivision(array $data): Division
    {
        return DB::transaction(function () use ($data) {
            return Division::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
            ]);
        });
    }

    public function updateDivision(string $id, array $data): Division
    {
        return DB::transaction(function () use ($id, $data) {
            $division = Division::findOrFail($id);
            $division->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
            ]);
            return $division->fresh();
        });
    }

    public function deleteDivision(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $division = Division::findOrFail($id);
            
            // Check if division has admins
            if ($division->admins()->count() > 0) {
                throw new \Exception('Cannot delete division with assigned admins');
            }
            
            return $division->delete();
        });
    }
}
