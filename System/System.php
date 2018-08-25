<?php
namespace ComposerPack\System;

/**
 * System osztály. Ez az osztály kezeli a system táblát az adatbázisban.
 * @author Nagy Gergely info@nagygergely.eu
 * @version 0.5
 *
 */
class System
{
	protected static $__prefix = null;
	protected static $__table = "system";
	protected static $__db = null;
	
	protected static $__less = null;
	protected static $__mustache = null;

	public function __construct()
	{
		if(is_null(self::$__db))
			self::$__db = Sql::getDefaultDb();
		if(is_null(self::$__prefix))
			self::$__prefix = Site::$SQL_PREFIX;
		if(is_null(self::$__less))
			self::$__less = new \lessc();
		if(is_null(self::$__mustache))
			self::$__mustache = new \Mustache_Engine(Site::$CONFIG['mustache']);
	}
	
	public static function getLess()
	{
		return self::$__less;
	}
	
	public static function getMustache()
	{
		return self::$__mustache;
	}

	public static function get($name,$default=null)
	{
		$db = self::$__db->query("SELECT * FROM `".self::$__prefix.self::$__table."` WHERE `name` = '".$name."'");
		if(self::$__db->row == 1)
		{
			$db = self::$__db->fetch_assoc($db);
			return $db['value'];
		}
		return $default;
	}

	public static function set($name,$value)
	{
		$db = self::$__db->query("SELECT * FROM `".self::$__prefix.self::$__table."system` WHERE `name` = '".$name."'");
		if(self::$__db->fetch_array($db))
			$db = self::$__db->query("UPDATE `".self::$__prefix.self::$__table."system` SET `value` = '".$value."' WHERE `name` = '".$name."'");
		else
			$db = self::$__db->query("INSERT INTO `".self::$__prefix.self::$__table."system` (`value`,`name`) VALUES ('".$value."','".$name."')");
	}

}