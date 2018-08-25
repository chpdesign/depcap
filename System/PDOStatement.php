<?php
namespace ComposerPack\System;

use PDO;

/**
 * Az eredeti PDOStatment annyi különbséggel hogy ki lehet tőle kérni mi a query paraméterekkel!
 * Class PDOStatement
 * @package BooqBinder\System
 */
class PDOStatement extends \PDOStatement
{
    /**
     * bindValues with bindParam or bindValue
     * @var array
     */
    protected $_bindValues = [];
    /**
     * The values for execute query
     * @var array
     */
    protected $_executeValues = [];

    /**
     * string const for preg replace qouted string in sql query for replaceing ? marks
     */
    const QUOTED_STRING = "yUjhLmaTDscycSwSgILdepxHApoxSCIRwFawjnnZXuwEgCvCEdRTgXwieGuuhshELZkMCwEtyankDlil";

    /**
     * PDOStatement constructor.
     */
    protected function __construct()
    {
        // need this empty construct()!
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
     * Binds a parameter to the specified variable name
     * @link http://php.net/manual/en/pdostatement.bindparam.php
     * @param mixed $parameter <p>
     * Parameter identifier. For a prepared statement using named
     * placeholders, this will be a parameter name of the form
     * :name. For a prepared statement using
     * question mark placeholders, this will be the 1-indexed position of
     * the parameter.
     * </p>
     * @param mixed $variable <p>
     * Name of the PHP variable to bind to the SQL statement parameter.
     * </p>
     * @param int $data_type [optional] <p>
     * Explicit data type for the parameter using the PDO::PARAM_*
     * constants.
     * To return an INOUT parameter from a stored procedure,
     * use the bitwise OR operator to set the PDO::PARAM_INPUT_OUTPUT bits
     * for the <i>data_type</i> parameter.
     * </p>
     * @param int $length [optional] <p>
     * Length of the data type. To indicate that a parameter is an OUT
     * parameter from a stored procedure, you must explicitly set the
     * length.
     * </p>
     * @param mixed $driver_options [optional] <p>
     * </p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        $this->_bindValues[$parameter] = $variable;
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 1.0.0)<br/>
     * Binds a value to a parameter
     * @link http://php.net/manual/en/pdostatement.bindvalue.php
     * @param mixed $parameter <p>
     * Parameter identifier. For a prepared statement using named
     * placeholders, this will be a parameter name of the form
     * :name. For a prepared statement using
     * question mark placeholders, this will be the 1-indexed position of
     * the parameter.
     * </p>
     * @param mixed $value <p>
     * The value to bind to the parameter.
     * </p>
     * @param int $data_type [optional] <p>
     * Explicit data type for the parameter using the PDO::PARAM_*
     * constants.
     * </p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $this->_bindValues[$parameter] = $value;
        return parent::bindValue($parameter, $value, $data_type);
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
     * Executes a prepared statement
     * @link http://php.net/manual/en/pdostatement.execute.php
     * @param array $input_parameters [optional] <p>
     * An array of values with as many elements as there are bound
     * parameters in the SQL statement being executed.
     * All values are treated as <b>PDO::PARAM_STR</b>.
     * </p>
     * <p>
     * You cannot bind multiple values to a single parameter; for example,
     * you cannot bind two values to a single named parameter in an IN()
     * clause.
     * </p>
     * <p>
     * You cannot bind more values than specified; if more keys exist in
     * <i>input_parameters</i> than in the SQL specified
     * in the <b>PDO::prepare</b>, then the statement will
     * fail and an error is emitted.
     * </p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function execute($input_parameters = null)
    {
        try {
            if (is_array($input_parameters))
                $this->_executeValues = $input_parameters;
            else
                $this->_executeValues = $this->_bindValues;
            return parent::execute($input_parameters);
        }
        catch (\PDOException $e)
        {
            //if(getConfig('log_on')==1)
            //{
                // $e->errorInfo[0] String error Code
                // $e->errorInfo[1] Int error Code
                $newExc = new \PDOException($e->getMessage() . ": \r\n" . $this->debugQuery() . "\r\n", $e->errorInfo[1], $e);
                throw $newExc;
            //}
            //else
            //{
            //    throw $e;
            //}
        }
    }

    /**
     * @param bool $replaced if it's true, replace all the possible parameters
     * @return string
     */
    public function debugQuery($replaced=true)
    {
        $q = $this->queryString;

        if (!$replaced) {
            return $q;
        }

        // https://www.metaltoad.com/blog/regex-quoted-string-escapable-quotes
        if($this->isAssoc($this->_executeValues))
        {
            return preg_replace_callback('/:([0-9a-z_]+)/i', array($this, 'replaceAssoc'), $q);
        }
        else
        {
            $matches = [];
            preg_match_all('/((?<![\\\\])[\'"])((?:.(?!(?<![\\\\])\1))*.?)\1/', $q, $matches, PREG_SET_ORDER);
            foreach ($matches as $key => $match)
            {
                $matches[$key] = $match[0];
            }
            $q = preg_replace('/((?<![\\\\])[\'"])((?:.(?!(?<![\\\\])\1))*.?)\1/', self::QUOTED_STRING, $q);
            reset($this->_executeValues);
            $q = preg_replace_callback('/(\?)/i', array($this, 'replaceIndex'), $q);
            reset($this->_executeValues);
            $from = '/'.preg_quote(self::QUOTED_STRING, '/').'/';
            for($i = 0; $i <= count($this->_executeValues); $i++)
            {
                $q = preg_replace($from, $matches[$i], $q, 1);
            }
            return $q;
        }
    }

    /**
     * helper method for replace ? mark parameters
     * @param $m
     * @return string
     */
    protected function replaceIndex($m)
    {
        $keys = array_keys($this->_executeValues);
        $v = current($this->_executeValues);
        $key = key($this->_executeValues);
        next($this->_executeValues);
        if ($v === null) {
            $v = "NULL";
        }
        else if (!is_numeric($v)) {
            $v = "'".str_replace("'", "''", $v)."'";
        }

        return "". $v ." /* ".array_search($key, $keys)." */";
    }

    /**
     * helper method for replacing :key parameters
     * @param $m
     * @return string
     */
    protected function replaceAssoc($m)
    {
        $key = $m[1];
        if(isset($this->_executeValues[$key])) {
            $v = $this->_executeValues[$key];
            if ($v === null) {
                $v = "NULL";
            } else if (!is_numeric($v)) {
                $v = "'" . str_replace("'", "''", $v) . "'";
            }

            return "" . $v . " /* " . $key . " */";
        }
        else
            return ":".$key;
    }

    /**
     * the array is assoc or not
     * @param array $arr
     * @return bool
     */
    protected function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}