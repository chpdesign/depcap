<?php
namespace ComposerPack\System\ORM\Table;

use ComposerPack\System\ORM\Types\Type;
use ComposerPack\System\Sql;

class Table implements \ArrayAccess, \Iterator
{

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $fieldIds = [];

    /**
     * @var array
     */
    protected $sqlParams = [
        "SELECT" => [],
        "FROM" => "",
        "JOIN" => [],
        "WHERE" => [],
    ];

    /**
     * @var Sql
     */
    protected $db = null;

    protected $name = "";

    public function name($name = null)
    {
        if(is_null($name))
        {
            return $this->name;
        }
        else
        {
            //unset(self::$tables[$this->name]);
            $this->name = $name;
            //self::$tables[$this->name] = $this;
            //return $this;
        }
    }

    protected $alias = null;

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

    /**
     * @var array
     */
    protected static $tables = [];

    public static function getTable($name, $new = true)
    {
        if($new == true && !isset(self::$tables[$name]))
            self::$tables[$name] = new Table($name);
        if(isset(self::$tables[$name]))
            return self::$tables[$name];
        return null;
    }

    public function __construct($table, array $fields = [], Sql $db = null)
    {
        if (is_null($db))
        {
            $this->db = Sql::getDefaultDb();
        }
        else
        {
            $this->db = $db;
        }
        if (!empty($table)) {
            $this->sqlParams["FROM"] = $table;
        } else {
            throw new \Exception('$table cannot be empty!');
        }

        $from = $this->sqlParams["FROM"];
        if(is_array($from))
            $from = reset($from);

        //$this->fields = $fields;

        $this->sqlParams["SELECT"] = [];

        $index = 0;
        foreach ($fields as $key => $field) {
            $columnName = $field->column();
            $alias = $fields[$key]->alias();
            if(!is_numeric($key))
            {
                if (empty($columnName))
                {
                    $column = trim($key);
                    $column = str_replace("`", "", $column);
                    $column = preg_split('/(\sas\s|\s)/imu', $column);
                    if (count($column) == 2 && !empty($column[0]) && !empty($column[1])) {
                        $column[0] = explode(".", $column[0]);
                        $fields[$key]->column(array_pop($column[0]));
                        $table = $fields[$key]->table();
                        if (count($column[0]) > 0 && !empty($column[0][0]) && empty($table)) {
                            $fields[$key]->table($column[0][0]);
                        }
                        $fields[$key]->alias($column[1]);
                        $columnName = $column[1];
                    } else {
                        $fields[$key]->column($column[0]);
                        $columnName = $column[0];
                    }
                }
                else
                {
                    if(empty($alias))
                    {
                        $fields[$key]->alias($key);
                    }
                }
            }

            if($field->sql()) {
                $_key = $field->key();
                if (!isset($this->sqlParams["SELECT"][$_key])) {
                    $_from = $from;
                    if(!is_null($field->table())) {
                        $_from = $field->table()->from();
                        if($from != $_from) {
                            $sqlParams["JOIN"][$_key] = "
                                LEFT JOIN
                                    `".$_from."`
                                ON
                            ";
                            if(is_array($field->on()))
                            {
                                foreach($field->on() as $m => $v) {
                                    $sqlParams["JOIN"][$_key] .= "
                                        `" . $from . "`.`" . $m . "` = `" . $_from . "`.`" . $v . "`
                                    ";
                                }
                            }
                            else
                            {
                                $sqlParams["JOIN"][$_key] .= "
                                    `".$from."`.`".$key."` = `".$_from."`.`".$field->on()."`
                                ";
                            }
                        }

                    }
                    if (empty($alias)) {
                        $this->sqlParams["SELECT"][$_key] = "`" . $_from . "`.`" . $columnName . "`";
                    } else {
                        $this->sqlParams["SELECT"][$_key] = "`" . $_from . "`.`" . $columnName . "` AS `" . $field->alias() . "`";
                    }
                }
            }

            $table = $fields[$key]->table();
            if(empty($table)) {
                $fields[$key]->table($this);
            }
            /**
             * @var $field Type
             */
            if ($fields[$key]->sortBlock() == 0) {
                $fields[$key]->sortBlock($index);
            }
            if ($fields[$key]->sortList() == 0) {
                $fields[$key]->sortList($index);
            }
            $index++;
            $this->fields[$key] = $field;
        }

        /*foreach ($this->fields as $id => $field) {
            if ($field->id()) {
                $this->fieldIds[$id] = $id;
            }
        }*/

        //[/*"`".implode("`\n,`", array_keys($this->fields))."`"*/];
        /*$_sqlColumns = $this->db->fetch_all_array("SHOW COLUMNS FROM " . $from . ";");
        foreach ($_sqlColumns as $column) {
            $this->sqlParams["SELECT"][$column['Field']] = "`" . $from . "`.`" . $column['Field'] . "`";
            if(isset($this->fields[$column['Field']]))
                $this->fields[$column['Field']]->null($column['Null'] === "YES");
        }*/

        foreach ($this->fields as $key => $field) {
            $this->sqlParams = $field->sqlConnection($this->sqlParams, $this);
        }


        foreach ($this->fields as $key => $field) {
            $w = $field->whereFilter();
            if (!empty($w))
                $this->sqlParams["WHERE"][] = "(" . $w . ")";
        }

        self::$tables[$from] = $this;
    }

