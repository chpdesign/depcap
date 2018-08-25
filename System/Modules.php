<?php
namespace ComposerPack\System;

use ComposerPack\System\Config\Config;

class Modules extends \ArrayIterator
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var string
     */
    protected $namespace = null;

    /**
     * @var \ComposerPack\Module\Config[]
     */
    protected $usedModules = [];

    /**
     * @var \ComposerPack\Module\Config[]
     */
    protected $allModules = [];

    protected $controllers = [];

    protected static $sum = [];

    public function __construct(Config $config, $namespace = 'ComposerPack\\Module')
    {
        $this->config = $config;
        $this->namespace = $namespace;

        $thisClass = get_called_class();


        $moduleFind = $this->namespace;
        $moduleFind = Psr4ClassFinder::removeNamespace($moduleFind);
        $moduleFind = str_replace('\\', DS, $moduleFind);
        $moduleFind = get("base_dir") . $moduleFind . DS;
        foreach(scandir($moduleFind) as $moduleDir)
        {
            if(in_array($moduleDir, ['.', '..']))
                continue;
            if(is_dir($moduleFind.$moduleDir))
            {
                $config = str_replace(DS, '\\', $this->namespace . DS . $moduleDir . DS . 'Config');
                if(class_exists($config))
                {
                    $c = isset($this->config[$moduleDir]) ? $this->config[$moduleDir] : [];
                    /**
                     * @var $config \ComposerPack\Module\Config
                     */
                    $config = new $config($c, $this);
                    if($config->isRequired() && !isset($this->config[$moduleDir]))
                    {
                        $this->config[$moduleDir] = [];
                    }
                    $this->allModules[$moduleDir] = $config;
                }
            }
        }

        foreach ($this->config as $module => $config) {

            $keys = [];
            $values = [];

            $currentNamespace = $this->namespace . '\\' . $module ;
            $class = $currentNamespace . '\\Config';

            if (class_exists($class)) {

                $m = $currentNamespace.'\\'.$module;
                $values['ORM'] = $m;
                $keys[] = $currentNamespace;

                $keys[] = $class;

                /**
                 * @var $moduleConfig \ComposerPack\Module\Config
                 */
                $moduleConfig = new $class($config, $this);

                $values['Config'] = $moduleConfig;

                $controller = $moduleConfig->getController();
                if(!empty($controller))
                {
                    self::$sum[$controller] = $controller;
                    $values['Controller'] = $controller;
                    $keys[] = $controller;
                }

                $this->allModules[$module] = $moduleConfig;

                $this->usedModules[$module] = $moduleConfig;

                //$moduleConfig->getModules();
                /*foreach ($moduleConfig->getModules() as $subModule => $subConfig) {
                    $subThis = new $thisClass(new Config($subConfig), $currentNamespace);
                    //$submodules[$subModule] = $subThis;
                }*/

                foreach ($keys as $key)
                {
                    self::$sum[$key] = $values;
                }
            }
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getAllModules()
    {
        return $this->allModules;
    }

    /**
     * @param $find
     * @return array|null
     */
    public static function find($find)
    {
        if(is_object($find))
        {
            $find = get_class($find);
        }
        if(isset(self::$sum[$find]))
            return self::$sum[$find];
        return null;
    }

    public function findController($controller, Modules $modules = null)
    {
        if($modules == null)
            $modules = $this;
        foreach ($modules as $module => $config)
        {
            $moduleController = $config->getController();
            if($moduleController == $controller)
                return $controller;
            $subModules = $config->getModules();
            $subController = $this->findController($controller, $subModules);
            if(!empty($subController))
                return $subController;
        }
        return false;
    }

    public function getController(\ComposerPack\Module\Config $module)
    {
        return $module->getController();
    }

    public function getControllerByParameters($parameters, $action)
    {
        $classNamespace = [mb_ucfirst($action)];
        $url = [$action];
        foreach($parameters as $urlTag)
        {
            $classNamespace[] = mb_ucfirst($urlTag);
            $url[] = $urlTag;
        }
        $controller = null;
        $nextUrl = [];

        do{
            $controller = 'ComposerPack\\Module\\'.implode('\\', $classNamespace).'\\Controller';
            $controller = $this->findController($controller);
            if(!empty($controller))
                break;
            array_pop($classNamespace);
            $nextUrl[] = array_pop($url);
        }while(!empty($classNamespace));

        if(!empty($controller))
        {
            $action = array_pop($nextUrl);
            $nextUrl = array_reverse($nextUrl);
            $controller = new $controller($nextUrl, $action);
            return $controller;
        }
        return null;
    }

    public function getControllerUrls(Modules $modules = null)
    {
        $urls = [];
        if($modules == null)
            $modules = $this;
        foreach ($modules as $module => $config)
        {
            $moduleController = $config->getController();
            $moduleController = Psr4ClassFinder::removeNamespace($moduleController);
            if(strpos($moduleController, 'Module\\') === 0)
                $moduleController =  mb_strtolower(mb_substr($moduleController, strlen('Module\\')));
            if(!empty($moduleController) && endsWith($moduleController, '\\controller'))
                $moduleController = mb_substr($moduleController, 0, -strlen('\\controller'));
            if(!empty($moduleController))
                $urls[$module] = strtolower(str_replace('\\', '/', $moduleController));
            $subModules = $config->getModules();
            $urls = array_merge($urls, $this->getControllerUrls($subModules));
        }
        return $urls;
    }

    /**
     * SET
     * @param string $name
     * @param mixed $value
     */
    public function __set($name,$value)
    {
        $this[$name] = $value;
    }

    /**
     * GET
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if(isset($this[$name]))
            return $this[$name];
        else
            return null;
    }

    /**
     * Tag ellenörzés
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if(array_key_exists($name, $this->usedModules))
            return isset($this[$name]);
        else
            return false;
    }


    /**
     * Tag törlés
     * @param string $name
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->usedModules))
        {
            unset($this[$name]);
        }
    }

    /**
    ARRAY ------------------------------------------------
     */

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::next()
     */
    public function next(){
        return next($this->usedModules);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::rewind()
     */
    public function rewind(){
        reset($this->usedModules);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::current()
     */
    public function current(){
        return current($this->usedModules);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::key()
     */
    public function key(){
        return key($this->usedModules);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::valid()
     */
    public function valid(){
        $key = key($this->usedModules);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetGet()
     */
    public function offsetGet($name) {
        if(isset($this->usedModules[$name]))
            return $this->usedModules[$name];
        else
            return null;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetSet()
     */
    public function offsetSet($name, $value) {
        $this->usedModules[$name] = $value;

    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetExists()
     */
    public function offsetExists($name) {
        return isset($this->usedModules[$name]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetUnset()
     */
    public function offsetUnset($name) {
        unset($this->usedModules[$name]);
    }

    public function count()
    {
        return count($this->usedModules);
    }

    /**
    ARRAY ------------------------------------------------
     */
}