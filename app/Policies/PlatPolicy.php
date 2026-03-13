<?php

namespace App\Policies;

use App\Models\Plat;
use App\Models\User;

class PlatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Plat $plat): bool
    {
        return $user->id === $plat->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Plat $plat): bool
    {
        return $user->id === $plat->user_id;
    }

    public function delete(User $user, Plat $plat): bool
    {
        return $user->id === $plat->user_id;
    }
}