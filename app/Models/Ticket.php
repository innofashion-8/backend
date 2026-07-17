<?php

namespace App\Models;

use App\Enum\AttendedStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_rsvp_id',
        'ticket_code',
        'guest_name',
        'attended_status',
        'check_in_at',
        'check_out_at',
    ];

    protected $casts = [
        'attended_status' => AttendedStatus::class,
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function userRsvp(): BelongsTo
    {
        return $this->belongsTo(UserRsvp::class);
    }
}
