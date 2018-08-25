<?
namespace ComposerPack\System;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\Provider\CSSProvider;
use ComposerPack\System\Provider\JSProvider;
use ComposerPack\System\Render\JSONRender;
use ComposerPack\System\Render\PHPRender;
use ComposerPack\System\Render\RenderInterface;

/**
 * Template osztály a behívandó templatekhez.<br/>Semmi extra csak a szokásos.<br/>
 * @example
 * $template = new Template('alma');<br/>
 * $template->magvak = "magok";
 * @author Nagy Gergely info@nagygergely.eu
 *
 */
class Template extends \ArrayIterator implements RenderAbleInterface
{
    protected $filename = "not found!";
	protected $data = array();
	protected $cache = false;
	protected $extension = "php";

    /**
     * @var JSProvider
     */
    protected $js;
    /**
     * @var CSSProvider
     */
    protected $css;

	/**
	 * Megengedett mappa ahonnan a templateket be lehet tölteni!
	 * @var array
	 */
	public static $FOLDERS = array();

	public function __construct( $filename , $where = "" , $cache = false )
	{
	    $this->data['js'] = $this->js = new JSProvider();
        $this->data['css'] = $this->css = new CSSProvider();

		$this->cache = $cache == true ? 24*60*60 : $cache;
		if(is_string($filename))
		{
		    if(strpos($filename, ".") !== false) {
                $_filename = $filename;
            } else {
                $_filename = $filename . "." . $this->extension;
            }
			$_filename = str_replace(array("\\","/"), DS, $_filename);
			$this->filename = $filename;
			if(!empty($where) && isset(self::$FOLDERS[$where]))
			{
				$folder = self::$FOLDERS[$where];
				// HA NEM NYERT MEGPRÓBÁLJUK A MAPPÁVAL!
				$fileinfo = pathinfo(str_replace(array("\\","/"), DS, $folder.$_filename));
				$fileinfo['dirname'] = str_replace(array("\\","/"), DS, $fileinfo['dirname']);
				$folder = str_replace(array("\\","/"), DS, $folder);
				if(substr($fileinfo['dirname'].DS, 0,strlen($folder)) == $folder && file_exists($folder.$_filename))
				{
					$this->filename = $folder.$_filename;
					return;
				}
			}
			elseif(!empty($where) && !isset(self::$FOLDERS[$where]) && class_exists('ComposerPack\\Models\\'.$where))
			{
				self::$FOLDERS[$where] = $folder = dirname(__DIR__).'/Pages/'.$where.'/Template/';
				// HA NEM NYERT MEGPRÓBÁLJUK A MAPPÁVAL!
				$fileinfo = pathinfo(str_replace(array("\\","/"), DS, $folder.$_filename));
				$fileinfo['dirname'] = str_replace(array("\\","/"), DS, $fileinfo['dirname']);
				$folder = str_replace(array("\\","/"), DS, $folder);
				if(substr($fileinfo['dirname'].DS, 0,strlen($folder)) == $folder && file_exists($folder.$_filename))
				{
					$this->filename = $folder.$_filename;
					return;
				}
			}
			elseif(!empty(self::$FOLDERS))
			{
				foreach(self::$FOLDERS as $key => $folder)
				{
					// HA NEM NYERT MEGPRÓBÁLJUK A MAPPÁVAL!
					$fileinfo = pathinfo(str_replace(array("\\","/"), DS, $folder.$_filename));
					$fileinfo['dirname'] = str_replace(array("\\","/"), DS, $fileinfo['dirname']);
					$folder = str_replace(array("\\","/"), DS, $folder);
					if(substr($fileinfo['dirname'].DS, 0,strlen($folder)) == $folder && file_exists($folder.$_filename))
					{
						$this->filename = $folder.$_filename;
						return;
					}
				}
			}
			else
			{
				if(file_exists($_filename))
				{
					$this->filename = $_filename;
					return;
				}
				elseif(file_exists(__DIR__.DS.'..'.DS.'template'.DS.$_filename))
				{
					$this->filename = __DIR__.DS.'..'.DS.'template'.DS.$_filename;
					return;
				}
				else
				{
					$this->filename = $filename;
					return;
				}
			}
			//trigger_error('Template not found: <br>'.$this->filename, E_USER_WARNING);
			/*//$this->filename = __DIR__.'/../template/'.$filename.".php";
			 if(file_exists($filename.".php"))
			 {
			$this->filename = $filename.".php";
			}
			elseif(file_exists(__DIR__.'/../template/'.$filename.".php"))
			{
			$this->filename = __DIR__.'/../template/'.$filename.".php";
			}
			else
			{
			$this->filename = $filename.".php";
			}*/
		}
	}

	public function escape( $str )
	{
		return htmlspecialchars( $str ); //for example
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

	/**
	 * Render function
	 * @param RenderInterface $function
	 * @return string
	 */
    public function render(/* $print = false */RenderInterface $function = null)
    {
        if($this->filename == 'json')
            return (new JSONRender())->render('', $this->data);
        if(empty($function))
            $function = new PHPRender();
        if(file_exists($this->filename))
        {
            if($this->cache !== false)
            {
                $cache_id = 'template'.DS.Cache::hash($this->filename.serialize($this->data).Language::language());
                $cache_data = Cache::get($cache_id);
                if(!empty($cache_data))
                {
                    $rendered = $cache_data;
                }
                else
                {
                    $rendered = $function::render($this->filename,$this->data);
                    Cache::set($cache_id, $rendered, $this->cache);
                }
            }
            else
            {
                $rendered = $function::render($this->filename,$this->data);
            }
            /*if($print)
            {
                echo $rendered;
                return;
            }*/
            return $rendered;
        }
        else
        {
            $rendered = $function::render($this->filename,$this->data);
            return $rendered;
        }
    }

    public function __toString()
    {
        $output = '';
        try
        {
            $output = $this->render();
        }
        catch(\Exception $e)
        {
            return Settings::whoops()->handleException($e);
            //$output = '';
            //$output .= $e->getMessage();
            //$output .= PHP_EOL;
            //$output .= $e->getTraceAsString();
        }
        return $output;
    }

}