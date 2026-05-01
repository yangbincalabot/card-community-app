<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User\UserBank;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserBankPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the user bank.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User\UserBank  $userBank
     * @return mixed
     */
    public function own(User $user, UserBank $userBank)
    {
        return $user->id === $userBank->user_id;
    }
}
