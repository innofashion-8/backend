<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens, HasUuids;
    use HasRoles;
    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'nrp',
        'email',
        'division_id'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function syncRoleByDivision()
    {
        if (!$this->relationLoaded('division')) {
            $this->load('division');
        }

        if ($this->division) {
            $slug = $this->division->slug;
           
            $specialMap = [
                'it'    => 'super_admin',
                'kabid' => 'bph',
            ];

            $targetRole = $specialMap[$slug] ?? $slug;

            $roleExists = Role::where('name', $targetRole)
                            ->where('guard_name', 'admin')
                            ->exists();

            if ($roleExists) {
                $this->syncRoles($targetRole);
            } else {
                $this->syncRoles('admin');
            }
        }
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'verified_by', 'id');
    }

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'verified_by', 'id');
    }
}
