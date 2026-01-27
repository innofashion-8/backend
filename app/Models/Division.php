<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'division_id', 'id');
    }
}
