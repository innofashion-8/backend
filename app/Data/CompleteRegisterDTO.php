<?php

namespace App\Data;

use Illuminate\Http\UploadedFile;
use App\Models\User;

class CompleteRegisterDTO
{
    public function __construct(
        public readonly User $user,
        public string $phone,
        public string $major,
        public ?string $line = null,
        public ?string $institution = null,
        public readonly ?string $nrp = null,
        public readonly ?string $batch = null,
        public readonly ?UploadedFile $ktm = null,
        public readonly ?UploadedFile $idCard = null,
    ) {}
}