<?php

namespace App\Models;

use App\Enum\StatusRegistration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompetitionRegistration extends Model
{
    use HasUuids;
    protected $table = 'competition_registrations';

    protected $casts = [
        'draft_data' => 'array',
        'status'     => StatusRegistration::class,
    ];

    protected $fillable = [
        'user_id',
        'competition_id',
        'verified_by',
        'draft_data',
        'nrp',
        'batch',
        'major',
        'ktm_path',
        'id_card_path',
        'payment_proof',
        'status',
        'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by', 'id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'registration_id', 'id');
    }
}
