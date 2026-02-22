<?php

namespace App\Data;

use Illuminate\Http\UploadedFile;
use App\Models\User;

class CompleteProfileDTO
{
    public function __construct(
        public readonly User $user,
        public readonly string $major,
        public readonly ?string $nrp = null,
        public readonly ?string $batch = null,
        public readonly ?UploadedFile $ktm = null,
        public readonly ?UploadedFile $idCard = null,
    ) {}
}