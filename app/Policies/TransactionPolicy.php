<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->canView($user);
    }

    public function refund(User $user, Transaction $transaction): bool
    {
        return in_array($user->role, ['admin', 'finance'], true);
    }

    private function canView(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'finance'], true);
    }
}
