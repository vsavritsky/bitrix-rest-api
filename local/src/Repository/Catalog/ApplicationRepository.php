<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Application;

class ApplicationRepository extends \BitrixModels\Repository\HighloadRepository
{
    protected $class = Application::class;

    public function __construct()
    {
        parent::__construct($this->class);
    }
}
