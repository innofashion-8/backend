<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasUuids;
    protected $table = 'evaluations';

    protected $fillable = [
        'event_registration_id',
    ];

    public function eventRegistration()
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id', 'id');
    }

    public function evaluationAnswers()
    {
        return $this->hasMany(EvaluationAnswer::class, 'evaluation_id', 'id');
    }
}
