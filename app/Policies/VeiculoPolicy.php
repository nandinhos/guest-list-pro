<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Auth\Access\HandlesAuthorization;

class VeiculoPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Veiculo $veiculo): bool
    {
        return $veiculo->criado_por === $user->id;
    }

    public function update(User $user, Veiculo $veiculo): bool
    {
        return $veiculo->criado_por === $user->id;
    }

    public function delete(User $user, Veiculo $veiculo): bool
    {
        return $veiculo->criado_por === $user->id;
    }
}
