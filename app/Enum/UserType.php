<?php

namespace App\Enum;

enum UserType: string
{
    case INTERNAL = "INTERNAL";
    case EXTERNAL = "EXTERNAL";
    case GUEST    = "GUEST";

    public function isParticipant(): bool
    {
        return in_array($this, [self::INTERNAL, self::EXTERNAL]);
    }

    public function isGuest(): bool
    {
        return $this === self::GUEST;
    }
}

