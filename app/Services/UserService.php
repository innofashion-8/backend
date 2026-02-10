<?php

namespace App\Services;

use App\Models\CompetitionRegistration;
use App\Models\EventRegistration;
use App\Models\User;

class UserService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getRegistrations(string $userId): array
    {
        $competitions = CompetitionRegistration::with('competition')
            ->where('user_id', $userId)
            ->get();

        $events = EventRegistration::with('event')
            ->where('user_id', $userId)
            ->get();
        
        return [
            'competitions' => $competitions,
            'events' => $events,
        ];
    }
}