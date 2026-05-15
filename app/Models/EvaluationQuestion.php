<?php

namespace App\Models;

use App\Enum\QuestionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    use HasUuids;
    protected $table = 'evaluation_questions';

    protected $casts = [
        'type'        => QuestionType::class,
        'options'     => 'array',
        'is_required' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected $fillable = [
        'event_id',
        'question_text',
        'type',
        'options',
        'is_required',
        'sort_order',
        'page_number',
        'header_title',
        'header_description',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function evaluationAnswers()
    {
        return $this->hasMany(EvaluationAnswer::class, 'evaluation_question_id', 'id');
    }
}
