<?php

namespace App\Enum;

enum UserType: string
{
    case INTERNAL = "INTERNAL";
    case EXTERNAL = "EXTERNAL";
}
