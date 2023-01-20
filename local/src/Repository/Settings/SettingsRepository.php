<?php

namespace App\Repository\Settings;

use App\Entity\Settings\Settings;

class SettingsRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = Settings::class;
}