    public function getRealFields()
    {
        $fields = [];
        $from = $this->from();
        if(is_array($from))
            $from = reset($from);
        $_sqlColumns = $this->db->columns($from);
        foreach ($_sqlColumns as $column) {
            if(isset($this->fields[$column]))
                $fields[$column] = $this->fields[$column];
        }
        return $fields;
    }

    public function offset($offset)
    {
        $this->sqlParams['OFFSET'] = (int)$offset;
        return $this;
    }

    public function limit($limit)
    {
        $this->sqlParams['LIMIT'] = (int)$limit;
        return $this;
    }

    public function where($where)
    {
        $_where = trim($where);
        if(!empty($_where))
            $this->sqlParams['WHERE'][] = $where;
        return $this;
    }

    public function having($having)
    {
        $_having = trim($having);
        if(!empty($_having))
            $this->sqlParams['HAVING'][] = $having;
        return $this;
    }

    public function groupBy($groupBy)
    {
        if (is_string($groupBy) && !array_key_exists($groupBy, $this->fields))
            throw new \Exception('Group by (' . $groupBy . ') not exists in field list!');
        if (is_array($groupBy) && self::isAssoc($groupBy) && count(array_intersect(array_keys($groupBy), array_keys($this->fields))) != count($groupBy))
            throw new \Exception('Group by contains key that is not exists in field list!');
        $this->sqlParams['GROUP BY'] = $groupBy;
        return $this;
    }

    public function orderBy($orderBy, $order = "DESC")
    {
        if(is_array($orderBy)) {
            $this->sqlParams['ORDER BY'] = implode(" ", $orderBy);
        }
        else {
            if (!array_key_exists($orderBy, $this->fields))
                throw new \Exception('No ' . $orderBy . ' key in field list!');
            $this->sqlParams['ORDER BY'] = $orderBy . " " . $order;
        }
        return $this;
    }

    public function count()
    {
        $count = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS `count` FROM (" . self::__sql_generate_static($this->sqlParams) . ") AS `table`"));
        return (int) $count['count'];
    }

    public function result()
    {
        $results = [];
        $query = $this->db->query(self::__sql_generate_static($this->sqlParams));
        while ($result = $this->db->fetch_assoc($query))
        {
            $results[] = $result;
        }
        return $results;
    }

    public function first($dummyIfNotExists = false)
    {
        $result = $this->db->fetch_assoc($this->db->query(self::__sql_generate_static($this->sqlParams)));
        if(empty($result) && $dummyIfNotExists === true)
            return $this->dummy();
        return $result;
    }

