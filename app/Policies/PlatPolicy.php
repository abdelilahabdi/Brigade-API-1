<?php

namespace App\Policies;

use App\Models\Plat;
use App\Models\User;

class PlatPolicy
{
    public function view(User $user, Plat $plat): bool
    {
        return $plat->restaurant_id === $user->restaurant?->id;
    }

    public function update(User $user, Plat $plat): bool
    {
        return $plat->restaurant_id === $user->restaurant?->id;
    }

    public function delete(User $user, Plat $plat): bool
    {
        return $plat->restaurant_id === $user->restaurant?->id;
    }
}
