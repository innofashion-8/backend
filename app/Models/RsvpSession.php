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
        'rsvpsable_id',
        'rsvpsable_type',
        'max_tickets_per_user',
        'total_quota',
        'is_active',
        'rsvp_open_at',
        'rsvp_close_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rsvp_open_at' => 'datetime',
        'rsvp_close_at' => 'datetime',
        'max_tickets_per_user' => 'integer',
        'total_quota' => 'integer',
    ];

    /**
     * Polymorphic relation to Competition or Event
     */
    public function rsvpsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function userRsvps(): HasMany
    {
        return $this->hasMany(UserRsvp::class);
    }
}
