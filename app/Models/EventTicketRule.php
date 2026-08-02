<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventTicketRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'condition_type',
        'condition_value',
        'max_tickets',
    ];
    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
