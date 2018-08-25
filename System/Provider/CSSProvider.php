<?php
namespace ComposerPack\System\Provider;

use ComposerPack\System\Cache;
use ComposerPack\System\Control;
use ComposerPack\System\Settings;
use ComposerPack\System\System;
use ComposerPack\System\Minify\MinifyInterface;
use ComposerPack\System\Minify\CSSMinify;
use ComposerPack\System\Url;

/**
 * CSS össze gyüjtő
 * @author Nagy Gergely info@nagygergely.eu 2014
 * @version 0.1
 *
 */
class CSSProvider implements ProviderInterface
{
	private $_list = array();
	private $cache_css_solt = "css/";
	private $cache_less_solt = "less/";
	private $cache_ext = ".css";

	/* (non-PHPdoc)
	 * @see \ComposerPack\System\Provider\Provider::get()
	 */
	public function get($id) {
		if(isset($this->_list[$id]))
			return $this->_list[$id];
	}

	/* (non-PHPdoc)
	 * @see \ComposerPack\System\Provider\Provider::getAll()
	 */
	public function getAll() {
		return $this->_list;
	}

	/* (non-PHPdoc)
	 * @see \ComposerPack\System\Provider\Provider::set()
	 */
	public function set($value, $id = null, $type = null) {
        if(empty($id))
            $id = $value;
        if(!(startsWith($value, "https://") || startsWith($value, "http://") || startsWith($value, "//")) && !Url::isValidUrl($value)) {
            if (!file_exists(Settings::get("base_dir").DS.'public'.DS.$value)) {
                if(Url::isValidUrl($value)) {
                    $value = null;
                } else {
                    if ($type == "less") {
                        $cache_id = Cache::hash($value);
                        $cache_id = $this->cache_less_solt . $cache_id . $this->cache_ext;
                        $cache_data = Cache::get($cache_id);
                        $value = System::getLess()->compile($value);
                    } else {
                        $cache_id = Cache::hash($value);
                        $cache_id = $this->cache_css_solt . $cache_id . $this->cache_ext;
                        $cache_data = Cache::get($cache_id);
                    }
                    if (empty($cache_data)) {
                        Cache::set($cache_id, $value, 24 * 60 * 60);
                    }
                    $value = Cache::$dir . DS . $cache_id;
                }
            } else {
                if (endsWith($value, ".less")) {
                    $cache_id = Cache::hash($value);
                    $cache_id = $this->cache_less_solt . $cache_id . $this->cache_ext;
                    $cache_data = Cache::get($cache_id);
                    if (empty($cache_data)) {
                        $value = System::getLess()->compileFile($value);
                        Cache::set($cache_id, $value, 24 * 60 * 60);
                    }
                    $value = Cache::$dir . DS . $cache_id;
                }
                else {
                    $value = $value;
                }
            }
        }
        if(!empty($value))
		    $this->_list[$id] = $value;
	}

	/* (non-PHPdoc)
	 * @see \ComposerPack\System\Provider\Provider::clear()
	 */
	public function clear() {
		$this->_list = array();
	}
	
	public function renderHTML(CSSMinify $minify = null)
	{
		ob_start();
		$list = $this->_list;
		if(!empty($list))
		{
			if($minify != null)
			{
			    $l = $minify::minify($this->_list);
			    if(is_array($l))
			        $list = $l;
			    else
				    $list = array($l);
			}
			foreach($list as $id => $file)
			{
				?><link rel="stylesheet" type="text/css" href="<?=$file?>"/><? echo "\n";
			}
		}
		return ob_get_clean();
	}

}