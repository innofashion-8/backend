<?php

namespace App\Data;

use Illuminate\Http\UploadedFile;

class UpdateProfileDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $institution,
        public readonly string $major,
        public readonly ?string $line = null,
        public readonly ?string $nrp = null,
        public readonly ?string $batch = null,
        public readonly ?UploadedFile $ktmFile = null,
        public readonly ?UploadedFile $idCardFile = null
    ) {}
}