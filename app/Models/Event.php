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
        'start_time',
        'is_active',
    ];

    protected $casts = [
        'category' => EventCategory::class,
        'start_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }
}
