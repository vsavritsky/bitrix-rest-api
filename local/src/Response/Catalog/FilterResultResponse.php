<?php

namespace App\Response\Catalog;

use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use CCatalogProduct;

class FilterResultResponse extends BaseSuccessResponse implements \JsonSerializable
{
    public $filter = [];

    public static $resultFields = [
        'filter'
    ];

    public function __construct($filter)
    {
        parent::__construct(null);
        $this->populate(['filter' => $filter]);
    }

    public function populate($object)
    {
        foreach ($object['filter'] as $key => $value) {
            if (isset($value['values'][0]) && $value['values'][0] == null) {
                unset($object['filter'][$key]);
            }

            if (isset($object['filter'][$key]['values']) && !isset($object['filter'][$key]['values']['min'])) {
                $object['filter'][$key]['values'] = array_values($value['values']);
            }
        }

        $this->filter = array_values($object['filter']->jsonSerialize());
    }
}
