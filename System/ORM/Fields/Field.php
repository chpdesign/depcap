<?php
namespace ComposerPack\System\ORM\Fields;

use ComposerPack\System\ORM;

abstract class Field {

    /**
     * @var ORM
     */
    protected $model = null;

    protected $column = null;

    protected $nullable = false;

    protected $value = null;

    protected $foreigns = null;

    private $loaded = false;

    private $modified = false;

    /**
     * Field constructor.
     * @param ORM $model
     * @param string $column
     * @param string $foreigns
     * @param boolean $nullable
     * @param string $value
     */
    public function __construct($model, $column, $foreigns, $nullable = false, $value = null)
    {
        $this->model = $model;
        $this->foreigns = $foreigns;
        $this->column = $column;
        $this->nullable = $nullable;
        $this->value = $value;
    }

    public function fromValue($value)
    {
        $class = get_called_class();
        $class = new $class($this->model, $this->column, $this->foreigns, $this->nullable, $value);
        $class->value = $class->read();
        return $class;
    }

    public function setValue($value)
    {
        $this->modified = true;
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public final function isLoaded()
    {
        return $this->loaded;
    }

    public final function isModified()
    {
        return $this->modified;
    }

    public final function load()
    {
        $this->modified = true;
        if($this->loaded == true)
            return $this->value;
        $this->loaded = true;
        $this->value = $this->read();
        return $this->value;
    }

    public final function save($save = true)
    {
        if($this->loaded === false) {
            $this->loaded = true;
            $this->value = $this->read();
        }
        return $this->write($save);
    }

    public final function toSqlParam($value)
    {
        if(is_null($value) && $this->nullable === true)
        {
            return 'NULL';
        }
        elseif(!is_numeric($value) && empty($value) && $this->nullable === true)
        {
            return 'NULL';
        }
        elseif(is_numeric($value))
        {
            return "'".$value."'";
        }
        elseif(is_null($value) && $this->nullable !== true)
        {
            if(is_null($this->nullable))
                return 'NULL';
            return $this->toSqlParam($this->nullable);
        }
        elseif(can_be_string($value))
        {
            return "'".$value."'";
        }
    }

    public abstract function column();

    public abstract function read();

    public abstract function write($save = true);
}