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
    const E_INCORRECT_REQUEST = 'Incorrect API request';
    const E_UNKNOWN_METHOD = 'Unknown API method';
    
    const REGEXP_PATH = '#path="([a-zA-Z0-9./-{}]*)"#';
    
    // конфигурации всех АПИ
    protected $config = null;
    
    // дефолтное значение
    protected $defaultConfig = [
        'format' => null,
    ];
    
    protected $entityFactory;
    
    // форматирующие вывод объекты
    protected $responser = [];
    
    // параметры текущего обрабатываемого запроса АПИ
    protected $apiConfig = null;
    
    protected $namespace = null;
    
    /** @var string|null */
    protected $method = null;
    
    /** @var JwtManagerInterface|null */
    protected $jwtManager = null;
    
    /** @var UserManagerInterface|null */
    protected $userManager = null;
    
    /** @var ParameterBag|null */
    private $behaviorConfig = null;
    
    protected $user = null;
    
    public function __construct(ParameterBag $config, ApiEntityFactory $entityFactory, ParameterBag $behaviorConfig = null)
    {
        $this->config = $config;
        $this->entityFactory = $entityFactory;
        $this->behaviorConfig = $behaviorConfig;
    }
    
    public function setJwtManager(JwtManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }
    
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    
    /**
     *
     * @param string $code код респонсера
     * @param ResponserInterface $responser
     * @return Dispatcher
     */
    public function addResponser($code, ResponserInterface $responser)
    {
        $this->responser[$code] = $responser;
        return $this;
    }
    
    /**
     * Обработка запроса $request и вызов соответствующего метода API
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function execute(Request $request)
    {
        if ($request->getMethod() == Request::METHOD_OPTIONS) {
            $result = ['code' => 204];
            $this->response($result);
            
            return $result;
        }
        
        try {
            // рабираем строку запроса, вытаскиваем все подробности запроса
            $request = $this->parseRequest($request);
        } catch (BException\NotAuthorizedException $e) {
            $this->response(new Response\Security\TokenErrorResponse());
        }
        
        $this->fetchConfig($this->namespace);
        
        // формируем имя класса
        $className = str_replace('/', '\\', $this->namespace);
        
        try {
            $object = $this->entityFactory->create($className);
        } catch (BException\ApiNotFoundException $e) {
            $this->response(new Response\ApiNotFoundErrorResponse());
        }
        
        if (!method_exists($object, $this->method)) {
            $this->response(new Response\MethodNotFoundErrorResponse());
        }
        
        try {
            $object->setRequest($request);
            $object->setUser($this->user);
            $result = call_user_func(array($object, $this->method), $request);
        } catch (\Throwable $e) {
            $response = new Response\SystemErrorResponse();
            $response->message = $e->getMessage();
            $response->setTrace($e->getTraceAsString());
            $this->response($response);
        }
        
        $this->response($result);
    }
    
    private function response($result)
    {
        // если есть соответствующий респонсер - вызываем его
        if (isset($this->responser[$this->apiConfig['format']])) {
            $this->responser[$this->apiConfig['format']]->send((array)$result);
        } else {
            $responser = reset($this->responser);
            $responser->send((array)$result);
        }
        die();
    }
    
    /**
     * Разбираем строку запроса на параметры
     * @param Request $request
     * @throws Exception
     */
    private function parseRequest(Request $request)
    {
        $path = $request->getPathInfo();
        
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $json = json_decode($request->getContent(), true);
            
            if ($json) {
                foreach ($json as $key => $item) {
                    $request->request->set($key, $item);
                }
            }
        }
        
        $path = rtrim($path, '/');
        $this->method = explode('/', $path);
        $this->method = end($this->method);
        
        $factory = DocBlockFactory::createInstance();
        
        foreach ($this->config->getIterator() as $className => $item) {
            $rClass = new ReflectionClass($className);
            
            if ($rClass->hasMethod($this->method)) {
                /** @var DocBlock $docblock */
                $docblock = $factory->create($rClass->getMethod($this->method));
                
                $tags = $docblock->getTagsByName('OA\\' . ucfirst(strtolower($request->getMethod())));
                
                $needAuth = false;
                foreach ($tags as $tag) {
                    if (preg_match("#@Security#", (string)$tag->getDescription(), $m)) {
                        $needAuth = true;
                    }
                    
                    if (preg_match(self::REGEXP_PATH, (string)$tag->getDescription(), $m)) {
                        $parsePath = explode('/', $m[1]);
                        $realPath = explode('/', $path);
                        array_filter($parsePath, function ($value) {
                            return empty($value) || $value == '/';
                        });
                        array_filter($realPath, function ($value) {
                            return empty($value) || $value == '/';
                        });
                        
                        $equal = true;
                        foreach ($parsePath as $parsePathKey => $parsePathItem) {
                            if ($parsePathItem != $realPath[$parsePathKey] && $parsePathItem[0] != '{') {
                                $equal = false;
                            }
                            
                            if ($parsePathItem[0] == '{') {
                                $paramName = substr($parsePathItem, 1, strlen($parsePathItem) - 2);
                                
                                $request->request->set($paramName, $realPath[$parsePathKey]);
                                $request->query->set($paramName, $realPath[$parsePathKey]);
                            }
                        }
                        
                        if ($equal) {
                            $this->namespace = $className;
    
                            if ($needAuth) {
                                $this->validateTokenRequest($request);
                            }
                        }
                    }
                }
                
            }
        }
        
        return $request;
    }
    
    private function validateTokenRequest(Request $request)
    {
        if (!$this->jwtManager) {
            throw new Exception('jwtManager not exist');
        }
    
        $token = $this->jwtManager->getTokenFromRequest($request);
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
    
    /**
     * выбор конфига по неймспейсу
     * @param string $namespace
     */
    private
    function fetchConfig($namespace)
    {
        $this->apiConfig = $this->config->get($namespace);
    }
}
