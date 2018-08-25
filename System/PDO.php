<?php
/**
 * Created by PhpStorm.
 * User: chip
 * Date: 2018.03.25.
 * Time: 17:07
 */

namespace ComposerPack\System;


class PDO
{
    /**
     * @var \PDO
     */
    protected $link;

    private $dsn, $username, $password;

    public function __construct($dsn, $username, $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    private function connect()
    {
        $this->link = new \PDO($this->dsn, $this->username, $this->password);
    }

    public function __sleep()
    {
        return array('dsn', 'username', 'password');
    }

    public function __wakeup()
    {
        $this->connect();
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->link;
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->link, $name))
            return call_user_func_array([$this->link, $name], $arguments);
    }
}