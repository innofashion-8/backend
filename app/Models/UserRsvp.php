<?php

namespace App\Models;

use App\Enum\StatusRegistration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRsvp extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'rsvp_session_id',
        'verified_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => StatusRegistration::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by', 'id');
    }

    public function rsvpSession(): BelongsTo
    {
        return $this->belongsTo(RsvpSession::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
