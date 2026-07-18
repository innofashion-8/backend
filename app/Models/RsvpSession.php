<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RsvpSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'max_tickets_per_user',
        'total_quota',
        'is_active',
        'rsvp_open_at',
        'rsvp_close_at',
        'start_time',
        'end_time',
        'venue',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rsvp_open_at' => 'datetime',
        'rsvp_close_at' => 'datetime',
        'max_tickets_per_user' => 'integer',
        'total_quota' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function userRsvps(): HasMany
    {
        return $this->hasMany(UserRsvp::class);
    }
}