    public function delete()
    {
        /*foreach ($this->fields as $key => $field) {
            if ($field->translate()) {
                if (!is_null($model[$key])) {
                    $this->db->delete("lang", "`id`='" . $model[$key] . "'");
                }
            }
        }*/
        $from = $this->sqlParams['FROM'];
        if (is_array($from))
            $from = reset($from);
        if (isset($this->sqlParams['WHERE']) && !empty($this->sqlParams['WHERE'])) {
            $this->db->query(self::__sql_generate_static(['DELETE' => true, 'FROM' => $from, 'WHERE' => $this->sqlParams['WHERE']]));
        }
        return $this;
    }

    public function dummy(array $data = [])
    {
        $result = [];
        foreach($this->fields as $key => $field)
        {
            /**
             * @var $field Type
             */
            if(empty($result[$key])) {
                $result[$key] = $field->defaultValue($data);
            }
        }
        return $result;
    }

    public function parse(array $model, array $data = [])
    {
        $result = $model;
        foreach($this->fields as $key => $field)
        {
            /**
             * @var $field Type
             */
            $result[$key] = $field->processData($result, $data, $this);
        }
        return $result;
    }

    public function sortList()
    {
        $_fields = array_merge($this->fields);
        uasort($_fields, function ($a, $b) {
            /**
             * @var $a Type
             */
            $sorta = $a->sortList();
            /**
             * @var $b Type
             */
            $sortb = $b->sortList();
            return $sorta - $sortb;
        });
        return $_fields;
    }

    public function sortBlock()
    {
        $_fields = array_merge($this->fields);
        uasort($_fields, function ($a, $b) {
            /**
             * @var $a Type
             */
            $sorta = $a->sortBlock();
            /**
             * @var $b Type
             */
            $sortb = $b->sortBlock();
            return $sorta - $sortb;
        });
        return $_fields;
    }

    /**
     * @param null $from
     * @return $this|mixed
     */
    public function from($from = null)
    {;
        if(is_null($from))
        {
            return $this->sqlParams['FROM'];
        }
        else
        {
            $this->sqlParams['FROM'] = $from;
            return $this;
        }
    }

    public function __toString()
    {
        return self::__sql_generate_static($this->sqlParams);
    }


