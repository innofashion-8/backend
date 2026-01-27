<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'registration_fee',
        'is_active',
    ];

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'competition_id', 'id');
    }
}
