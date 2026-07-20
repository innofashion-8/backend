<?php

namespace App\Services\Eligibility;

use App\Contracts\EventEligibilityCheckerInterface;
use App\Models\Event;
use App\Models\User;

class DefaultEligibilityChecker implements EventEligibilityCheckerInterface
{
    public function isEligible(Event $event, User $user): bool
    {
        return true;
    }

    public function getErrorMessage(): string
    {
        return "";
    }
}
