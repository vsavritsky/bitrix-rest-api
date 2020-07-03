<?php

namespace BitrixRestApi\Responser;

/**
 *
 * @author dmitriy
 */
interface ResponserInterface
{
    function send(array $result, $error = false);
}
