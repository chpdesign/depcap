<?php
namespace ComposerPack\System;
use ComposerPack\Module\Language\Language;
use ComposerPack\System\ORM\Fields\Field;

/**
 * Egy keret az alap osztályok számára mely befoglalja a tömbösítést és az sql parancsok kezelhetőségét
 * Az osztály tanulsága az osztály származtatás
 * Nem kell agyba főbe külön osztályokat készíteni elég lesz ebből leszármaztatni és kész is a csoda :)
 *
 * @author Nagy Gergely info@nagygergely.eu 2013
 * @version 0.2
 *
 */
abstract class ORM extends \ArrayIterator implements \JsonSerializable, \Serializable
{

    /**
     * Álltalában egy van de lehet több akkor egy tömb van a string helyén!
     * @var string|array
     */
    protected $primary_key = array();
    protected $id = null;
    /**
     * Akkor van rá szükség ha nem listát kérünk vagy egyéb, ennek tartalmaznia kell az eredeti kulcs érték listát ez is vagy string vagy tömb
     * @var string|array
     */
    protected $default_primary_key = array('id');

    /**
     * Plusz table.* meg az oszlopok
     * @var array
     */
    protected $columns = array();

    /**
     * Új objectum?
     * @var bool
     */
    protected $new = false;

    protected $tags = array();

    protected $calculatedTags = array();

    protected $fields = array();

    protected $connection = null;

    protected $table = null;

