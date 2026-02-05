<?php

namespace App\Models;

use App\Enum\StatusRegistration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasUuids;
    protected $table = 'event_registrations';

    protected $casts = [
        'draft_data' => 'array',
        'status'     => StatusRegistration::class,
        'attended'   => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'event_id',
        'verified_by',
        'nrp',
        'major',
        'payment_proof',
        'status',
        'rejection_reason',
        'attended',
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
}
