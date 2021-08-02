<?php

namespace BitrixRestApi;

use BitrixRestApi\Exception as BException;
use BitrixRestApi\Jwt\JwtManagerInterface;
use BitrixRestApi\Responser\Response;
use BitrixRestApi\Responser\ResponserInterface;
use BitrixRestApi\UserManager\UserManagerInterface;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Диспетчер API запросов
 */
class Dispatcher
{
    const REGEXP_PATH = '#path="([a-zA-Z0-9./\-{}]*)"#';

    // конфигурации всех АПИ
    protected $config = null;

    protected $entityFactory;

    /** @var ResponserInterface */
    protected $responser = null;

    /** @var ParameterBag|null */
    protected $responserList = null;

    /** @var string|null  */
    protected $namespace = null;

    /** @var string|null  */
    protected $method = null;

    /** @var JwtManagerInterface|null */
    protected $jwtManager = null;

    /** @var UserManagerInterface|null */
    protected $userManager = null;

    /** @var Request */
    protected $request;

    /** @var null  */
    protected $user = null;

    public function __construct(ParameterBag $config, ApiEntityFactory $entityFactory)
    {
        $this->config = $config;
        $this->entityFactory = $entityFactory;
        $this->responserList = new ParameterBag();
    }

    public function setJwtManager(JwtManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    
    public function addResponser(string $code, ResponserInterface $responser): self
    {
        $this->responserList->set($code, $responser);
        return $this;
    }

    public function setResponser(string $code)
    {
        $this->responser = $this->responserList->get($code);
    }

    /**
     * Обработка запроса $request и вызов соответствующего метода API
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function execute(Request $request): void
    {
        $this->request = $request;

        if ($this->request->getMethod() == Request::METHOD_OPTIONS) {
            $result = ['code' => 204];
            $this->response($result);
        }

        // рабираем строку запроса, вытаскиваем все подробности запроса
        try {
            $this->request = $this->parseRequest();
        } catch (BException\NotAuthorizedException $e) {
            $this->response(new Response\Security\TokenErrorResponse());
        }

        try {
            $object = $this->entityFactory->create($this->namespace);
        } catch (BException\ApiNotFoundException $e) {
            $this->response(new Response\ApiNotFoundErrorResponse());
        }

        if (!method_exists($object, $this->method)) {
            $this->response(new Response\MethodNotFoundErrorResponse());
        }

        try {
            $object->setRequest($this->request);
            $object->setUser($this->user);
            $result = call_user_func([$object, $this->method], $this->request);
        } catch (\Throwable $e) {
            $response = new Response\SystemErrorResponse();
            $response->message = $e->getMessage();
            $response->setTrace($e->getTraceAsString());
            $this->response($response);
        }

        $this->response($result);
    }

    private function response($result): void
    {
        if (is_object($result) && method_exists($result, 'jsonSerialize')) {
            $result = $result->jsonSerialize();
        }

        $this->responser->send((array)$result);
        die();
    }

    /**
     * Разбираем строку запроса на параметры
     * @param Request $request
     * @throws Exception
     */
    private function parseRequest()
    {
        if (in_array($this->request->getMethod(), [
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_DELETE,
            Request::METHOD_PATCH,
        ])) {
            $json = json_decode($this->request->getContent(), true);
            foreach ($json as $key => $item) {
                $this->request->request->set($key, $item);
            }
        }

        $factory = DocBlockFactory::createInstance();

        foreach ($this->config->getIterator() as $className => $item) {
            $rClass = new ReflectionClass($className);

            foreach ($this->getMethodsWithDocs($rClass) as $method) {
                /** @var DocBlock $docblock */
                $docblock = $factory->create($rClass->getMethod($method));

                $tags = $docblock->getTagsByName('OA\\' . ucfirst(strtolower($this->request->getMethod())));

                if ($this->parseTags($tags)) {
                    $this->namespace = $className;
                    $this->method = $method;
                }
            }
        }

        return $this->request;
    }

    private function parseTags($tags)
    {
        $path = $this->request->getPathInfo();
        $path = rtrim($path, '/');

        $needAuth = false;
        foreach ($tags as $tag) {
            if (preg_match("#SecurityScheme#", (string)$tag->getDescription(), $m)) {
                $needAuth = true;
            }

            if (preg_match(self::REGEXP_PATH, (string)$tag->getDescription(), $m)) {
                $parsePath = explode('/', $m[1]);
                $realPath = explode('/', $path);
                $parsePathElements = array_filter($parsePath, function ($value) {
                    return !empty($value) && $value != '/';
                });
                $realPathElements = array_filter($realPath, function ($value) {
                    return !empty($value) && $value != '/';
                });
                $parsePath = implode('/', $parsePath);
                $realPath = implode('/', $realPath);

                if ($parsePath == $realPath) {
                    $equal = true;
                } elseif(strpos($parsePath, '{')) {
                    $equal = true;
                    foreach ($parsePathElements as $parsePathKey => $parsePathItem) {
                        if ($parsePathItem != $realPathElements[$parsePathKey] && $parsePathItem[0] != '{') {
                            $equal = false;
                        }
                    }

                    if ($equal) {

                        foreach ($parsePathElements as $parsePathKey => $parsePathItem) {
                            if ($parsePathItem[0] == '{') {
                                $paramName = substr($parsePathItem, 1, strlen($parsePathItem) - 2);
                                $this->request->request->set($paramName, $realPathElements[$parsePathKey]);
                                $this->request->query->set($paramName, $realPathElements[$parsePathKey]);
                            }
                        }
                    }
                }

                if (!$equal) {
                    continue;
                }

                if ($needAuth) {
                    $this->validateTokenRequest();
                }

                return true;
            }
        }
    }

    private function getMethodsWithDocs($rClass): array
    {
        $methods = [];

        foreach ($rClass->getMethods() as $method) {
            $docComment = $rClass->getMethod($method->getName())->getDocComment();
            if (!preg_match(self::REGEXP_PATH, $docComment)) {
                continue;
            }
            $methods[] = $method->getName();
        }

        return $methods;
    }

    private function validateTokenRequest()
    {
        if (!$this->jwtManager) {
            throw new Exception('jwtManager not exist');
        }

        $token = $this->jwtManager->getTokenFromRequest($this->request);
        if (!$this->jwtManager->validate($token)) {
            throw new BException\NotAuthorizedException();
        }

        $userId = $this->jwtManager->getUserIdByToken($token);
        $user = $this->userManager->getUserById($userId);
        if (!$user) {
            throw new BException\NotAuthorizedException();
        }

        $this->user = $user;
    }
}
