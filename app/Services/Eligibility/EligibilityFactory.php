<?php

namespace App\Services\Eligibility;

use App\Contracts\EventEligibilityCheckerInterface;
use App\Models\Event;
use App\Enum\EventCategory;

class EligibilityFactory
{
    public static function make(Event $event): EventEligibilityCheckerInterface
    {
        if ($event->category === EventCategory::RESTYLING) {
            return new RestylingEligibilityChecker();
        }

        return new DefaultEligibilityChecker();
    }
}
