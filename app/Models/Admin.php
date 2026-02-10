<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasApiTokens, HasUuids;

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

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'verified_by', 'id');
    }

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'verified_by', 'id');
    }
}
