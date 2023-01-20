<?php

namespace App\Repository\Settings;

use App\Entity\Settings\Menu;

class MenuRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = Menu::class;
}
