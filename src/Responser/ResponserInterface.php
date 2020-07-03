<?php

namespace BitrixApi\Responser;

/**
 *
 * @author dmitriy
 */
interface ResponserInterface
{
    function send(array $result, $error = false);
}
