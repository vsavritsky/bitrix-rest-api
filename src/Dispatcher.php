<?php

namespace BitrixApi;

use Bitrix\Main\HttpRequest;

/**
 * Диспетчер API запросов
 */
class Dispatcher
{
    const E_INCORRECT_REQUEST = 'Incorrect API request';
    const E_UNKNOWN_METHOD = 'Unknown API method';
    
    const REGEXP_PATH = '#^([a-zA-Z\-]*)\/([\.a-zA-Z\-]*)\/([a-zA-Z\-]*)\/([a-zA-Z\-]*)#';
    const REGEXP_PATH_VERSIONING = '#^([a-zA-Z\-]*)\/([\.a-zA-Z\-]*)\/([0-9a-zA-Z\-_]*)\/([a-zA-Z\-]*)\/([a-zA-Z\-]*)#';
    
    // конфигурации всех АПИ
    private $config = array();
    // дефолтное значение
    private $defaultConfig = array(
        'versioning' => false,
        'format' => null,
    );
    
    private $entityFactory;
    
    // форматирующие вывод объекты
    private $responser = array();
    
    // параметры текущего обрабатываемого запроса АПИ
    private $apiConfig = null;
    private $namespace = null;
    private $version = null;
    private $method = null;
    private $params = [];
    private $files = [];
    private $behaviorConfig = [];
    
    public function __construct($config, ApiEntityFactory $entityFactory, $behaviorConfig = [])
    {
        $this->config = $config;
        foreach ($config as $namespace => $item) {
            if (is_array($item)) {
                $this->config[$namespace] = array_merge($this->defaultConfig, $item);
            }
        }
        $this->entityFactory = $entityFactory;
        $this->behaviorConfig = $behaviorConfig;
    }
    
    /**
     *
     * @param string $code код респонсера
     * @param \Library\Api\Responser\ResponserInterface $responser
     * @return \Library\Api\Dispatcher
     */
    public function addResponser($code, Responser\ResponserInterface $responser)
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
    public function execute(HttpRequest $request)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
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
            // в случае ошибки вызываем метод интерфейса ApiInterface::lastError()
            //$result = $object->lastError();
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
    private function parseRequest(HttpRequest $request)
    {
        $path = $request->getRequestedPage();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->params = $request->getPostList();
            $this->files = $request->getFileList()->toArray();
            if (count($this->files) > 1) {
                foreach ($this->files as $key => $file) {
                    $this->files[$key] = $this->normalizeFiles($this->files[$key]);
                }
            }
        } else {
            $this->params = $request->getQueryList();
        }
        
        $path = trim($path, '/');
        
        $path = str_replace('.', '_', $path);
        
        if (!preg_match(self::REGEXP_PATH, $path, $m) && !preg_match(self::REGEXP_PATH_VERSIONING, $path, $m)) {
            throw new \Exception(self::E_INCORRECT_REQUEST);
        }
        
        // извлекаем namespace
        $m = null;
        if (preg_match(self::REGEXP_PATH, $path, $m)) {
            $this->namespace = ucfirst($m[1]) . "\\" . ucfirst($m[2]) . "\\" . ucfirst($m[3]);
            $this->method = $m[4];
        } elseif (preg_match(self::REGEXP_PATH_VERSIONING, $path, $m)) {
            $this->namespace = ucfirst($m[1]) . "\\" . ucfirst($m[2]) . "\\" . $m[3] . "\\" . ucfirst($m[4]);
            $this->method = $m[5];
        }
    }
    
    /**
     * выбор конфига по неймспейсу
     * @param string $namespace
     */
    private function fetchConfig($namespace)
    {
        $this->apiConfig = isset($this->config[$namespace])
            ? $this->config[$namespace]
            : $this->defaultConfig;
    }
    
    private function normalizeFiles($files)
    {
        $files = [];
        $filesCount = count($files['name']);
        $filesKeys = array_keys($files);
        
        for ($i = 0; $i < $filesCount; $i++) {
            foreach ($filesKeys as $key) {
                $files[$i][$key] = $files[$key][$i];
            }
        }
        
        return $files;
    }
    
}

