<?php

namespace App\Models;

use App\Enum\AttendedStatus;
use App\Enum\TicketCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTicket extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_code',
        'event_registration_id',
        'guest_name',
        'ticket_category',
        'attended_status',
        'check_in_at',
        'check_out_at',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'attended_status' => AttendedStatus::class,
        'ticket_category' => TicketCategory::class,
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }
}
