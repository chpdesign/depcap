<?php
namespace ComposerPack\System\Config;

use ComposerPack\System\Sql;

class MysqlConfig extends Config
{

    protected $db;

    protected $id;

    public function __construct($id) {
        $this->db = Sql::getDefaultDb();
        $this->id = $id;
        $query = $this->db->query("SELECT * FROM `config` WHERE `id` = :id ORDER BY `sort` ASC", ["id" => $id]); // CHAR_LENGTH(`key`) DESC, `key` DESC,
        $config = [];
        while ($row = $this->db->fetch_assoc($query))
        {
            $value = $row['value'];
            if($value == '[]')
                $value = [];
            $this->assignArrayByPath($config, $row['key'], $value);
        }
        parent::__construct($config);
    }

    public function save($file = null)
    {
        $this->db->query("DELETE FROM `config` WHERE `id` = :id", ["id" => $this->id]);
        $sort = 1;
        foreach(array_dot($this->config) as $key => $svalue)
        {
            if(is_array($svalue) && empty($svalue))
                $svalue = '[]';
            $this->db->query(
                "INSERT INTO `config` (`id`, `key`, `value`, `sort`) VALUES (:id, :key, :value, :sort) ON DUPLICATE KEY UPDATE `value` = :value, `sort` = :sort",
                ["id" => $this->id, "key" => $key, "value" => $svalue, "sort" => $sort]
            );
            $sort++;
        }
        return true;
    }

    public function merge($config)
    {
        $c = new Config([]);
        if(is_class_a($config,$c))
        {
            $config = $config->toArray();
        }
        if(is_array($config))
        {
            foreach ($config as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
        return $this;
    }

    public function __toString()
    {
        return (string) json_encode($this->config);
    }

    // php dot notation to array
    // https://stackoverflow.com/questions/9635968/convert-dot-syntax-like-this-that-other-to-multi-dimensional-array-in-php
    protected function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

}