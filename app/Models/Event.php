<?php

namespace App\Models;

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

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }
}
