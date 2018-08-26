<?

/* GLOBAL *************************************/

if ( ! function_exists('lang')) {
    function lang($name = null, $lang = null, array $langs = array())
    {
        return \ComposerPack\Module\Language\Language::lang($name, $lang, $langs);
    }
}

if ( ! function_exists('langs')) {
    function langs($name = null)
    {
        return \ComposerPack\Module\Language\Language::langs($name);
    }
}

if ( ! function_exists('get')) {
    function get($name)
    {
        return \ComposerPack\System\Settings::get($name);
    }
}

if ( ! function_exists('set')) {
    function set($name, $value)
    {
        return \ComposerPack\System\Settings::set($name, $value);
    }
}

if ( ! function_exists('url')) {
    function url()
    {
        return call_user_func_array(['ComposerPack\\System\\Url', 'url'], func_get_args());
    }
}

if ( ! function_exists('seo')) {
    function seo()
    {
        return call_user_func_array(['ComposerPack\\System\\Url', 'SEO'], func_get_args());
    }
}
if ( ! function_exists('url_seo')) {
    function url_seo()
    {
        return url(call_user_func_array('seo', func_get_args()));
    }
}
if( ! class_exists('Session')) {
    class Session extends \ComposerPack\System\Session\Session {

    }
}
/* GLOBAL *************************************/

if ( ! function_exists('unparse_url')) {
    function unparse_url($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? $pass . '@' : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
    }
}


if ( ! function_exists('array_pluck'))
{
	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return array
	 */
	function array_pluck($array, $key)
	{
		return array_map(function($value) use ($key)
		{
			return is_object($value) ? $value->$key : $value[$key];
		}, $array);
	}
}
/**
 * Recursively delete a directory that is not empty
 * Original source: http://hu1.php.net/manual/en/function.rmdir.php#98622
 *
 * @param string $dir
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}
function is_iterable($var) {
	return (is_array($var) || $var instanceof Traversable);
}
/*
 * Convert seconds to human readable text.
 *
 */
function secs_to_h($secs)
{
	$units = array(
			"week"   => 7*24*3600,
			"day"    =>   24*3600,
			"hour"   =>      3600,
			"minute" =>        60,
			"second" =>         1,
	);

	// specifically handle zero
	if ( $secs == 0 ) return "0 seconds";

	$s = "";

	foreach ( $units as $name => $divisor ) {
		if ( $quot = intval($secs / $divisor) ) {
			$s .= "$quot $name";
			$s .= (abs($quot) > 1 ? "s" : "") . ", ";
			$secs -= $quot * $divisor;
		}
	}

	return substr($s, 0, -2);
}
function secs_to_v($secs)
{
	$units = array(
			"weeks"   => 7*24*3600,
			"days"    =>   24*3600,
			"hours"   =>      3600,
			"minutes" =>        60,
			"seconds" =>         1,
	);

	foreach ( $units as &$unit ) {
		$quot  = intval($secs / $unit);
		$secs -= $quot * $unit;
		$unit  = $quot;
	}

	return $units;
}
/**
 * This function is a proper replacement for realpath
 * It will _only_ normalize the path and resolve indirections (.. and .)
 * Normalization includes:
 * - directiory separator is always /
 * - there is never a trailing directory separator
 * @param  $path
 * @return String
 */
function normalizepath($path) {
    $parts = preg_split(":[\\\/]:", $path); // split on known directory separators

    // resolve relative paths
    for ($i = 0; $i < count($parts); $i +=1) {
        if ($parts[$i] === "..") {          // resolve ..
            if ($i === 0) {
                throw new Exception("Cannot resolve path, path seems invalid: `" . $path . "`");
            }
            unset($parts[$i - 1]);
            unset($parts[$i]);
            $parts = array_values($parts);
            $i -= 2;
        } else if ($parts[$i] === ".") {    // resolve .
            unset($parts[$i]);
            $parts = array_values($parts);
            $i -= 1;
        }
        if ($i > 0 && $parts[$i] === "") {  // remove empty parts
            unset($parts[$i]);
            $parts = array_values($parts);
        }
    }
    return implode(DIRECTORY_SEPARATOR, $parts);
}
function implode_key($glue = "", $pieces = array()) {
	$arrK = array_keys($pieces);
	return implode($glue, $arrK);
}
function implode_with_key($assoc, $inglue = '>', $outglue = ',') {
	$return = '';

	foreach ($assoc as $tk => $tv) {
		$return .= $outglue . $tk . $inglue . $tv;
	}

	return substr($return, strlen($outglue));
}
function is_assoc_array($array){
	return !ctype_digit( implode('', array_keys($array) ) );
}
function startsWith($haystack, $needle)
{
	return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}
