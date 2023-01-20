<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\ProductSection;

class ProductSectionRepository extends \BitrixModels\Repository\SectionRepository
{
    protected $class = ProductSection::class;
}
