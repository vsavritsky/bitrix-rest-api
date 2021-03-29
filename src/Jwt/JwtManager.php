<?php

namespace BitrixRestApi\Jwt;

use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Validate;
use Symfony\Component\HttpFoundation\Request;

class JwtManager implements JwtManagerInterface
{
    protected $secret = '';
    protected $expirationTime;
    
    
    public function __construct($secret, $expirationTime = 3600)
    {
        $this->secret = $secret;
        $this->expirationTime = $expirationTime;
    }
    
    public function getTokenFromRequest(Request $request): ?string
    {
        return  $token = (string)($request->headers->get('apiKey') ?? $request->cookies->get('apiKey'));
    }
    
    public function create($userId, $issuer = 'localhost'): string
    {
        return Token::create($userId, $this->secret, time() + $this->expirationTime, $issuer);
    }
    
    public function validate($token): bool
    {
        $token = (string)$token;
        
        return Token::validate($token, $this->secret);
    }
    
    public function getUserIdByToken(string $token): string
    {
        $jwt = new Jwt($token, $this->secret);
        $parse = new Parse($jwt, new Validate(), new Encode());
        $parsed = $parse->validate()->parse();
        
        $payload = $parsed->getPayload();
        
        return $payload['user_id'];
    }
}
