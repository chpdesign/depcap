<?
namespace ComposerPack\System;

/**
 * Az oldal cache-elését végzi.
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!   http://php.net/manual/en/function.touch.php#84081  !!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 
 * @author Nagy Gergely info@nagygergely.eu
 *
 */
class Cache
{
	public static $dir = 'cache';
	/* lejárati dátum végitélet napja.. */
	public static $default_expire = 3600;
	public static $clear_random_min = 0;
	public static $clear_random_max = 1000;
	public static $clear_limit_min = 0;
	public static $clear_limit_max = 10;

    /**
     * @param $hash
     * @return string
     */
	public static function hash($hash)
	{
		return hash('sha1', serialize($hash));
	}

	/**
	 * új cache létrehozása
	 * @param unknown $id cache azonosítója
	 * @param unknown $data tartalma
	 * @param string $expire lejárat
	 */
	public static function set($id, $data, $expire = null)
	{
		if(!is_string($id) && !is_numeric($id))$id = self::hash($id);
		if(!is_string($data))$data = serialize($data);
		if(empty($expire))$expire = self::$default_expire;
		$path = self::getPath() . $id;
		if(!is_dir(dirname($path)))
			mkdir(dirname($path), 0755, true);
		//file_put_contents($path, $expire."\n".$data);
		file_put_contents($path, $data);
		chmod($path, 0644);
		touch($path,time()+$expire);
	}
	
	/**
	 * Cache mappa elérési útvonala
	 * @param string $t külső vagy belső elérés ha true akkor külső
	 * @return string
	 */
	public static function getPath($t = false)
	{
		$dir = ( $t == true ? Settings::get("base_link") : Settings::get("base_dir") . DS . 'public' . DS ) . self::$dir . DS;
		$dir = str_replace("\\", "/", $dir);
		return $dir;
	}

	/**
	 * cache tartalom vissza adása
	 * @param unknown $id cache azonosítója
	 * @return void|NULL|string ha nincs ilyen cache-elt tartalom akkor null a vissza térés máskülönben a cache tartalma
	 */
	public static function get($id)
	{
		if(!is_string($id) && !is_numeric($id)) $id = self::hash($id);
		$path = self::getPath() . $id;
		if(!is_file($path))return;
		//$f = fopen($path, 'r');
		//$expire = $line = fgets($f);
		if( time() > filemtime($path) )
		{
			//fclose($f);
			unlink($path);
			return null;
		}
		//$cache = fread($f, filesize($path)-strlen($line));
		$cache = file_get_contents($path);//fread($f, filesize($path));
		//fclose($f);
		self::clear();
		return $cache;
	}
	
	/**
	 * cache tartalom vissza adása
	 * @param unknown $id cache azonosítója
	 * @return void|NULL|string ha nincs ilyen cache-elt tartalom akkor null a vissza térés máskülönben a cache tartalma
	 */
	public static function inc($id)
	{
		if(!is_string($id) && !is_numeric($id)) $id = self::hash($id);
		$path = self::getPath() . $id;
		if(!is_file($path))return;
		//$f = fopen($path, 'r');
		//$expire = $line = fgets($f);
		if( time() > filemtime($path) )
		{
			//fclose($f);
			unlink($path);
			return null;
		}
		//$cache = fread($f, filesize($path)-strlen($line));
		$cache = include($path);//fread($f, filesize($path));
		//fclose($f);
		self::clear();
		return $cache;
	}

	/**
	 * Cache törlése
	 * @param unknown $id törölendő cache azonosítója
	 * @return void|boolean
	 */
	public static function delete($id)
	{
		if(!is_string($id)&& !is_numeric($id))
			$id = self::hash($id);
		$path = self::getPath() . $id;
		if(!is_file($path))
			return;
		return unlink($path);
	}

	/**
	 * Takarítás
	 * @param string $check
	 * @param string $full_gc
	 * @return void|NULL
	 */
	public static function clear($check = true, $full_gc = false)
	{
		 $random = mt_rand(self::$clear_random_min, self::$clear_random_max);
		if($check)
		{
			if($random < self::$clear_limit_min || $random > self::$clear_limit_max)return;
		}
		self::directory_list(self::getPath());
		if($full_gc)
		{
			$file_list_index_min = 0;
			$file_list_index_max = count(self::$file_list);
		}
		else
		{
			$file_list_index_min = mt_rand(0, count(self::$file_list)-1);
			$file_list_index_max = mt_rand($file_list_index_min, count(self::$file_list));
		}
		$sliced_file_list = array_slice(self::$file_list, $file_list_index_min, $file_list_index_max - $file_list_index_min );
		foreach($sliced_file_list as $file)
		{
			$file = realpath($file);
			if($file != false && is_file($file))
			{
				$f = fopen($file, 'r');
				if( time() > filemtime($file) )
				{
					unlink($file);
					return null;
				}
			}
			fclose($f);
		}
	}

	protected static $file_list = array();
	static protected function directory_list($dir)
	{
		if(is_file($dir))
		{
			if(!preg_match('#\.htaccess$#', $dir))
			{
				self::$file_list[] = $dir;
			}
			return self::$file_list;
		}
		$dir_content = scandir($dir);
		array_shift($dir_content);
		array_shift($dir_content);
		foreach($dir_content as $file)
		{
		 self::directory_list($dir.'/'.$file);
		}
		return self::$file_list;

	}

}
?>