<?php
namespace ComposerPack\System\Config;

class Config extends \ArrayIterator implements ConfigInterface
{
	
	protected $config = array();

	public function __construct(array $config) {
	    $this->config = $config;
	}

	public function save($file)
    {
        file_put_contents($file, var_export($this->config, true));
        return true;
    }
	
	public function merge($config)
	{
	    $c = new Config([]);
		if(is_array($config))
		{
			$this->config = array_merge($this->config,$config);
		}
		elseif(is_class_a($config,$c))
		{
			$this->config = array_merge($this->config,$config->toArray());
		}
		return $this;
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
		if(array_key_exists($name, $this->config))
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
		if (array_key_exists($name, $this->config))
		{
			unset($this[$name]);
		}
	}
	
	/**
	 ARRAY ------------------------------------------------
	 */
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::next()
	 */
	public function next(){
		return next($this->config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::rewind()
	 */
	public function rewind(){
		reset($this->config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::current()
	 */
	public function current(){
		return current($this->config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::key()
	 */
	public function key(){
		return key($this->config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::valid()
	 */
	public function valid(){
		$key = key($this->config);
		return ($key !== NULL && $key !== FALSE);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::offsetGet()
	 */
	public function offsetGet($name) {
		if(isset($this->config[$name]))
			return $this->config[$name];
		else
			return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::offsetSet()
	 */
	public function offsetSet($name, $value) {
		$this->config[$name] = $value;
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::offsetExists()
	 */
	public function offsetExists($name) {
		return isset($this->config[$name]);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::offsetUnset()
	 */
	public function offsetUnset($name) {
		unset($this->config[$name]);
	}
	
	/**
	 ARRAY ------------------------------------------------
	 */
	
	public function __toString()
	{
		return (string) json_encode($this->config);
	}
	
	public function toArray()
	{
		return $this->config;
	}
	
	/**
	 * Minden olyan methodus ami segítségre lehet ebben a csodálatos osztályban és ennek a leszármazotaiban!
	 * HELPER methods -------------------------------------
	 */
	
	public static function implode_key($glue = "", $pieces = array()) {
		if(!is_array($pieces)) return '';
		$arrK = array();
		foreach($pieces as $k => $value)
			$arrK[] = $k;
		return implode($glue, $arrK);
	}
	
	public static function implode_with_key($assoc, $inglue = '>', $outglue = ',') {
		$return = '';
	
		foreach ($assoc as $tk => $tv) {
			$return .= $outglue . $tk . $inglue . $tv;
		}
	
		return substr($return, strlen($outglue));
	}
	
	public static function is_assoc_array($array){
		$bla = ARRAY_KEYS($array);
		$bla = ARRAY_SHIFT($bla);
		if(IS_ARRAY($array) && !IS_NUMERIC($bla)){
			return true;
		}
		return false;
	}
	
	/**
	 *
	 * HELPER methods -------------------------------------
	 */
	
}
