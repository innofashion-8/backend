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
        // 'category',
        'participant_type',
        'min_members',
        'max_members',
        'wa_link_international',
        'wa_link_national',
        'description',
        // 'registration_fee',
        'is_active',
        'registration_open_at',
        'registration_close_at',
        'submission_open_at',
        'submission_close_at',
    ];

    protected $casts = [
        // 'category' => CompetitionCategory::class,
        // 'registration_fee' => 'integer',
        'is_active' => 'boolean',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
        'submission_open_at' => 'datetime',
        'submission_close_at' => 'datetime',
    ];

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'competition_id', 'id');
    }
}
