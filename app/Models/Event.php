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
        'close_registration_at',
        'is_active',
    ];

    protected $appends = ['start_time_human'];

    public function getStartTimeHumanAttribute()
    {
        return $this->start_time ? $this->start_time->translatedFormat('d F Y, H:i') : null;
    }

    protected $casts = [
        'price'         => 'integer',
        'quota'         => 'integer',
        'category'      => EventCategory::class,
        'start_time'    => 'datetime',
        'close_registration_at' => 'datetime',
        'is_active'     => 'boolean',
    ];

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }

    public function evaluationQuestions()
    {
        return $this->hasMany(EvaluationQuestion::class, 'event_id', 'id');
    }
}
