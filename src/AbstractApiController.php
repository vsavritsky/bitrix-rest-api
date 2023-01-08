<?php

namespace BitrixRestApi;

use BitrixModels\Manager\EntityManager;
use BitrixServiceContainer\ServiceContainer;
use BitrixModels\Entity\BaseModel;
use Symfony\Component\HttpFoundation\Request;

class AbstractApiController implements ApiInterface
{
    const LANG_RU = 'ru';
    const LANG_EN = 'en';

    protected Request $request;

    protected BaseModel|null $user;

    protected ServiceContainer|null $serviceContainer;

    public function __construct(?ServiceContainer $serviceContainer = null)
    {
        $this->serviceContainer = $serviceContainer;
    }

    public function getLang(): string
    {
        $lang = $this->getRequest()->query->get('lang', 'ru');

        if (str_contains($lang, self::LANG_EN)) {
            return self::LANG_EN;
        }

        return self::LANG_RU;
    }

    public function get($class)
    {
        return $this->serviceContainer->get($class);
    }

    public function getEm(): EntityManager
    {
        return $this->serviceContainer->get(EntityManager::class);
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setUser($user)
    {
        if ($user) {
            $this->user = $user;
        }
    }

    /** @return BaseModel */
    public function getUser(): ?BaseModel
    {
        return $this->user;
    }

    public function response($response)
    {
        return $response;
    }
}
