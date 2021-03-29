<?php

namespace BitrixRestApi\Jwt;

use Symfony\Component\HttpFoundation\Request;

interface JwtManagerInterface
{
    public function getTokenFromRequest(Request $request): ?string;
    
    public function create($userId, $issuer = 'localhost'): string;
    
    public function validate($token): bool;
    
    public function getUserIdByToken(string $token): string;
}
