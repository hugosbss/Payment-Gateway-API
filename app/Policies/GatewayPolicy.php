<?php

namespace App\Policies;

use App\Models\Gateway;
use App\Models\User;

class GatewayPolicy
{
    public function update(User $user, Gateway $gateway): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
