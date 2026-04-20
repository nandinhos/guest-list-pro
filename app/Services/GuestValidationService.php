<?php

namespace App\Services;

use App\Models\EventAssignment;
use App\Models\Guest;
use App\Models\User;
use App\Rules\GuestLimitRule;
use App\Rules\PlusOneRule;
use App\Rules\TimeWindowRule;

class GuestValidationService
{
    public function __construct(
        private User $user,
        private int $eventId,
        private int $sectorId
    ) {}

    public static function for(User $user, int $eventId, int $sectorId): self
    {
        return new self($user, $eventId, $sectorId);
    }

    public function canRegister(): array
    {
        $limitCheck = GuestLimitRule::validateLimit($this->user, $this->eventId, $this->sectorId);

        if (! $limitCheck['allowed']) {
            return $limitCheck;
        }

        $timeCheck = TimeWindowRule::validateTimeWindow($this->user->id, $this->eventId, $this->sectorId);

        if (! $timeCheck['allowed']) {
            return $timeCheck;
        }

        return [
            'allowed' => true,
            'remaining' => $limitCheck['remaining'] ?? 0,
        ];
    }

    public function canAddCompanion(): array
    {
        return PlusOneRule::validatePlusOne($this->user, $this->eventId, $this->sectorId);
    }

    public function getPermission(): ?EventAssignment
    {
        return EventAssignment::where('user_id', $this->user->id)
            ->where('event_id', $this->eventId)
            ->where('sector_id', $this->sectorId)
            ->first();
    }

    public function getGuestCount(): int
    {
        return Guest::where('promoter_id', $this->user->id)
            ->where('event_id', $this->eventId)
            ->where('sector_id', $this->sectorId)
            ->count();
    }

    public function getCompanionCount(): int
    {
        return Guest::where('parent_id', '!=', null)
            ->whereHas('parent', fn ($q) => $q->where('promoter_id', $this->user->id))
            ->where('event_id', $this->eventId)
            ->where('sector_id', $this->sectorId)
            ->count();
    }

    public function getSummary(): array
    {
        $permission = $this->getPermission();

        return [
            'permission' => $permission,
            'guest_limit' => $permission?->guest_limit ?? 0,
            'guests_used' => $this->getGuestCount(),
            'guests_remaining' => $permission ? max(0, $permission->guest_limit - $this->getGuestCount()) : 0,
            'plus_one_enabled' => $permission?->plus_one_enabled ?? false,
            'plus_one_limit' => $permission?->plus_one_limit ?? 0,
            'companions_used' => $this->getCompanionCount(),
            'companions_remaining' => $permission ? max(0, $permission->plus_one_limit - $this->getCompanionCount()) : 0,
            'time_window_active' => $permission ? TimeWindowRule::isWithinWindow($permission) : false,
            'can_register' => $this->canRegister()['allowed'],
            'can_add_companion' => $this->canAddCompanion()['allowed'],
        ];
    }
}
