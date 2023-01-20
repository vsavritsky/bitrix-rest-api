<?php

namespace App\Repository\User;

use App\Entity\User\User;

class UserRepository extends \BitrixModels\Repository\UserRepository
{
    protected $class = User::class;
}
