<?php
namespace ComposerPack\Module;

use ComposerPack\Migration\MysqlMigration;
use ComposerPack\System\Config\MysqlConfig;
use ComposerPack\System\Modules;
use ComposerPack\System\ORM;
use ComposerPack\System\ORM\Table\Table;
use ComposerPack\System\Psr4ClassFinder;
use ComposerPack\System\Settings;

abstract class Config extends \ComposerPack\System\Config\Config
{
    protected $namespace = null;

    protected $class = null;

    protected $table = null;

    protected $moduleParent = null;

    public function __construct($config, Modules $modules)
    {
        parent::__construct($config);

        $namespace = get_called_class();
        $namespace = explode('\\', $namespace);
        $this->class = array_pop($namespace);
        $namespace = implode('\\', $namespace);
        $this->namespace = $namespace;
        $this->moduleParent = $modules;
    }

    /**
     * @param $model ORM
     * @return Table|null
     */
    public function getTable(ORM $model)
    {
        if(is_null($this->table)) {
            $fields = [];
            $defaultFieldType = 'ComposerPack\\System\\ORM\\Types\\Input';
            foreach ($model as $column => $value)
            {
                $fields[$column] = new $defaultFieldType();
            }
            if (isset($this->config['fields'])) {
                foreach ($this->config['fields'] as $column => $field) {
                    $fieldType = $defaultFieldType;
                    $configFieldType = 'ComposerPack\\System\\ORM\\Types\\' . mb_ucfirst($field['type']);
                    if (class_exists($configFieldType)) {
                        $fieldType = $configFieldType;
                    }
                    $fields[$column] = new $fieldType($field);
                }
            }
            $this->table = new Table($model->getTable(), $fields, $model->getConnection());
        }
        return $this->table;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this['name'];
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this['author'];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this['description'];
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this['icon'];
    }

    /**
     * @var Modules
     */
    protected $modules = null;

    /**
     * @return Modules
     */
    public function getModules()
    {
        if($this->modules == null) {
            $submodules = $this['modules'];
            if (empty($submodules))
                $submodules = [];

            $this->modules = new Modules(new \ComposerPack\System\Config\Config($submodules), $this->namespace);
        }
        return $this->modules;
    }

    /**
     * @return null|string
     */
    public function getController()
    {
        $controller = $this->namespace.'\\Controller';
        if(class_exists($controller))
            return $controller;
        return null;
    }

    /**
     * @return string
     */
    public function getDefaultAction()
    {
        $action = $this['action'];
        return $action;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    abstract public function getClass();

    /**
     * @return null|string
     */
    public function getTemplateDir()
    {
        $this->namespace = Psr4ClassFinder::removeNamespace($this->namespace);
        $dir = get("base_dir") . str_replace('\\', DS, $this->namespace."/Template/");
        if(file_exists($dir))
            return $dir;
        return null;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return !isset($this['visible']) || $this['visible'] === true;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return isset($this['required']) && $this['required'] == true;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $namespace = explode('\\', $this->namespace);
        $module = array_pop($namespace);
        return isset($this->moduleParent[$module]);
    }

    public function isUpdateAble()
    {
        if(!$this->isInstalled())
            return false;

    }

    protected function getDbDir()
    {
        return str_replace('\\', DS, Psr4ClassFinder::removeNamespace($this->namespace)).DS.'db'.DS;
    }

    public function install()
    {
        if($this->isInstalled())
            return;
        $group = get_class($this->getClass());
        $migration = new MysqlMigration(Settings::getConfig(), $group);
        $dir = get("base_dir") . $this->getDbDir();
        $migration->setMigration($dir);
        $migration->up();
        /**
         * @var $config MysqlConfig
         */
        $config = $this->moduleParent->getConfig();
        $config[get_base_class($this->getClass())] = [];
        $config->save();
    }

    public function update()
    {
        $group = get_class($this->getClass());
        $migration = new MysqlMigration(Settings::getConfig(), $group);
        $dir = get("base_dir") . $this->getDbDir();
        $migration->setMigration($dir);
        $migration->up();
        /**
         * @var $config MysqlConfig
         */
        $config = $this->moduleParent->getConfig();
        $config[get_base_class($this->getClass())] = [];
        $config->save();
    }

    public function uninstall()
    {
        $group = get_class($this->getClass());
        $migration = new MysqlMigration(Settings::getConfig(), $group);
        $dir = get("base_dir") . $this->getDbDir();
        $migration->setMigration($dir);
        $migration->down();
        /**
         * @var $config MysqlConfig
         */
        $config = $this->moduleParent->getConfig();
        unset($config[get_base_class($this->getClass())]);
        $config->save();
    }
}