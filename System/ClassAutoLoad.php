<?php
namespace ComposerPack\System;


/**
 * 
 * @author Nagy Gergely info@nagygergely.eu
 * @version 0.1
 *
 */
class ClassAutoLoad
{
	/**
	 * Az osztályok megtalálásának elősegítésére szolgáló tömb
	 * Ez tartalmazza azokat a helyeket ahol az osztályok előfordulhatnak
	 * @var unknown
	 */
	private static $AUTO_LOAD = array();
	
	/**
	 * Itt tárolódnak a megtalált osztályok elérési útvonala ha szükség lenne rájuk
	 * @var unknown
	 */
	private static $CLASS_LOCATIONS = array();
	
	/**
	 * Vissza adja az osztályokhoz tartozó elérési útvonalakat
	 * @return \ComposerPack\System\unknown
	 */
	public static function getClassLocations()
	{
		return self::$CLASS_LOCATIONS;
	}
	
	/**
	 * Új elérési útvonalak megadása
	 * @param unknown $name osztály név vagy egy tömb felsorolva benne osztály név és elérés
	 * @param string $value ha a name string akkor ide kerül a osztálynévhez tartozó elérés
	 */
	public static function add($name, $value = null)
	{
		if(is_array($name) && is_null($value))
		{
			self::$AUTO_LOAD = array_merge(self::$AUTO_LOAD,$name);
		}
		else
		{
			self::$AUTO_LOAD[$name] = $value;
		}
	}
	
	/**
	 * Az automatikusan betöltődő osztály lista visszaadása
	 * @return \ComposerPack\System\unknown
	 */
	public static function getLoad()
	{
		return self::$AUTO_LOAD;
	}
	
	/**
	 * A php autoload class metódusa ezt fogja meghívni... nem bántani csak ha nagyon muszály!
	 * @param unknown $info
	 */
	public static function autoload($info)
	{
		$info = explode("\\", $info);
		$class = $info[count($info)-1];
		$namespace = $info;
		unset($namespace[count($namespace)-1]);
		$namespaces = $namespace;
		$namespace = implode("\\", $namespace);

		if(file_exists (__DIR__ . '/../' . implode('/', $info) . '.php'))
		    require_once (__DIR__ . '/../' . implode('/', $info) . '.php');
	}
	
	/**
	 * Megpróbálja felderíteni az adott névből hogy az az osztály hol lehet a file-ok között
	 * @param unknown $info osztály név
	 * @return string|NULL
	 */
	public static function translateLocation($info)
	{
		$info = explode("\\", $info);
		$class = $info[count($info)-1];
		$namespace = $info;
		unset($namespace[count($namespace)-1]);
		$namespaces = $namespace;
		$namespace = implode("\\", $namespace);
		
		if(!empty($namespaces))
		{
			if($namespaces[0] == 'ComposerPack')
			{
				$_namespace = $namespaces;
				if($_namespace[1] == 'Pages')
				{
					$last = $class;
					$last = explode("_", $last);
					$last = $last[0];
					array_splice($_namespace, 2, 0, $last);
					$class = $last;
				}
				unset($_namespace[0]);
				$_namespace = implode("\\", $_namespace);
				if(isset(self::$AUTO_LOAD[$_namespace]))
				{
					$level = str_replace("__CLASS__", $class, self::$AUTO_LOAD[$_namespace]);
					$level = str_replace("__HALF:CLASS__", str_replace("_",DIRECTORY_SEPARATOR,$class), $level);
					$level = $level."/".$class.'.php';
					$path = $level;
				}
			}
		}
		else foreach (self::$AUTO_LOAD as $key => $level)
		{
			$level = str_replace("__CLASS__", $class, $level);
			$level = str_replace("__HALF:CLASS__", str_replace("_",DIRECTORY_SEPARATOR,$class), $level);
			if(file_exists($level.'/'.$class.'.php'))
			{
				$path = $level.'/'.$class.'.php';
			}
		}
		if(!empty($path))
		{
			return $path;
		}
		return null;
	}
}