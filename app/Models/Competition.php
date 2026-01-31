<?php

namespace App\Models;

use App\Enum\CompetitionCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'registration_fee',
        'is_active',
    ];

    protected $casts = [
        'category' => CompetitionCategory::class,
        'registration_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'competition_id', 'id');
    }
}