    /**
     * Az $__sql_select változóban lévő adatok alapján SQL-t generál
     */
    public static function __sql_generate_static($sql_select = null)
    {
        $sql = array();
        //
        // SELECT
        //
        //if (empty($sql_select['SELECT']))
        //{
        //	$sql_select['SELECT'] = array();
        //	$columns = $this->getColumnNames();
        //	foreach($columns as $column)
        //	{
        //		$sql_select['SELECT'][] = '`'.$this->table().'`.`'.$column.'`';
        //	}
        //	$sql_select['SELECT'] = implode(",\n", $sql_select['SELECT']);/*'`'.$this->table().'`.*';*/
        //}
        if(empty($sql_select['DELETE']))
        {
            if (is_string($sql_select['SELECT'])) $sql[] = 'SELECT '.$sql_select['SELECT'];
            if (is_array($sql_select['SELECT'])) $sql[] = "SELECT \n".implode(",\n", $sql_select['SELECT']);
        }
        else
        {
            if ($sql_select['DELETE']) $sql[] = 'DELETE';
        }
        //else $sql[] ='SELECT '.$sql_select['SELECT'];
        //
        // FROM
        //
        //if (empty($sql_select['FROM'])) $sql[] = "\n FROM ".$this->table();
        //else $sql[] = " \n FROM ".$sql_select['FROM'];
        if(is_array($sql_select['FROM'])) {
            $sql[] = " \n FROM " . implode(" \n ", $sql_select['FROM']);
        }
        else {
            $sql[] = " \n FROM " . $sql_select['FROM'];
        }
        //
        // JOIN
        //
        //if (empty($sql_select['FROM'])) $sql[] = "\n FROM ".$this->table();
        //else $sql[] = " \n FROM ".$sql_select['FROM'];
        if(isset($sql_select['JOIN'])) {
            if (is_array($sql_select['JOIN'])) {
                $sql[] = " \n " . implode(" \n ", $sql_select['JOIN']);
            } else {
                $sql[] = " \n " . $sql_select['JOIN'];
            }
        }
        //
        // WHERE
        //
        if (!empty($sql_select['WHERE']))
        {
            if (is_string($sql_select['WHERE'])) $sql[] = 'WHERE '.$sql_select['WHERE'];
            if (is_array($sql_select['WHERE'])) $sql[] = 'WHERE '.implode(' AND ', $sql_select['WHERE']);
        }
        //
        // GROUP BY:
        //
        if (!empty($sql_select['GROUP BY']))
        {
            if (is_string($sql_select['GROUP BY'])) $sql[] = 'GROUP BY '.$sql_select['GROUP BY'];
            if (is_array($sql_select['GROUP BY'])) {
                if(self::isAssoc($sql_select['GROUP BY'])) {
                    $sql[] = 'GROUP BY ' . self::implode_with_key($sql_select['GROUP BY'], ' ', ' , ');
                }
                else {
                    $sql[] = 'GROUP BY ' . implode(' , ', $sql_select['GROUP BY']);
                }
            }
        }
        //
        // HAVING
        //
        if (!empty($sql_select['HAVING']))
        {
            if (is_string($sql_select['HAVING'])) $sql[] = 'HAVING '.$sql_select['HAVING'];
            if (is_array($sql_select['HAVING'])) $sql[] = 'HAVING '.implode(' AND ', $sql_select['HAVING']);
        }
        //
        // ORDER BY:
        //
        if (!empty($sql_select['ORDER BY']))
        {
            if (is_string($sql_select['ORDER BY'])) $sql[] = 'ORDER BY '.$sql_select['ORDER BY'];
            if (is_array($sql_select['ORDER BY'])) {
                if(self::isAssoc($sql_select['GROUP BY'])) {
                    $sql[] = 'ORDER BY ' . self::implode_with_key($sql_select['ORDER BY'], ' ', ' , ');
                }
                else {
                    $sql[] = 'ORDER BY ' . implode(' , ', $sql_select['ORDER BY']);
                }
            }
        }
        //
        // LIMIT:
        //
        if (!empty($sql_select['LIMIT'])) $sql[] ='LIMIT '.$sql_select['LIMIT'];
        //
        // OFFSET:
        //
        if (!empty($sql_select['OFFSET'])) $sql[] ='OFFSET '.$sql_select['OFFSET'];

        return implode(' ', $sql);
    }




    protected static function implode_key($glue = "", $pieces = array()) {
        $arrK = array_keys($pieces);
        return implode($glue, $arrK);
    }

    protected static function implode_with_key($assoc, $inglue = '>', $outglue = ',') {
        $return = '';

        foreach ($assoc as $tk => $tv) {
            $return .= $outglue . $tk . $inglue . $tv;
        }

        return substr($return, strlen($outglue));
    }

    protected static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }


    private $index = 0;

    public function rewind()
    {
        $this->index = 0;
    }

    public function current()
    {
        $k = array_keys($this->fields);
        $var = $this->fields[$k[$this->index]];
        return $var;
    }

    public function key()
    {
        $k = array_keys($this->fields);
        $var = $k[$this->index];
        return $var;
    }

    public function next()
    {
        $k = array_keys($this->fields);
        if (isset($k[++$this->index])) {
            $var = $this->fields[$k[$this->index]];
            return $var;
        } else {
            return false;
        }
    }

    public function valid()
    {
        $k = array_keys($this->fields);
        $var = isset($k[$this->index]);
        return $var;
    }

    public function offsetExists($index) {
        return isset($this->fields[$index]);
    }

    public function offsetGet($index) {
        return $this->fields[$index];
    }

    public function offsetSet($index, $newValue) {
        $this->fields[$index] = $newValue;
    }

    public function offsetUnset($index) {
        unset($this->fields[$index]);
    }

}