    public function getTable()
    {
        return $this->table;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Ideiglenes tároló a felépített SQL parancsoknak
     * @var array
     */
    public $__sql_select = array(
        'SELECT' => null,
        'FROM' => null,
        'WHERE' => null,
        'ORDER BY' => null,
        'GROUP BY' => null,
        'LIMIT' => null,
    );

    /**
     * Return the primary key array, Like: array("id" => 1) or array("url" => "asd") or more mixed array
     * @return array
     */
    public function getPrimaryKeys()
    {
        foreach($this->primary_key as &$value)
        {
            if(is_numeric($value))
            {
                $value = floatval($value);
            }
        }
        ksort($this->primary_key);
        return $this->primary_key;
    }

    /**
     *
     * @return array|string
     */
    public function getDefaultPrimaryKeys()
    {
        return $this->default_primary_key;
    }

    public function __sleep()
    {
        $this->getPrimaryKeys();
        return ["primary_key"];
    }

    public function __wakeup()
    {
        $this->init($this->primary_key);
    }

    public function serialize() {
        return json_encode($this->getPrimaryKeys());
    }

    public function unserialize($data) {
        $this->init(json_decode($data, true));
    }

    protected function init()
    {
        if(is_null($this->table))
        {
            $this->table = get_base_class($this);
            $this->table = strtolower($this->table);
        }
        if(is_null($this->connection))
        {
            $this->connection = Sql::getDefaultDb();
        }

        $args = func_get_args();
        if(count($args) == 1)
        {
            $args = $args[0];
        }
        if(!is_array($args) && count($this->default_primary_key) == 1)
        {
            $args = array($this->default_primary_key[0] => $args);
        }

        $this->tags = $args;

        $id = array();
        $this->new = false;
        if(is_array($args) && !empty($args))
        {
            if(!is_assoc_array($args))
            {
                $i = 0;
                foreach($this->default_primary_key as $key)
                {
                    if(array_key_exists($i,$args))
                    {
                        $id[$key] = $args[$i];
                    }
                    else
                    {
                        $this->new = true;
                    }
                    $i++;
                }
            }
            else
            {
                foreach($this->default_primary_key as $key)
                {
                    if(array_key_exists($key,$args))
                    {
                        $id[$key] = $args[$key];
                    }
                    else
                    {
                        $this->new = true;
                    }
                }
            }
            if($this->new == false)
            {
                foreach($this->default_primary_key as $key)
                {
                    if(!array_key_exists($key,$id))
                    {
                        $this->new = true;
                    }
                }
            }
        }
        else
        {
            $this->new = true;
        }
        if(count($id) < count($this->default_primary_key))
            $this->new = true;

        $columnSettings = $this->connection->columns($this->table, true);

        $fields = [];

        $foreigns = $this->connection->table_foreign_keys($this->table);

        foreach($columnSettings as $key => $info)
        {
            $class = 'ComposerPack\\System\\ORM\\Fields\\DefaultField';
            $type = $columnSettings[$key]['Type'];
            $type = explode("(", $type);
            $type = reset($type);
            $_class = 'ComposerPack\\System\\ORM\\Fields\\'.mb_ucfirst($type);
            if(class_exists($_class))
                $class = $_class;
            /**
             * @var $class Field
             */
            $nullable = $this->connection->nullable($this->table, $key);
            $class = new $class($this, $key, $foreigns, $nullable);
            $fields[$key] = $class;
        }

        $this->fields = $fields;

        if($this->new == false)
        {
            foreach($id as $key => $value)
            {
                $this->primary_key[$key] = $value;
            }
            $this->id = $this->id_generation();

            $this->tags = array_merge($id);
            $columns = [];

            foreach($fields as $key => $class)
            {
                $c = $class->column();
                if(is_array($c))
                {
                    $columns = array_merge($columns, $c);
                }
                else
                {
                    $columns[] = $c;
                }
            }

            if(empty($columns))
                $columns = ['`'.$this->table.'`.*'];
            if(!empty($this->__columns))
            {
                $co = $this->__columns;
                if(!is_array($columns))
                    array_unshift($co, $columns);
                $columns = array_merge($co,$columns);
            }

            $result = [];

            if($this->connection->table_exists($this->table))
            {
                $params = empty($this->primary_key) ? [] : $this->primary_key;
                $where = [];

                $selector = "";

                if(!empty($this->primary_key))
                {
                    Sql::paramsToWhere($params, $where);
                    $selector = "`".self::implode_with_key($where,"` = "," AND `")."";
                }
                if(!empty($selector))
                {
                    $sql = $this->__sql([
                        'SELECT' => $columns,
                        'FROM' => $this->table,
                        'WHERE' => $selector
                    ]);

                    $result = $this->connection->query($sql, $params);
                    $result = $this->connection->fetch_assoc($result);
                }
            }
            if(!empty($result))
            {
                $this->tags = array_merge($result, $this->tags);
            }
            else
            {
                $this->new = true;
            }
        }
        else
        {
            $this->new = true;
        }

        foreach($fields as $key => &$class)
        {
            /**
             * @var $class Field
             */
            if(isset($this->tags[$key]))
            {
                $class = $class->fromValue($this->tags[$key]);
            }
        }

        $this->tags = array_merge($this->tags, $fields);

        return $this;
    }

    /**
     * ORM constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        call_user_func_array([$this, "init"], func_get_args());
    }

    public function foreign_keys()
    {
        return $this->connection->foreign_keys($this->table);
    }

    /**
     * Az $__sql_select-ből rendes utasítás!
     * Beleírodik az osztály változóba is mint kérés!
     * @param $sql array
     * @return string
     */
    public function __sql($sql = null)
    {
        if(!is_array($sql))
        {
            $sql = $this->__sql_select;
        }

        $query = [];
        // SELECT -------------------------------------------------------------------
        if(empty($sql['SELECT']))
            $sql['SELECT'] = '`'.$this->table.'`.*';
        if(is_string($sql['SELECT']) && !strncmp($sql['SELECT'], 'DELETE', strlen('DELETE')))
            $query[] = 'DELETE ';
        else
            if(is_string($sql['SELECT']))
                $query[] = 'SELECT '.$sql['SELECT'];
            else
                if(is_array($sql['SELECT'])) {
                    if(self::is_assoc_array($sql['SELECT']))
                    {
                        $cols = [];
                        foreach($sql['SELECT'] as $as => $column)
                        {
                            if($column == $as) {
                                $cols[] = '`' . $column . '`';
                            }
                            else {
                                $cols[] = '' . $column . ' AS `' . $as . '`';
                            }
                        }
                        $query[] = 'SELECT ' . implode(', ', $cols); //implode_with_key($sql['SELECT'], ' AS ', ', ');
                    }
                    else
                    {
                        $query[] = 'SELECT ' . implode(', ', $sql['SELECT']);
                    }
                }
        // FROM ---------------------------------------------------------------------
        if(empty($sql['FROM']))
            $query[] = 'FROM `'.$this->table.'`';
        elseif(is_string($sql['FROM']))
            $query[] ='FROM '.$sql['FROM'];
        elseif(is_array($sql['FROM']))
            $query[] ='FROM '.implode(" \n ",$sql['FROM']);
        // WHERE --------------------------------------------------------------------
        if(!empty($sql['WHERE']))
        {
            if(is_string($sql['WHERE']))
                $query[] = 'WHERE '.$sql['WHERE'];
            if(is_array($sql['WHERE']))
                $query[] = 'WHERE '.implode(' AND ', $sql['WHERE']);
        }
        // GROUP BY -----------------------------------------------------------------
        if(!empty($sql['GROUP BY']))
            if(is_string($sql['GROUP BY']))
                $query[] = 'GROUP BY '.$sql['GROUP BY'];
            else
                if(is_array($sql['GROUP BY']))
                    $query[] = 'GROUP BY '.implode(', ',$sql['GROUP BY']);
        // HAVING --------------------------------------------------------------------
        if(!empty($sql['HAVING']))
        {
            if(is_string($sql['HAVING']))
                $query[] = 'HAVING '.$sql['HAVING'];
            if(is_array($sql['WHERE']))
                $query[] = 'HAVING '.implode(' AND ', $sql['HAVING']);
        }
        // ORDER BY -----------------------------------------------------------------
        if(!empty($sql['ORDER BY']))
            if(is_string($sql['ORDER BY']))
                $query[] = 'ORDER BY '.$sql['ORDER BY'];
            else
                if(is_array($sql['ORDER BY']))
                    $query[] = 'ORDER BY '.implode(', ',$sql['ORDER BY']);
        // LIMIT --------------------------------------------------------------------
        if(!empty($sql['LIMIT']))
            $query[] = ' '.$sql['LIMIT'];

        return implode(" \n ", $query);
    }

    /**
     * Eredmény összesítés
     */
    public function count()
    {
        // az eredeti query megörzése! --------------------------------------------
        $old = $this->__sql_select['SELECT'];
        // count készítés! --------------------------------------------------------
        $this->__sql_select['SELECT'] = 'count(*) AS `count`';
        // sql készítés -----------------------------------------------------------
        $sql = $this->__sql();
        $result = $this->connection->query($sql);
        $result = $this->connection->fetch_assoc($result);
        // vissza a régit! --------------------------------------------------------
        $this->__sql_select['SELECT'] = $old;
        return (int) $result['count'];
    }

    /**
     * Eredmény összesítés
     */
    public function sum($column = '')
    {
        if(empty($column)) return 0;
        // az eredeti query megörzése! --------------------------------------------
        $old = $this->__sql_select['SELECT'];
        // count készítés! --------------------------------------------------------
        $this->__sql_select['SELECT'] = 'sum(`'.$column.'`) AS `sum`';
        // sql készítés -----------------------------------------------------------
        $sql = $this->__sql();
        $result = $this->connection->query($sql);
        $result = $this->connection->fetch_assoc($result);
        // vissza a régit! --------------------------------------------------------
        $this->__sql_select['SELECT'] = $old;
        return (int) $result['sum'];
    }

    public function search($text = null)
    {
        $names = $this->connection->columns($this->table);
        if(is_bool($names)) return $this;
        $names = array_values($names);
        $query = array();
        foreach($names as $name)
        {
            $query[] = "`".$this->table."`.`".$name."` LIKE '%".$text."%'";
        }
        foreach($this->connection->table_foreign_keys($this->table) as $column => $foreign)
        {
            if(!is_array($this->__sql_select['FROM']))
            {
                $this->__sql_select['FROM'] = array($this->table."" => "`".$this->table."`");
            }
            if(isset($this->__sql_select['FROM'][$foreign['table']]))
            {
                $i = '';
                while(isset($this->__sql_select['FROM'][$foreign['table'].$column.$i]))
                {
                    $i = ((int) $i)+1;
                }
                $ftable = $foreign['table'].$column.$i;
                $this->__sql_select['FROM'][$ftable] = "LEFT JOIN `".$foreign['table']."` AS `".$ftable."` ON `".$this->table."`.`".$column."` = `".$ftable."`.`".$foreign['column']."`";
            }
            else
            {
                $ftable = $foreign['table'];
                $this->__sql_select['FROM'][$foreign['table']] = "LEFT JOIN `".$foreign['table']."` ON `".$this->table."`.`".$column."` = `".$foreign['table']."`.`".$foreign['column']."`";
            }
            //$query[] = "`".$foreign['table']."`.`".$foreign['column']."` LIKE '%".$text."%'";

            $names = $this->connection->columns($foreign['table']);
            if(is_bool($names)) return $this;
            $names = array_values($names);
            foreach($names as $name)
            {
                $query[] = "`".$ftable."`.`".$name."` LIKE '%".$text."%'";
            }

        }
        $this->__sql_select['WHERE'][] = "( ".implode(" OR ", $query)." )";
        return $this;
    }

    protected function joiner($foreign_table, $foreign_column, $column, $type)
    {
        if(!is_array($this->__sql_select['FROM']))
        {
            $this->__sql_select['FROM'] = array($this->table."" => "`".$this->table."`");
        }
        $this->__sql_select['FROM'][$foreign_table.$foreign_column.$column] = $type." JOIN `".$foreign_table."` ON `".$this->table."`.`".$column."` = `".$foreign_table."`.`".$foreign_column."`";
    }

    public function join($column, $type = "")
    {
        $db = $this->connection;
        $columns = $db->foreign_keys($this->table);
        if(isset($columns[$column])) {
            $modelClasses = Psr4ClassFinder::getClassesInNamespace('ComposerPack\\Model');
            foreach ($modelClasses as $modelClass) {
                /**
                 * @var $model ORM
                 */
                $model = new $modelClass();
                if($columns[$column]['table'] == $model->table)
                {
                    $this->joiner($columns[$column]['table'], $columns[$column]['column'], $column, $type);
                }
            }
        }
        return $this;
    }

    public function left_join($column = null)
    {
        $this->join($column, "LEFT");
        return $this;
    }

    public function right_join($column = null)
    {
        $this->join($column, "RIGHT");
        return $this;
    }

    public function where($field,$value = null)
    {
        if(is_array($field))
        {
            foreach($field as $key => $val)
            {
                $this->where($key, $val);
            }
        }
        else
        {
            if(func_num_args() > 1)
            {
                if(is_null($value))
                    $value = "IS NULL";
                else
                    $value = "= '".$value."'";
                $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
            }
            else
            {
                $this->__sql_select['WHERE'][] = $field;
            }
        }
        return $this;
    }

    public function having($field,$value = null)
    {
        if(is_array($field))
        {
            foreach($field as $key => $val)
            {
                $this->having($key, $val);
            }
        }
        else
        {
            if(func_num_args() > 1)
            {
                if(is_null($value))
                    $value = "IS NULL";
                else
                    $value = "= '".$value."'";
                $this->__sql_select['HAVING'][] = "`".$this->table."`.`".$field."` ".$value."";
            }
            else
            {
                $this->__sql_select['HAVING'][] = $field;
            }
        }
        return $this;
    }

    public function where_regexp($field,$value)
    {
        if(is_null($value))
            $value = "IS NULL";
        else
            $value = "REGEXP '".$value."'";
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
        return $this;
    }

    public function where_like($field,$value)
    {
        if(is_null($value))
            $value = "IS NULL";
        else
            $value = "LIKE '%".$value."%'";
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
        return $this;
    }

    public function where_not($field,$value)
    {
        if(is_null($value))
            $value = "IS NOT NULL";
        else
            $value = "!= '".$value."'";
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
        return $this;
    }

    public function where_lower($field,$value)
    {
        if(is_null($value))
            $value = "IS NOT NULL";
        else
            $value = "< '".$value."'";
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
        return $this;
    }

    public function where_higher($field,$value)
    {
        if(is_null($value))
            $value = "IS NOT NULL";
        else
            $value = "> '".$value."'";
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` ".$value."";
        return $this;
    }

    public function where_not_in($field,$in)
    {
        if(!is_array($in))
            $in = array($in);
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` NOT IN ('".implode("','", $in)."')";
        return $this;
    }

    public function where_in($field,$in)
    {
        if(!is_array($in))
            $in = array($in);
        $this->__sql_select['WHERE'][] = "`".$this->table."`.`".$field."` IN ('".implode("','", $in)."')";
        return $this;
    }

    public function where_not_between($field,$from,$to)
    {
        $this->__sql_select['WHERE'][] = "(`".$this->table."`.`".$field."` < '".$from."' OR `".$this->table."`.`".$field."` > '".$to."')";
        return $this;
    }

    public function where_between($field,$from,$to)
    {
        $this->__sql_select['WHERE'][] = "(`".$this->table."`.`".$field."` > '".$from."' AND `".$this->table."`.`".$field."` < '".$to."')";
        return $this;
    }

    /**
     * LIMIT
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        if(is_null($offset))
            $this->__sql_select['LIMIT'] = 'LIMIT '. ((int) $limit);
        else
            $this->__sql_select['LIMIT'] = 'LIMIT '. ((int) $limit) . ' OFFSET ' . ((int) $offset);
        return $this;
    }

    public function group_by($group_by)
    {
        $this->__sql_select['GROUP BY'] = $group_by;
        return $this;
    }

    public function order_by($order_by)
    {
        $this->__sql_select['ORDER BY'] = $order_by;
        return $this;
    }

    public function order_by_field($name, $order = 'ASC')
    {
        $this->__sql_select['ORDER BY'] = "`".$this->table."`.`".$name."` ".$order;
        return $this;
    }

    /**
     * Új elemről van szó?
     * @return boolean
     */
    public function isnew()
    {
        return $this->new;
    }

    /**
     * Object és adatbázis törlés
     */
    public function delete()
    {

        if(!$this->new)
        {
            $temp = $this->primary_key;
            $keys = "`".self::implode_with_key($temp,"` = '","' AND `")."'";

            // Adatbázisból
            $this->connection->query('DELETE FROM `'.$this->table.'` WHERE '.$keys.' ');
            // Adatbázisból

            // Osztályból
            $this->tags = array();
            // Osztályból
        }
        else
        {
            $tmp = array_merge($this->__sql_select,array('SELECT' => 'DELETE'));
            $this->connection->query($this->__sql($tmp));
        }

        return null;
    }

    /**
     * Mentés
     * @return $this
     */
    public function save()
    {

        $this->beforeSave();

        if(!$this->new && count($this->primary_key) < count($this->default_primary_key))
            return false;

        $tags = $this->tags;
        if(empty($tags))
            $tags = array();

        $columns = $this->connection->columns($this->table);
        if(is_bool($columns)) return false;

        $result = null;
        if(!empty($this->primary_key))
        {

            $where = array();
            $params = array();

            foreach ($tags as $key => $value)
            {
                if(isset($columns[$key]) && isset($this->primary_key[$key])) {
                    if ($value instanceof Field) {
                        $where[$key] = $value->save(false);
                        //$where[$key] = $value->toSqlParam($where[$key]);
                    } else {
                        $where[$key] = $value;
                    }
                    $params[$key] = $where[$key];
                    $where[$key] = ':'.$key;
                }
            }
            //$where = array_merge($this->primary_key, $where);

            $selector = "WHERE `".self::implode_with_key($where,"` = "," AND `")."";
            $sql= "SELECT * FROM `".$this->table."` ".$selector;
            $result = $this->connection->query($sql, $params);
            $result = $this->connection->fetch_assoc($result);
        }
        if(!empty($result))
        {
            $update = array();
            $params = array();
            $primaries = array();

            foreach ($tags as $key => $value)
            {
                if(isset($columns[$key])) {
                    if ($value instanceof Field) {
                        $update[$key] = $value->save(true);
                        //$update[$key] = $value->toSqlParam($update[$key]);
                    } else {
                        $update[$key] = $value;
                    }
                    if(array_key_exists($key, $update))
                    {
                        $params[$key] = $update[$key];
                        $update[$key] = ':' . $key;
                    }
                }
                if(isset($this->primary_key[$key])) {
                    $primaries[$key] = isset($params[$key]) ? $params[$key] : $this->primary_key[$key];
                }
            }
            if(!empty($params) && $params != $primaries)
            {
                //$where = array_merge($this->primary_key, $where);

                $primaries = empty($primaries) ? [] : $primaries;
                $pwhere = [];
                Sql::paramsToWhere($primaries, $pwhere, 'update_');

                $params = array_merge($params, $primaries);

                $query = "UPDATE `" . $this->table . "` SET `" . $this->implode_with_key($update, "` = ", " , `") . "";
                $query .= " WHERE `" . self::implode_with_key($pwhere, "` = ", " AND `") . "";
                $this->connection->query($query, $params);
            }
            $this->new = false;
        }
        else
        {
            $update = array();
            $params = array();

            foreach ($tags as $key => $value)
            {
                if(isset($columns[$key])) {
                    if ($value instanceof Field) {
                        if($value->isModified() || $value->isLoaded()) {
                            $update[$key] = $value->save(true);
                        }
                        //$update[$key] = $value->toSqlParam($update[$key]);
                    } else {
                        $update[$key] = $value;
                    }
                    if(array_key_exists($key, $update))
                    {
                        $params[$key] = $update[$key];
                        $update[$key] = ':' . $key;
                    }
                }
            }
            if(!empty($params)) {

                $query = "INSERT INTO `" . $this->table . "` (`" . implode('` , `', array_keys($update)) . "`) VALUES (" . implode(" , ", $update) . ")";
                $this->connection->query($query, $params);

                if (count($this->default_primary_key) == 1 && empty($this->primary_key[$this->default_primary_key[0]])) {
                    $id = $this->connection->id();
                    $this[$this->default_primary_key[0]] = $id;
                }
            }

            $this->new = false;
        }

        $this->afterSave();

        return $this;
    }

    protected function beforeSave(){}

    protected function afterSave(){}

    /**
     * Keresés és kész!
     * @return ORM[]|array
     */
    public function result($obj = true)
    {
        $instance = get_called_class();

        $sql = $this->__sql_select;

        $columns = [];

        foreach($this->fields as $key => $class)
        {
            $c = $class->column();
            if(is_array($c))
            {
                $columns = array_merge($columns, $c);
            }
            else
            {
                $columns[] = $c;
            }
        }

        if(empty($columns))
            $columns = ['`'.$this->table.'`.*'];
        if(!empty($this->columns))
        {
            $co = $this->columns;
            if(!is_array($columns))
                array_unshift($co, $columns);
            $columns = array_merge($co,$columns);
        }

        $sql['SELECT'] = $columns;

        $sql = $this->__sql($sql);
        $res = $this->connection->query($sql);
        $return = array();
        while($result = $this->connection->fetch_assoc($res))
        {
            $id = array();
            foreach($this->default_primary_key as $key)
            {
                if(!is_numeric($result[$key]))
                {
                    $id[$key] = $result[$key];
                }
                else
                {
                    $id[$key] = $result[$key];
                }
            }

            if(is_bool($obj))
            {
                if($obj == true)
                {
                    $inst = new $instance($id);
                    $return[] = $inst;
                }
                else
                {
                    $return[] = $result;
                }
            }
            elseif(is_callable($obj))
            {
                $return[] = call_user_func($obj,$result);
            }
        }
        //if(empty($return)) return null;
        return $return;
    }

    /**
     * Keresés és kész!
     * @return ORM
     */
    public function first($obj = true)
    {
        $instance = get_called_class();

        $sql = $this->__sql_select;

        $columns = [];

        foreach($this->fields as $key => $class)
        {
            $c = $class->column();
            if(is_array($c))
            {
                $columns = array_merge($columns, $c);
            }
            else
            {
                $columns[] = $c;
            }
        }

        if(empty($columns))
            $columns = ['`'.$this->table.'`.*'];
        if(!empty($this->columns))
        {
            $co = $this->columns;
            if(!is_array($columns))
                array_unshift($co, $columns);
            $columns = array_merge($co,$columns);
        }

        $sql['SELECT'] = $columns;

        $sql = $this->__sql(array_merge($sql, array('LIMIT' => 'LIMIT 1')));
        $res = $this->connection->query($sql);
        $return = array();
        while($result = $this->connection->fetch_assoc($res))
        {
            $id = array();
            foreach($this->default_primary_key as $key)
            {
                if(!is_numeric($result[$key]))
                {
                    $id[$key] = $result[$key];
                }
                else
                {
                    $id[$key] = $result[$key];
                }
            }

            if(is_bool($obj))
            {
                if($obj == true)
                {
                    $inst = new $instance($id);
                    return $inst;
                }
                else
                {
                    return $result;
                }
            }
            elseif(is_callable($obj))
            {
                return call_user_func($obj,$result);
            }
        }
        //if(empty($return)) return null;
        return null;
    }

    public function id_generation()
    {
        $return = md5(json_encode($this->primary_key));
        return $return;
    }

    public function read_from_language($name, $value, $tags)
    {
        if(is_array($value))
        {
            $lang = new Language($value);
        }
        else if($value instanceof Language)
            $lang = $value;
        else
            $lang = new Language($value);

        $lang['id'] = $name;
        return $lang;
    }

    public function write_to_language($name, $value, $tags, $save = true)
    {
        $prefix = $this->table;
        if(is_string($value))
        {
            return $value;
        }
        else
        {
            if($prefix.'_'.$name.'_' == $name)
                $name = $name.md5(json_encode($value));
            elseif(!startsWith($name, $prefix.'_'.$name.'_'))
                $name = $prefix.'_'.$name.'_'.md5(json_encode($value));

            if($save == true) {
                $lang = new Language($name);
                foreach ($value as $l => $v)
                {
                    $lang[$l] = $v;
                }
                $lang['what'] = $prefix;
                $lang['id'] = $name;
                $lang->save();
            }
            return $name;
        }
    }

    /**
     * Az ORM által megadott táblában fixálja a sorrendet
     * @param string $sortColumn sorrendet képző oszlop másnéven az oszlop ami tartalmazza a sorrendet
     * @param string $where Szűrő feltétel "csoportosításhoz"
     * @param string|array $id id oszlop neve ha esetleg nem "id" lenne vagy tömben az id-k felsorolása
     * @return $this
     */
    public function sortingFix($sortColumn = 'sort', $where = "", $id = 'id')
    {
        if(is_array($id)) {
            if(empty($id))
                return $this;
            $idWhere = [];
            foreach ($id as $_id) {
                $idWhere[] = "`" . $this->table . "`." . $_id . " = r." . $_id . "";
            }
            $idWhere = implode(" AND ", $idWhere);
            $ids = implode(", ", $id);
        } else {
            $idWhere = "`" . $this->table . "`." . $id . " = r." . $id . "";
            $ids = "".$id."";
        }
        $this->connection->execute("UPDATE `" . $this->table . "` JOIN ( SELECT @rownum:=@rownum+1 rownum, ".$ids." FROM `" . $this->table . "` CROSS JOIN (select @rownum := 0) rn ".(!empty($where) ? 'WHERE '.$where : '')." ORDER BY ".$sortColumn.") AS r ON ".$idWhere." SET `" . $this->table . "`.".$sortColumn." = r.rownum ".(!empty($where) ? 'WHERE '.$where : '')."");
        return $this;
    }

    /**
     * Sorrend változtatás két érték között.
     * @param $from int
     * @param $to int
     * @param string $sortColumn sorrendért felelős oszlop
     */
    public function sorting($from, $to, $sortColumn = 'sort')
    {

		$between_from = $from;
        $between_to = $to;
        $direction = "+";

		if ($from < $to)
        {
            $between_from = $from;
            $between_to = $to;
            $direction = "-";
        }
        else
        {
            $between_from = $to;
            $between_to = $from;
            $direction = "+";
        }

		// Az SQL utasításhoz szükséges sablon:
        /*String sql_template =
			"UPDATE [DB_TABLE]	"+
			"SET [DB_ORDER_FIELD] = IF ([DB_ORDER_FIELD] = [FROM], [TO], [DB_ORDER_FIELD] [DIRECTION_OPERATOR] 1)	"+
			"WHERE [DB_ORDER_FIELD] BETWEEN "+between_from+" AND "+between_to+"";
        sql_template += "	ORDER BY [DB_ORDER_FIELD]";*/
        $this->connection->execute("UPDATE " . $this->table . " SET " . $sortColumn . " = IF (" . $sortColumn . " = " . $from . ", " . $to . ", " . $sortColumn . " " . $direction . " 1) WHERE " . $sortColumn . " BETWEEN " . $between_from . " AND " . $between_to);
    }

    /**
     * SET
     * @param unknown $name
     * @param unknown $value
     */
    public function __set($name,$value)
    {
        $this[$name] = $value;
    }

    /**
     * GET
     * @param unknown $name
     * @return multitype:|NULL
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
        if(array_key_exists($name, $this->tags))
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
        if (array_key_exists($name, $this->tags))
        {
            unset($this[$name]);
        }
    }

    /**
     * ARRAY ------------------------------------------------
     */

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::next()
     */
    public function next(){
        return next($this->tags);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::rewind()
     */
    public function rewind(){
        reset($this->tags);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::current()
     */
    public function current(){
        $c = current($this->tags);
        if($c instanceof Field)
        {
            return $c->load();
        }
        else
        {
            return $c;
        }
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::key()
     */
    public function key(){
        return key($this->tags);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::valid()
     */
    public function valid(){
        $key = key($this->tags);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetGet()
     */
    public function offsetGet($name) {
        if(method_exists($this, $name))
            return call_user_func_array(array($this, $name), array());
        /*elseif(property_exists($this,$name))
            return $this->{$name};*/
        elseif(isset($this->tags[$name]))
        {
            $c = $this->tags[$name];
            if($c instanceof Field)
            {
                return $c->load();
            }
            else
            {
                return $c;
            }
        }
        elseif(isset($this->calculatedTags[$name]))
        {
            return $this->calculatedTags[$name];
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetSet()
     */
    public function offsetSet($name, $value) {

        if(in_array($name,$this->default_primary_key))
        {
            if(!is_numeric($value))
            {
                if(can_be_string($value))
                {
                    $this->primary_key[$name] = $value;
                }
                else
                {
                    $this->primary_key[$name] = $value;
                }
            }
            else
            {
                if(is_float($value))
                {
                    $this->primary_key[$name] = floatval($value);
                }
                else
                {
                    $this->primary_key[$name] = intval($value);
                }
            }
        }

        if(method_exists($this, $name))
            $value = call_user_func_array(array($this, $name), array($value));

        if(isset($this->tags[$name])) {
            if ($this->tags[$name] instanceof Field)
                $this->tags[$name]->setValue($value);
            else
                $this->tags[$name] = $value;
        }

        if(!isset($this->tags[$name])) {

            if(property_exists($this,$name))
                $this->{$name} = $value;
            else
                $this->calculatedTags[$name] = $value;

        }

        $this->id = $this->id_generation();
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetExists()
     */
    public function offsetExists($name) {
        if(isset($this->tags[$name]))
            return true;
        elseif(property_exists($this,$name))
            return true;
        elseif(method_exists($this, $name))
            return true;
        if(isset($this->calculatedTags[$name]))
            return true;
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetUnset()
     */
    public function offsetUnset($name) {
        if(isset($this->tags[$name]))
            unset($this->tags[$name]);
        if(isset($this->calculatedTags[$name]))
            unset($this->calculatedTags[$name]);
    }

    /**
     * ARRAY ------------------------------------------------
     */

    /**
     * Return Json formated string
     * @return string
     */
    public function __toString()
    {
        return (string) json_encode($this->jsonSerialize());
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $array = [];
        foreach ($this as $key => $tag) {
            $array[$key] = $tag;
            if($array[$key] instanceof self) {
                $array[$key] = $array[$key]->toArray();
            }
        }
        return $array;
    }

    /**
     * Minden olyan methodus ami segítségre lehet ebben a csodálatos osztályban és ennek a leszármazotaiban!
     */

    public static function implode_key($glue = "", $pieces = array()) {
        if(!is_array($pieces)) return '';
        $arrK = array();
        foreach($pieces as $k => $value)
            $arrK[] = $k;
        return implode($glue, $arrK);
    }

    public static function implode_with_key($assoc, $inglue = '>', $outglue = ',') {
        $return = '';

        foreach ($assoc as $tk => $tv) {
            $return .= $outglue . $tk . $inglue . $tv;
        }

        return substr($return, strlen($outglue));
    }

    public static function is_assoc_array($array){
        $bla = ARRAY_KEYS($array);
        $bla = ARRAY_SHIFT($bla);
        if(IS_ARRAY($array) && !IS_NUMERIC($bla)){
            return true;
        }
        return false;
    }

    /**
     * Minden olyan methodus ami segítségre lehet ebben a csodálatos osztályban és ennek a leszármazotaiban!
     */
}