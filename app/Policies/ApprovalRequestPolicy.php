<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalRequestPolicy
{
    /**
     * Determine whether the user can view any approval requests.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if (in_array($user->role, [UserRole::VALIDATOR, UserRole::PROMOTER, UserRole::BILHETERIA]) && $user->is_active) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the approval request.
     */
    public function view(User $user, ApprovalRequest $request): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        return $request->requester_id === $user->id;
    }

    /**
     * Determine whether the user can create approval requests.
     */
    public function create(User $user): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if (in_array($user->role, [UserRole::VALIDATOR, UserRole::PROMOTER]) && $user->is_active) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the approval request.
     */
    public function approve(User $user, ApprovalRequest $request): bool
    {
        if ($user->role !== UserRole::ADMIN || ! $user->is_active) {
            return false;
        }

        return $request->requester_id !== $user->id;
    }

    /**
     * Determine whether the user can reject the approval request.
     */
    public function reject(User $user, ApprovalRequest $request): bool
    {
        if ($user->role !== UserRole::ADMIN || ! $user->is_active) {
            return false;
        }

        return $request->requester_id !== $user->id;
    }

    /**
     * Determine whether the user can reconsider the approval request.
     */
    public function reconsider(User $user, ApprovalRequest $request): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    /**
     * Determine whether the user can revert the approval request.
     */
    public function revert(User $user, ApprovalRequest $request): bool
    {
        if ($user->role !== UserRole::ADMIN || ! $user->is_active) {
            return false;
        }

        return $request->requester_id !== $user->id;
    }

    /**
     * Determine whether the user can cancel the approval request.
     */
    public function cancel(User $user, ApprovalRequest $request): bool
    {
        return $request->requester_id === $user->id && $request->isPending();
    }

    /**
     * Determine whether the user can view the report.
     */
    public function viewReport(User $user): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }
}
