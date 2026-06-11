<?php

namespace App\Presentation\User\Policies;

use App\Infrastructure\User\Models\User;

class UserPolicy
{
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id; // Only user can view their own full profile
    }

    public function delete(User $user, User $model): bool
    {
        // Add actual role check later
        return false;
    }
}
