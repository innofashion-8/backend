<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasUuids;

    protected $fillable = [
        'registration_id',
        'title',
        'description',
        'file_path',
        'submitted_at',
    ];

    public function competitionRegistration()
    {
        return $this->belongsTo(CompetitionRegistration::class, 'registration_id', 'id');
    }
}
