<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function view(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant?->id;
    }

    public function update(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant?->id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant?->id;
    }
}
