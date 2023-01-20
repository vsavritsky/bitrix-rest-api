<?php

namespace App\Repository\User;

use App\Entity\User\Compare;

class CompareRepository extends \BitrixModels\Repository\HighloadRepository
{
    protected $class = Compare::class;

    public function __construct()
    {
        parent::__construct($this->class);
    }
}
