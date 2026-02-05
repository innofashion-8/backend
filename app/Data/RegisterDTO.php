<?php

namespace App\Data;

use App\Enum\UserType;

class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly UserType $type,
        public readonly string $institution,
        public readonly string $phone,
        public readonly ?string $line
    ) {
    }
}
