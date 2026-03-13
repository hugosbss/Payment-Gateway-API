<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrManager($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isAdminOrManager($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrManager($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAdminOrManager($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isAdminOrManager($user);
    }

    private function isAdminOrManager(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }
}
