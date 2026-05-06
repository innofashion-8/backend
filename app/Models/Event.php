<?php

namespace App\Models;

use App\Enum\EventCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUuids;
    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'price',
        'quota',
        'wa_link',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'transfer_note_format',
        'start_time',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'quota' => 'integer',
        'category' => EventCategory::class,
        'start_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }
}
