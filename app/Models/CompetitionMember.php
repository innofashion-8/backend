<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompetitionMember extends Model
{
    use HasUuids;
    protected $fillable = [
        'registration_id',
        'user_id',
        'member_order'
    ];

    public function registration()
    {
        return $this->belongsTo(CompetitionRegistration::class, 'registration_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
