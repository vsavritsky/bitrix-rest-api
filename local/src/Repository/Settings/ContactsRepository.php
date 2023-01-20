<?php

namespace App\Repository\Settings;

use App\Entity\Settings\Contacts;
use App\Entity\Settings\Office;

class ContactsRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = Contacts::class;
}
