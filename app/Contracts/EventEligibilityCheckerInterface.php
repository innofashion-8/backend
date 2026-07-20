<?php

namespace App\Contracts;

use App\Models\Event;
use App\Models\User;

interface EventEligibilityCheckerInterface
{
    public function isEligible(Event $event, User $user): bool;
    public function getErrorMessage(): string;
}
