<?php

namespace BitrixRestApi;

use BitrixRestApi\Exception\ApiNotFoundException;

class ApiEntityFactory
{
    const E_UNKNOWN_API = 'Unknown API';
    
    /**
     *
     * @param string $className
     * @return ApiInterface
     */
    public function create($className)
    {
        // проверяем, существует ли этот класс
        // проверяем, реализует ли он интерфейс ApiInterface
        // используем is_subclass_of вместо instanceof чтобы не инстанцировать непроверенный класс
        if (!class_exists($className) || !is_subclass_of($className, ApiInterface::class, true)) {
            throw new ApiNotFoundException(self::E_UNKNOWN_API);
        }
        
        return new $className;
    }
}
