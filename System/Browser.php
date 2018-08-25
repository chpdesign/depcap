<?php
namespace ComposerPack\System;

/**
 * Browser osztály
 * @see https://github.com/cbschuld/Browser.php
 * @author Nagy Gergely <info@nagygergely.eu>
 *
 */
class Browser {
	
	private $_urls = array();
	private $_current = 0;
	private static $_self;
	/**
	 * browser
	 * @var \Browser
	 */
	private $_client = null;
	
	private function getBrowser() 
	{ 
	    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";
	
	    //First get the platform?
	    if (preg_match('/linux/i', $u_agent)) {
	        $platform = 'linux';
	    }
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'mac';
	    }
	    elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'windows';
	    }
	    
	    // Next get the name of the useragent yes seperately and for good reason
	    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Internet Explorer'; 
	        $ub = "MSIE"; 
	    } 
	    elseif(preg_match('/Firefox/i',$u_agent)) 
	    { 
	        $bname = 'Mozilla Firefox'; 
	        $ub = "Firefox"; 
	    } 
	    elseif(preg_match('/Chrome/i',$u_agent)) 
	    { 
	        $bname = 'Google Chrome'; 
	        $ub = "Chrome"; 
	    } 
	    elseif(preg_match('/Safari/i',$u_agent)) 
	    { 
	        $bname = 'Apple Safari'; 
	        $ub = "Safari"; 
	    } 
	    elseif(preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Opera'; 
	        $ub = "Opera"; 
	    } 
	    elseif(preg_match('/Netscape/i',$u_agent)) 
	    { 
	        $bname = 'Netscape'; 
	        $ub = "Netscape"; 
	    } 
	    
	    // finally get the correct version number
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    $matches = array();
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	        // we have no matching number just continue
	    }
	    
	    // see how many we have
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version= $matches['version'][0];
	        }
	        else {
	            $version= $matches['version'][1];
	        }
	    }
	    else {
	        $version= $matches['version'][0];
	    }
	    
	    // check if we have a number
	    if ($version==null || $version=="") {$version="?";}
	    
	    return array(
	        'userAgent' => $u_agent,
	        'name'      => $bname,
	        'version'   => $version,
	        'platform'  => $platform,
	        'pattern'    => $pattern
	    );
	} 
		
	
	private function __construct()
	{

	}
	
	private function currentUrl()
	{
		if(!Controller::isAjax())
		{
			$r = array('url' => Controller::$ACTIVE_URL, 'post' => $_POST, 'get' => $_GET, 'request' => $_REQUEST);
			$id = md5(json_encode($r));
			$keys = array_keys($this->_urls);
			end($this->_urls);
			$key = key($this->_urls);
			if($this->_current != $key)
			{
				$this->_urls = array_slice($this->_urls,0,array_search($id, $keys));
			}
			$this->_urls[$id] = $r;
			$this->_current = $id;
			//$this->_client = $this->getBrowser();//get_browser(null, true);
			$this->_client = new Browser();
		}
	}
	
	/**
	 * 
	 * @return Browser
	 */
	public function client()
	{
		return $this->_client;
	}
	
	public function current()
	{
		return $this->_urls[$this->_current];
	}
	
	public static function getInstance()
	{
		global $BrowserInit;
		if(static::$_self == null)
			static::$_self = new Browser();
		if($BrowserInit == false)
		{
			$BrowserInit = true;
			static::$_self->currentUrl();
		}
		return static::$_self;
	}
	
	/**
	 * Go back
	 * @param number $number how many? or back key
	 * @return String url or false
	 */
	public function back($number = 1)
	{
		if(is_numeric($number))
		{
			$keys = array_keys($this->_urls);
			if($number < 0) $number = $number*-1;
			$back = count($this->_urls)-($number-1);
		}
		else
		{
			$back = $number;
		}
		if(isset($keys[$back]))
		{
			$this->_current = $keys[$back];
			return $this->_urls[$keys[$back]];
		}
		return false;
	}
	
	/**
	 * Go forward
	 * @param number $number how many? or forward key
	 * @return String url or false
	 */
	public function forward($number = 1)
	{
		if(is_numeric($number))
		{
			$keys = array_keys($this->_urls);
			if($number < 0) $number = $number*-1;
			$forward = count($this->_urls)+($number-1);
		}
		else
		{
			$forward = $number;
		}
		if(isset($keys[$forward]))
		{
			$this->_current = $keys[$forward];
			return $this->_urls[$keys[$forward]];
		}
		return false;
	}
	
	public function __toString(){
		return 'ComposerPack\System\Browser';
	}
	
}