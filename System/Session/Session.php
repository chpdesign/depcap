<?php
namespace ComposerPack\System\Session;

class Session
{
    
    private function __construct(){}

    public static function &get($name, $defaultValue = null)
    {
        if(isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        if(isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        $_SESSION[$name] = $defaultValue;
        return $defaultValue;
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function has($name)
    {
        if(isset($_SESSION[$name]))
            return true;
        if(isset($_COOKIE[$name]))
            return true;
        return false;
    }

    public static function remove($name)
    {
        if(isset($_SESSION[$name]))
            unset($_SESSION[$name]);
        if(isset($_COOKIE[$name]))
            unset($_COOKIE[$name]);
    }

}