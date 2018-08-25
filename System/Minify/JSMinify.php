<?php
namespace ComposerPack\System\Minify;

use ComposerPack\System\Cache;

use ComposerPack\System\Settings;
use ComposerPack\System\Url;

/**
 * JS-eket gyüjti össze és minifikálja le!
 * @author Nagy Gergely info@nagygergely.eu 2014
 * @version 0.1
 *
 */
class JSMinify implements MinifyInterface
{
	
	private static $minify_solt = "minify/";
	private static $minify_ext = ".js";
	
	/* (non-PHPdoc)
	 * http://manas.tungare.name/software/css-compression-in-php/
	 * @see MinifyInterface::minify()
	 */
	public static function minify(array $files = array()) {
		$cache_id = Cache::hash($files);
		$cache_id = self::$minify_solt.$cache_id.self::$minify_ext;
		$cache_data = Cache::get($cache_id);
		$_files = [];
		$value = [];
		if(empty($cache_data))
		{
			/*$buffer = "";
			foreach ($files as $file) {
				$buffer .= file_get_contents($file);
			}*/
	
			//$buffer = \JShrink\Minifier::minify($buffer, array('flaggedComments' => false));
			$minifier = new \MatthiasMullie\Minify\JS();
			foreach ($files as $file) {
                if(startsWith($file, Settings::get("base_link")))
                {
                    $file = substr($file, strlen(Settings::get("base_link")));
                }
                if((startsWith($file, "https://") || startsWith($file, "http://") || startsWith($file, "//")) && Url::isValidUrl($file)) {

                    $cache_id = Cache::hash($_files);
                    $cache_id = self::$minify_solt.$cache_id.self::$minify_ext;
                    //$cache_data = Cache::get($cache_id);

                    Cache::set($cache_id, $minifier->minify(), 24*60*60);
                    $value[] = Cache::getPath(true).$cache_id;
                    $value[] = $file;
                    $minifier = new \MatthiasMullie\Minify\JS();
                    $_files = [];
                }
                else {
                    $_files[] = $file;
                    if(file_exists(Settings::get("base_dir").DS.'public'.DS.$file))
                        $minifier->add(Settings::get("base_dir").DS.'public'.DS.$file);
                    else
                        $minifier->add($file);
                }
			}

            $cache_id = Cache::hash($_files);
            $cache_id = self::$minify_solt.$cache_id.self::$minify_ext;
            //$cache_data = Cache::get($cache_id);

			Cache::set($cache_id, $minifier->minify(), 24*60*60);
		}
		$value[] = Cache::getPath(true).$cache_id;
	
		return $value;
	}

}