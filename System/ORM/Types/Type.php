<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\System\ORM\Table\Table;
use ComposerPack\System\Session\Session;
use ComposerPack\System\Sql;

abstract class Type {

    /**
     * @var bool
     */
	protected $id = false;

    /**
     * @param null $id
     * @return $this|bool
     */
	public function id($id = null)
	{
		if(is_null($id))
			return $this->id;
		else
		{
			$this->id = (bool) $id;
			return $this;
		}
	}

    /**
     * @var bool
     */
    protected $column = null;

    /**
     * @param null $column
     * @return $this|bool
     */
    public function column($column = null)
    {
        if(is_null($column))
        {
            return $this->column;
        }
        else
        {
            $this->column = $column;
            return $this;
        }
    }
    
    /**
     * @var bool
     */
    protected $alias = null;

    /**
     * @param null $alias
     * @return $this|bool
     */
    public function alias($alias = null)
    {
        if(is_null($alias))
        {
            return $this->alias;
        }
        else
        {
            $this->alias = $alias;
            return $this;
        }
    }
    
    public function key()
    {
        $alias = $this->alias();
        if(!empty($alias))
            return $this->alias();
        else
            return $this->column();
    }

    /**
     * @var Table
     */
    protected $table = null;

    /**
     * @param null $table
     * @return $this|Table
     */
    public function table($table = null)
    {
        if(is_null($table))
        {
            return $this->table;
        }
        else
        {
            if(is_string($table))
                $table = Table::getTable($table);
            $this->table = $table;
            return $this;
        }
    }

    protected $on = null;

    public function on($on = null)
    {
        if(is_null($on))
        {
            return $this->on;
        }
        else
        {
            $this->on = $on;
            return $this;
        }
    }

    /**
     * @var bool
     */
	protected $sql = true;

