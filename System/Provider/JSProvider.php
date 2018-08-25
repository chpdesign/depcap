<?php
namespace ComposerPack\System\Provider;

use ComposerPack\System\Cache;
use ComposerPack\System\Control;
use ComposerPack\System\Minify\MinifyInterface;
use ComposerPack\System\Minify\JSMinify;
use ComposerPack\System\Settings;
use ComposerPack\System\Url;

/**
 * JS össze gyüjtő
 * @author Nagy Gergely info@nagygergely.eu 2014
 * @version 0.1
 *
 */
class JSProvider  extends \ArrayIterator implements ProviderInterface
{
	private $_list = array();
	private $cache_solt = "js/";
	private $cache_ext = ".js";

	private $data = [];

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
                    $cache_id = Cache::hash($value);
                    $cache_id = $this->cache_solt . $cache_id . $this->cache_ext;
                    $cache_data = Cache::get($cache_id);
                    if (empty($cache_data)) {
                        Cache::set($cache_id, $value, 24 * 60 * 60);
                    }
                    $value = Cache::$dir . DS . $cache_id;
                }
            }/* else {
                $value = $value;
            }*/
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

	public function renderHTML(JSMinify $minify = null)
	{
		ob_start();
        ?><script type="text/javascript">!function(n,o){for(var i in o)o.hasOwnProperty(i)&&"function"!=typeof o[i]&&(window[i]=o[i])}(window, <?php echo json_encode($this->data); ?>);</script><?php
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
				?><script type="text/javascript" src="<?=$file?>"></script><? echo "\n";
		}
		}
		return ob_get_clean();
	}

    /**
     * SET
     * @param unknown $name
     * @param unknown $value
     */
    public function __set($name,$value)
    {
        $this[$name] = $value;
    }

    /**
     * GET
     * @param unknown $name
     * @return multitype:|NULL
     */
    public function __get($name)
    {
        if(isset($this[$name]))
            return $this[$name];
        else
            return null;
    }

    /**
     * Tag ellenörzés
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if(array_key_exists($name, $this->data))
            return isset($this[$name]);
        else
            return false;
    }


    /**
     * Tag törlés
     * @param string $name
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->data))
        {
            unset($this[$name]);
        }
    }

    /**
     * ARRAY ------------------------------------------------
     */

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::next()
     */
    public function next(){
        return next($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::rewind()
     */
    public function rewind(){
        reset($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::current()
     */
    public function current(){
        return current($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::key()
     */
    public function key(){
        return key($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::valid()
     */
    public function valid(){
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetGet()
     */
    public function offsetGet($name) {
        if(isset($this->data[$name]))
            return $this->data[$name];
        else
            return null;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetSet()
     */
    public function offsetSet($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetExists()
     */
    public function offsetExists($name) {
        return isset($this->data[$name]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayIterator::offsetUnset()
     */
    public function offsetUnset($name) {
        unset($this->data[$name]);
    }

    /**
     * ARRAY ------------------------------------------------
     */

}