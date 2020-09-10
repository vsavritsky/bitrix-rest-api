<?php

namespace BitrixRestApi;

use BitrixRestApi\Responser\ResponserInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Диспетчер API запросов
 */
class Dispatcher
{
    const E_INCORRECT_REQUEST = 'Incorrect API request';
    const E_UNKNOWN_METHOD = 'Unknown API method';
    
    const REGEXP_PATH = '#path="([a-zA-Z0-9./-]*)"#';
    
    // конфигурации всех АПИ
    private $config = null;
    
    // дефолтное значение
    private $defaultConfig = [
        'format' => null,
    ];
    
    private $entityFactory;
    
    // форматирующие вывод объекты
    private $responser = [];
    
    // параметры текущего обрабатываемого запроса АПИ
    private $apiConfig = null;
    
    private $namespace = null;
    
    private $method = null;
    
    /** @var ParameterBag|null */
    private $params = null;
    
    private $files = [];
    
    /** @var ParameterBag|null */
    private $behavior = null;
    
    public function __construct(ParameterBag $config, ApiEntityFactory $entityFactory, ParameterBag $behaviorConfig = null)
    {
        $this->config = $config;
        $this->entityFactory = $entityFactory;
        $this->behaviorConfig = $behaviorConfig;
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
     * @throws \Exception
     */
    public function execute(Request $request)
    {
        if ($request->getMethod() == Request::METHOD_OPTIONS) {
            $result = ['code' => 204];
            if (isset($this->responser[$this->apiConfig['format']])) {
                $this->responser[$this->apiConfig['format']]->send($result);
            }
            
            return $result;
        }
        
        // рабираем строку запроса, вытаскиваем все подробности запроса
        $this->parseRequest($request);
        
        // формируем имя класса
        $className = str_replace('/', '\\', $this->namespace);
        
        $object = $this->entityFactory->create($className);
        
        if (!method_exists($object, $this->method)) {
            throw new \Exception(self::E_UNKNOWN_METHOD);
        }
        
        try {
            $result = call_user_func(array($object, $this->method), $this->params, $this->files);
            $error = false;
        } catch (\Exception $e) {
            $error = $e->getCode();
        }
         
        $this->fetchConfig($this->namespace);
        
        // если есть соответствующий респонсер - вызываем его
        if (isset($this->responser[$this->apiConfig['format']])) {
            $this->responser[$this->apiConfig['format']]->send((array)$result);
        }
        
        return $result;
    }
    
    /**
     * Разбираем строку запроса на параметры
     * @param Request $request
     * @throws \Exception
     */
    private function parseRequest(Request $request)
    {
        $path = $request->getPathInfo();
        
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $json = json_decode($request->getContent(), true);
            if ($json) {
                $this->params = $json;
            } else {
                $this->params = $request->request->all();
            }

            foreach ($_FILES as $key => $file) {
				if (is_array($_FILES[$key]['name'])) {
					$this->files[$key] = $this->normalizeFiles($_FILES[$key]);
				} else {
					$this->files[$key] = $_FILES[$key];
				}
            }
        } else {
            $this->params = $request->query->all();
        }
    
        $path = rtrim($path, '/');
        $this->method = explode('/', $path);
        $this->method = end($this->method);

        $factory = DocBlockFactory::createInstance();
        
        foreach ($this->config->getIterator() as $className => $item) {
            $rClass = new \ReflectionClass($className);
            
            if ($rClass->hasMethod($this->method)) {
                /** @var \phpDocumentor\Reflection\DocBlock $docblock */
                $docblock = $factory->create($rClass->getMethod($this->method));
                $tags = $docblock->getTagsByName('OA\\' . ucfirst(strtolower($request->getMethod())));
                
                foreach ($tags as $tag) {
                    if (preg_match(self::REGEXP_PATH, (string)$tag->getDescription(), $m)) {
                        if ($m[1] == $path || substr($m[1], 0, -1) == $path) {
                            $this->namespace = $className;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * выбор конфига по неймспейсу
     * @param string $namespace
     */
    private function fetchConfig($namespace)
    {
        $this->apiConfig = $this->config->get($namespace);
    }
    
    private function normalizeFiles($files)
    {
        $result = [];
        $filesCount = count($files['name']);
        $filesKeys = array_keys($files);

		for ($i = 0; $i < $filesCount; $i++) {
			foreach ($filesKeys as $key) {
				$result[$i][$key] = $files[$key][$i];
			}
        }
        
        return $result;
    }
}