    /**
     * @param null $sql
     * @return $this|bool
     */
	public function sql($sql = null)
	{
		if(is_null($sql))
			return $this->sql;
		else
		{
			$this->sql = (bool) $sql;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $disabled = false;

    /**
     * @param null $disabled
     * @return $this|bool
     */
	public function disabled($disabled = null)
	{
		if(is_null($disabled))
			return $this->disabled;
		else
		{
			$this->disabled = (bool) $disabled;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $required = false;

    /**
     * @param null $required
     * @return $this|bool
     */
	public function required($required = null)
	{
		if(is_null($required))
			return $this->required;
		else
		{
			$this->required = (bool) $required;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $visibleList = false;

    /**
     * @param null $visible
     * @return $this|bool
     */
	public function visibleList($visible = null){
		if(is_null($visible))
			return $this->visibleList;
		else
		{
			$this->visibleList = (bool) $visible;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $visibleBlock = false;

    /**
     * @param null $visible
     * @return $this|bool
     */
	public function visibleBlock($visible = null){
		if(is_null($visible))
			return $this->visibleBlock;
		else
		{
			$this->visibleBlock = (bool) $visible;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $searchable = false;

    /**
     * @param null $searchable
     * @return $this|bool
     */
	public function searchable($searchable = null){
		if(is_null($searchable))
			return $this->searchable;
		else
		{
			$this->searchable = (bool) $searchable;

			$field = [];
			$field['data'] = $this->data();
            $field['data']['searchable'] = $this->searchable;

            //var_dump($searchable);

            if(!isset($field['data']['searchable']) || $field['data']['searchable'] === true)
            {

                if($this instanceof Date || $this instanceof DateTime)
                {
                    if(!isset($field['data']['filter-control-placeholder']))
                        $field['data']['filter-control-placeholder'] = "";
                    if(!isset($field['data']['filter-control']))
                        $field['data']['filter-control'] = "daterangepicker";
                    if(!isset($field['data']['filter-datepicker-options']))
                        $field['data']['filter-datepicker-options'] = '{"keepOpen": true, "keepInvalid": true, "locale": {"format": "YYYY-MM-DD", "cancelLabel": "Törlés", "applyLabel": "Szűrés"}, "opens": "center", "applyClass": "btn-site" }';
                }
                else if($this instanceof Select)
                {
                    if(count($this->values()) > 0) {
                        if (!isset($field['data']))
                            $field['data'] = [];
                        //if(!isset($field['data']['filter-control-placeholder']))
                        $field['data']['filter-control-placeholder'] = "";
                        //if(!isset($field['data']['filter-control']))
                        $field['data']['filter-control'] = "select";
                        //if(empty($field['data']['filter-data']))
                        $field['data']['filter-data'] = 'json:' . json_encode($this->values());
                    }
                }

                if(!isset($field['data']['filter-control']))
                    $field['data']['filter-control'] = "input";
                if(!isset($field['data']['filter-control-placeholder']))
                    $field['data']['filter-control-placeholder'] = "";
            }
            else
            {
                if(isset($field['data']['filter-control']))
                    unset($field['data']['filter-control']);
                if(isset($field['data']['filter-control-placeholder']))
                    unset($field['data']['filter-control-placeholder']);
            }

            if($this->translate())
            {
                $field['data']['formatter'] = 'lang';
            }

            $this->data($field['data']);


			return $this;
		}
	}

    /**
     * @var array
     */
	protected $data = [];

    /**
     * @param null $data
     * @return $this|array
     */
	public function data($data = null)
	{
		if(is_null($data))
			return $this->data;
		else
		{
			$this->data = $data;
			return $this;
		}
	}

    /**
     * @var int
     */
	protected $sortBlock = 0;

    /**
     * @param null $sort
     * @return $this|int
     */
	public function sortBlock($sort = null)
	{
		if(is_null($sort))
			return $this->sortBlock;
		else
		{
			$this->sortBlock = (int) $sort;
			return $this;
		}
	}

    /**
     * @var int
     */
	protected $sortList = 0;

    /**
     * @param null $sort
     * @return $this|int
     */
	public function sortList($sort = null)
	{
		if(is_null($sort))
			return $this->sortList;
		else
		{
			$this->sortList = (int) $sort;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $sortable = true;

    /**
     * @param null $sortable
     * @return $this|bool
     */
	public function sortable($sortable = null)
	{
		if(is_null($sortable))
			return $this->sortable;
		else
		{
			$this->sortable = (bool) $sortable;
			return $this;
		}
	}

    /**
     * @var null
     */
	protected $groupBlock = null;

    /**
     * @param null $group
     * @return null|string
     */
	public function groupBlock($group = null)
    {
        if(is_null($group))
        {
            return $this->groupBlock;
        }
        else
        {
            $this->groupBlock = $group;
        }
    }

    /**
     * @var string
     */
	protected $name = "";

    /**
     * @param null $name
     * @return $this|string
     */
	public function name($name = null)
	{
		if(is_null($name))
			return $this->name;
		else
		{
			$this->name = $name;
			return $this;
		}
	}

    /**
     * @var bool
     */
	protected $translate = false;

    /**
     * @param null $translate
     * @return $this|bool
     */
	public function translate($translate = null)
	{
		if(is_null($translate))
			return $this->translate;
		else
		{
			$this->translate = $translate;
			return $this;
		}
	}

    /**
     * @var null|string
     */
	protected $help = null;

    /**
     * @param null $help
     * @return $this|null
     */
	public function help($help = null)
	{
		if(is_null($help))
			return $this->help;
		else
		{
			$this->help = $help;
			return $this;
		}
	}

    /**
     * @var Sql
     */
	protected $db = null;

    /**
     * Type constructor.
     * @param array $field
     * @param Sql $db
     */
	public function __construct(array $field = [], Sql $db = null)
	{
	    if(is_null($db))
	        $this->db = Sql::getDefaultDb();

		$this->visibleList($this->fieldVisible($field, 0, 'list'));
		$this->visibleBlock($this->fieldVisible($field, 1, 'block'));
		
		$this->sortList($this->fieldSort($field, 0, 'list'));
		$this->sortBlock($this->fieldSort($field, 1, 'block'));

		if(isset($field['sortable']))
		    $this->sortable(isset($field['sortable']) && $field['sortable'] === true);
		
		$this->name(isset($field['name']) ? $field['name'] : null);
		
		$this->translate(isset($field['translate']) && $field['translate'] === true);
		
		$this->defaultValue = isset($field['default']) ? $field['default'] : null;
		
		$this->disabled(isset($field['disabled']) && $field['disabled'] === true);
		
		$this->sql(!isset($field['sql']) || $field['sql'] === true);
		
		$this->id(isset($field['id']) && $field['id'] === true);
		
		$this->required(isset($field['required']) && $field['required'] === true);
		
		$this->help(isset($field['help']) ? $field['help'] : null);

        $this->filter(isset($field['filter']) ? $field['filter'] : null);

        $this->onAction(isset($field['action']) ? $field['action'] : null);

        $this->column(isset($field['column']) ? $field['column'] : null);

        $this->table(isset($field['table']) ? $field['table'] : null);
        $this->on(isset($field['on']) ? $field['on'] : null);


        if(!isset($field['data']))
			$field['data'] = [];
		if(!isset($field['data']['valign']))
			$field['data']['valign'] = "top";
				
		$this->data($field['data']);

        $this->searchable(!isset($field['data']['searchable']) || $field['data']['searchable'] === true);

        $this->groupBlock($this->fieldGroup($field, 0, 'list'));
	}

    /**
     * @param Type $field
     * @param int $index
     * @param string $key
     * @return int
     */
	private function fieldSort($field, $index, $key)
	{
		return isset($field['sort']) ? (is_array($field['sort']) ? (isset($field['sort'][$index]) ? $field['sort'][$index] : (isset($field['sort'][$key]) ? $field['sort'][$key] : 0) ) : $field['sort']) : 0;
	}

    /**
     * @param Type $field
     * @param int $index
     * @param string $key
     * @return string|null
     */
	private function fieldGroup($field, $index, $key)
	{
		return isset($field['group']) ? (is_array($field['group']) ? (isset($field['group'][$index]) ? $field['group'][$index] : (isset($field['group'][$key]) ? $field['group'][$key] : null) ) : $field['group']) : null;
	}

    /**
     * @param Type $field
     * @param int $index
     * @param string $key
     * @return bool
     */
	private function fieldVisible($field, $index, $key)
	{
		return
            !isset($field['visible'])
		    ||
		    $field['visible'] === true
		    ||
            (
                is_array($field['visible'])
                &&
                (
                    (isset($field['visible'][$key]) && $field['visible'][$key] == true)
                    ||
                    (isset($field['visible'][$index]) && $field['visible'][$index] == true)
                )
            );
	}

    /**
     * @param string $value
     * @return string
     */
	public function where($value, $type = null)
	{
		if($this->translate())
		{
			return "`".$this->key()."_lang_table`.`".Session::get('language')."` LIKE '%".$value."%'";
		}
		else
		{
		    $from = $this->table()->from();
		    if(is_array($from))
                $from = reset($from);

			return "`".$from."`.`".$this->key()."` LIKE '%".$value."%'";
		}
	}

    /**
     * @param $value
     * @return null
     */
	public function having($value)
    {
        return null;
    }

    /**
     * @var bool
     */
	protected $filter = false;

    /**
     * @param null $filter
     * @return bool
     */
	public function filter($filter = null)
    {
        if(is_null($filter))
        {
            return $this->filter;
        }
        else
        {
            $this->filter = $filter;
        }
    }

    /**
     * @return bool
     */
	public function whereFilter()
    {
        return $this->filter();
    }

    /**
     * @param array $model
     * @param string $formid
     * @param string $url
     * @return mixed
     */
	public abstract function blockField($model, $formid, $url);

    /**
     * @var mixed|null
     */
	protected $defaultValue = null;

    /**
     * @param array $model
     * @return mixed|null
     */
	public function defaultValue($model)
	{
	    if(!empty($model[$this->key()]))
	        return $model[$this->key()];
		return $this->defaultValue;
	}

	public function visibleValue($model)
    {
        if($this->translate() && isset($model['lang__'.$this->key()]))
        {
            return $model['lang__'.$this->key()];
        }
        if(isset($model[$this->key()]))
            return $model[$this->key()];
        return null;
    }

    /**
     * @param array $model
     * @param string $url
     * @return mixed
     */
	public function listField($model, $url){
        if($this->translate())
        {
            if(is_array($model[$this->key()]) || is_object($model[$this->key()]))
            {
                return $model[$this->key()][Session::get('language')];
            }
            return $model['lang__'.$this->key()];
        }
        if(isset($model[$this->key()]))
            return $model[$this->key()];
        return null;
    }

    /**
     * @param array $sqlParams
     * @return array
     */
	public function sqlConnection($sqlParams, $table, $type = null){
		if($this->sql())
		{
            $from = $table->from();
            if(is_array($from))
                $from = reset($from);
			if($this->translate())
			{
                if(isset($sqlParams["JOIN"])) {
                    if (!is_array($sqlParams["JOIN"]))
                        $sqlParams["JOIN"] = [$sqlParams["JOIN"]];
                }
                else
                {
                    $sqlParams["JOIN"] = [];
                }
				$sqlParams["SELECT"][$this->key()] = "`".$from."`.`".$this->key()."`";
				$sqlParams["SELECT"]["lang__".$this->key()] = "`".$this->key()."_lang_table`.`".Session::get('language')."` AS `lang__".$this->key()."`";
				$sqlParams["JOIN"][$this->key()] = "
					LEFT JOIN
						`language` AS `".$this->key()."_lang_table`
					ON
						`".$from."`.`".$this->key()."` = `".$this->key()."_lang_table`.`id`
				";
			}
			else if($this->sql())
			{
				//$sqlParams["SELECT"][$key] = "`".$from."`.`".$key."`";
			}
		}
		return $sqlParams;
	}

	public function sqlOrder($order)
    {
        if(in_array(strtolower($order), ['asc', 'desc']))
        {
            return [$this->key(), strtoupper($order)];
        }
        return false;
    }

    /**
     * @var null
     */
	private $formFieldId = null;

    /**
     * @return null|string
     */
	public function formFieldId()
	{
		if($this->formFieldId == null)
			$this->formFieldId = "field".md5(microtime());
		return $this->formFieldId;
	}

    /**
     * @param $type
     * @return null|Type
     */
	public static function getByType($type)
	{
		$class = '\Types\\'.ucfirst($type);
		if(class_exists($class))
		{
			return new $class();
		}
		return null;
	}

    /**
     * @var array
     */
	protected $actions = [];

    /**
     * @param null $actions
     * @return array
     */
	public function onAction($actions = null)
    {
        if(is_null($actions)) {
            return $this->actions;
        } else {
            $this->actions = $actions;
        }
    }


    /**
     * @param array $model
     * @return mixed
     */
    public function processData(&$model, $data, $table)
    {
        if(isset($data[$this->key()]))
            return $data[$this->key()];
        if(isset($model[$this->key()]))
            return $model[$this->key()];
        return null;
    }


    /**
     * @var bool
     */
	protected $null = true;
	
	public function null($null = null)
    {
        if(is_null($null))
        {
            return $this->null;
        }
        else
        {
            $this->null = $null;
            return $this;
        }
    }
	
	
	
	public function __call($method, $arguments)
	{
		$class_name = get_called_class();
		
		if(property_exists($this, $method))
			return $this->{$method};
		
		return $this;
		
	}
	
	/*
	 * TÖMBÖS HIVATKOZÁSHOZ SZÜKSÉGES METÓDUSOK
	 */
	
	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}
	
	
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}
	
	
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}
	
	
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}
	
	
	/*
	 * TÖMBÖS HIVATKOZÁSHOZ SZÜKSÉGES METÓDUSOK  END!!
	 */
	
	
	
	
	
}