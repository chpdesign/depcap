<?php
namespace ComposerPack\System;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\Config\Config;
use ComposerPack\System\Farmer\ContentFarmer;
use ComposerPack\System\Farmer\SeoFarmer;
use ComposerPack\System\Farmer\SiteFarmer;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

class Settings implements \ArrayAccess
{
    protected static $self = null;

    protected $config = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        if(self::$self == null)
            self::$self = $this;
        if(is_assoc_array($config['db'])) {
            // adatbázis kapcsolat
            Sql::connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['database'], isset($config['db']['name']) ? $config['db']['name'] : 'default');
        }
        else
        {
            foreach ($config['db'] as $index => $settings)
            {
                // adatbázis kapcsolat
                Sql::connect($settings['host'], $settings['user'], $settings['password'], $settings['database'], isset($settings['name']) ? $settings['name'] : 'default'.($index+1));
            }
        }
        $this->setFarmers([new ContentFarmer(), new SeoFarmer(), new SiteFarmer()]);
    }

    public static function getConfig()
    {
        return self::$self->config;
    }

    public static function setConfig(Config $config)
    {
        self::$self->config = $config;
    }

    public function urlRules()
    {
        // url rewrite rules
        $urls = [
            '^'.seo(lang('login', null, array("hu" => "Belépés", "en" => "Login", "de" => "Anmeldung"))).'$' => 'controller/index/login',
            '^'.seo(lang('logout', null, array("hu" => "Kijelentkezés", "en" => "Logout", "de" => "Ausloggen"))).'$' => 'controller/index/logout',
        ];
        $seourls = new Language();
        $seourls = $seourls->where('what', 'seo')->where_regexp('id', '^seo_url')->result(false);
        foreach ($seourls as $seourl) {
            $urls['^'.$seourl[Language::language()].'$'] = 'index/contact';
        }
        $urls['^packages.json$'] = 'packages/index';
        $urls['^p\/provider\$([^\.]*).json$'] = 'packages/provider/$1';
        $urls['^p\/(.*)\/(.*)\$([^\.]*).json$'] = 'packages/repository/$1/$2/$3';
        return $urls;
    }

    protected $farmers = [];

    public function setFarmers($farmers = [])
    {
        $this->farmers = $farmers;
    }

    public function addFarmer($farmer)
    {
        $this->farmers[] = $farmer;
    }

    public function getFarmers()
    {
        return $this->farmers;
    }

    private static $whoops = null;

    public static function whoops($whoops = null)
    {
        if(self::$whoops == null && $whoops == null) {
            $run = new \Whoops\Run;

            if (Controller::isAjax()) {
                $JsonHandler = new JsonResponseHandler();
                //if($config['debug'])
                    $JsonHandler->addTraceToOutput(true);
                $run->pushHandler($JsonHandler);
            } else {
                $handler = new PrettyPageHandler();
                $run->pushHandler($handler);
            }
            self::$whoops = $run->register();
        }
        else if(self::$whoops == null && $whoops != null) {
            self::$whoops = $whoops;
        }

        return self::$whoops;
    }

    public static function get($name)
    {
        return self::$self->__get($name);
    }

    public static function set($name, $value)
    {
        return self::$self->__set($name, $value);
    }

    public static function has($name)
    {
        return self::$self->__isset($name);
    }

    public static function remove($name)
    {
        return self::$self->__unset($name);
    }

    /**
     * Ismeretlen változó lekérdezése.
     * A mezőértékekből próbál visszadni értéket. Ha nem tud, akkor NULL-at ad vissza!
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if(!isset($this->config[$name]))
            return null;
        else
            return $this->config[$name];
    }


    /**
     * Mezőérték beállítás közvetlenül $object->VARIABLE = VALUE hívással
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }


    /**
     * Mezőérték létezésének ellenőrzése
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }


    /**
     * Mezőérték teljes megszüntetése
     * @param string $name
     */
    public function __unset($name)
    {
        if(isset($this->config[$name]))
            unset($this->config[$name]);
    }

    /*
     * TÖMBÖS HIVATKOZÁSHOZ SZÜKSÉGES METÓDUSOK
     */

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
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