<?php

namespace App\Models;

use App\Enum\AttendedStatus;
use App\Enum\StatusRegistration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasUuids;
    protected $table = 'event_registrations';

    protected $casts = [
        'draft_data'      => 'array',
        'status'          => StatusRegistration::class,
        'attended_status' => AttendedStatus::class,
        'check_in_at'     => 'datetime',
        'check_out_at'    => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'event_id',
        'verified_by',
        'draft_data',
        'payment_proof',
        'status',
        'rejection_reason',
        'attended_status',
        'check_in_at',
        'check_out_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by', 'id');
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class, 'event_registration_id', 'id');
    }
}
