<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Product;

class ProductRepository extends \BitrixModels\Repository\ProductRepository
{
    protected $class = Product::class;
}
