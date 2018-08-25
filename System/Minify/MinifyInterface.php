<?php
namespace ComposerPack\System\Minify;

/**
 * "Minify"káló interface ebből kéretik származtatni a minifikálókat!
 * @author Nagy Gergely info@nagygergely.eu 2014
 * @version 0.1
 *
 */
interface MinifyInterface
{
	/**
	 * Minify the file list
	 * @param array $files
	 */
	public static function minify(array $files = array());
	
}