<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EvaluationAnswer extends Model
{
    use HasUuids;
    protected $table = 'evaluation_answers';

    protected $fillable = [
        'evaluation_id',
        'evaluation_question_id',
        'answer_value',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id', 'id');
    }

    public function evaluationQuestion()
    {
        return $this->belongsTo(EvaluationQuestion::class, 'evaluation_question_id', 'id');
    }
}
