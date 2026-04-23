<?php

namespace App\Policies;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonitorPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Monitor $monitor): bool
    {
        return $monitor->criado_por === $user->id;
    }

    public function update(User $user, Monitor $monitor): bool
    {
        return $monitor->criado_por === $user->id;
    }

    public function delete(User $user, Monitor $monitor): bool
    {
        return $monitor->criado_por === $user->id;
    }
}