if (!function_exists('getallheaders'))
{
	function getallheaders()
	{
		$headers = '';
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}
function getwords($string, $start = 0, $count = 5)
{
	$pieces = preg_split("#\s+#", $string);
	//$first_part = implode(" ", array_splice($pieces, 0, 5));
	//$other_part = implode(" ", array_splice($pieces, 5));
	return implode(" ", array_splice($pieces, $start, $count));
}
function reArrayFiles(&$file_post) {

	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);

	for ($i=0; $i<$file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}

	return $file_ary;
}
function diverse_array($vector) {
	$result = array();
	foreach($vector as $key1 => $value1)
	{
		foreach($value1 as $key2 => $value2)
		{
			$result[$key2][$key1] = $value2;
		}
	}
	return $result;
}
function restructure_files(array &$input)
{
	$output = [];
	foreach ($input as $name => $array) {
		foreach ($array as $field => $value) {
			$pointer = &$output[$name];
			if (!is_array($value)) {
				$pointer[$field] = $value;
				continue;
			}
			$stack = [&$pointer];
			$iterator = new \RecursiveIteratorIterator(
					new \RecursiveArrayIterator($value),
					\RecursiveIteratorIterator::SELF_FIRST
					);
			foreach ($iterator as $key => $value) {
				array_splice($stack, $iterator->getDepth() + 1);
				$pointer = &$stack[count($stack) - 1];
				$pointer = &$pointer[$key];
				$stack[] = &$pointer;
				if (!$iterator->hasChildren()) {
					$pointer[$field] = $value;
				}
			}
		}
	}
	$input = $output;
}
function fixFilesArray(&$files)
{
	$names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

	foreach ($files as $key => $part) {
		// only deal with valid keys and multiple files
		$key = (string) $key;
		if (isset($names[$key]) && is_array($part)) {
			foreach ($part as $position => $value) {
				$files[$position][$key] = $value;
			}
			// remove old key reference
			unset($files[$key]);
		}
	}
}
function fixFiles(&$files)
{
	if (!empty($files))
	{
		foreach($files as $name => &$file)
		{
			$names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);
			$ok = true;
			foreach($names as $name => $v)
			{
				if(!isset($file[$name]))
					$ok = false;
			}
			if($ok)
			{
				$this->fixFilesArray($file);
				$files[$name] = $file;
			}
			else
			{
				$this->fixFiles($file);
			}
		}
	}
}
function is_class_a($a, $b)
{
	if(is_object($a) && is_object($b))
		return $a == $b || is_subclass_of($a, get_class($b)) || get_class($a) == get_class($b);
	elseif(!is_object($a) && is_object($b))
		return is_subclass_of($b, $a) || $a == get_class($b);
	elseif(is_object($a) && !is_object($b))
		return is_subclass_of($a, $b) || $b == get_class($a);
}
function array_remove_empty_recursive($haystack)
{
	foreach ($haystack as $key => $value) {
		if (is_array($value)) {
			$haystack[$key] = array_remove_empty_recursive($haystack[$key]);
		}

		if (empty($haystack[$key])) {
			unset($haystack[$key]);
		}
	}

	return $haystack;
}
function can_be_string($var) {
	return $var === null || is_scalar($var) || is_callable([$var, '__toString']);
}
/*
 * Get the directory size
 * @param directory $directory
 * @return integer
 */
function get_dir_size($directory) {
	$size = 0;
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CATCH_GET_CHILD) as $file) {
		if($file->isReadable())
		{
			$size += $file->getSize();
		}
	}
	return $size;
}
function get_dir_size_formated($dir, $precision = 2) {
	$ritit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CATCH_GET_CHILD);
	$bytes = 0;
	foreach ( $ritit as $v ) {
		if($v->isReadable())
		{
			$bytes += $v->getSize();
		}
	}
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . ' ' . $units[$pow];
}
function get_space($dir)
{
	$size = disk_total_space($dir);
	$used = get_dir_size($dir);
	return array('size' => $size, 'used' => $used, 'free' => $size-$used);
}
function get_formated_size($bytes, $precision = 2)
{
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . ' ' . $units[$pow];
}
function mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);
}
function sentence_case($string) {
	$sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	$new_string = '';
	foreach ($sentences as $key => $sentence) {
		$new_string .= ($key & 1) == 0?
		ucfirst(strtolower(trim($sentence))) :
		$sentence.' ';
	}
	return trim($new_string);
}
function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
/*function recurse_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				recurse_copy($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}*/
// http://ben.lobaugh.net/blog/864/php-5-recursively-move-or-copy-files
/**
 * Recursively move files from one directory to another
 *
 * @param String $src - Source of files being moved
 * @param String $dest - Destination of files being moved
 */
function rmove($src, $dest){

	// If source is not a directory stop processing
	if(!is_dir($src)) return false;

	// If the destination directory does not exist create it
	if(!is_dir($dest)) {
		if(!mkdir($dest)) {
			// If the destination directory could not be created stop processing
			return false;
		}
	}

	// Open the source directory to read in files
	$i = new DirectoryIterator($src);
	foreach($i as $f) {
		if($f->isFile()) {
			rename($f->getRealPath(), "$dest/" . $f->getFilename());
		} else if(!$f->isDot() && $f->isDir()) {
			rmove($f->getRealPath(), "$dest/$f");
			unlink($f->getRealPath());
		}
	}
	unlink($src);
}
/**
 * Recursively copy files from one directory to another
 *
 * @param String $src - Source of files being moved
 * @param String $dest - Destination of files being moved
 */
function rcopy($src, $dest){

	// If source is not a directory stop processing
	if(!is_dir($src)) return false;

	// If the destination directory does not exist create it
	if(!is_dir($dest)) {
		if(!mkdir($dest)) {
			// If the destination directory could not be created stop processing
			return false;
		}
	}

	// Open the source directory to read in files
	$i = new DirectoryIterator($src);
	foreach($i as $f) {
		if($f->isFile()) {
			copy($f->getRealPath(), "$dest/" . $f->getFilename());
		} else if(!$f->isDot() && $f->isDir()) {
			rcopy($f->getRealPath(), "$dest/$f");
		}
	}
}

function get_base_class($object)
{
    $reflect = new ReflectionClass($object);
    return $reflect->getShortName();
}

function get_namespace($object)
{
    $reflect = new ReflectionClass($object);
    return $reflect->getNamespaceName();
}

if ( ! function_exists('object_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if (is_object($array))
            {
                $array = $array[$segment];
            }
            else
            {
                return value($default);
            }
        }

        return $array;
    }
}