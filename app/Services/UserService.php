<?php

namespace App\Services;

use App\Models\CompetitionRegistration;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsers(): Collection
    {
        return $this->user->with(['eventRegistrations.event', 'competitionRegistrations.competition'])->latest()->get();
    }

    public function getUser(string $id): ?User
    {
        return $this->user->with(['eventRegistrations.event', 'competitionRegistrations.competition'])->find($id);
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