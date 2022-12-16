<?php

namespace BitrixRestApi\Jwt;

use ReallySimpleJWT\Decode;
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
        return $token = str_replace('Bearer ', '', (string)($request->headers->get('Authorization') ?? $request->cookies->get('Authorization')));
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
        $encode = new Encode();
        $validate = new Validate();
        $parse = new Parse($jwt, $validate, $encode);
        $parsed = $parse->parse();

        $payload = $parsed->getPayload();

        return $payload['user_id'];
    }
}
