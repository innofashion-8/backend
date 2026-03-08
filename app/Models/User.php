<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\UserType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuids, HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'type',
        'institution',
        'nrp',
        'batch',
        'major',
        'ktm_path',
        'id_card_path',
        'phone',
        'line',
        'draft_data',
        'is_profile_complete',
    ];

    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'type' => UserType::class,
            'draft_data' => 'array',
            'is_profile_complete' => 'boolean',
        ];
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'user_id', 'id');
    }

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class, 'user_id', 'id');
    }

    public function competitionMembers()
    {
        return $this->hasMany(CompetitionMember::class, 'user_id', 'id');
    }
}
