<?php
namespace ComposerPack\System\Provider;

/**
 * Provider
 * @author Nagy Gergely info@nagygergely.eu 2014
 * @version 0.1
 *
 */
interface ProviderInterface
{
	
	/**
	 * Get one data tag from the list
	 * @param unknown $id
	 * @return one value from the list
	 */
	public function get($id);
	
	/**
	 * @return all value from the list 
	 */
	public function getAll();

	/**
	 * set one value with id
	 * @param unknown $value
     * @param unknown $id
	 * @param unknown $type
	 */
	public function set($value,$id=null,$type = null);

	/**
	 * reset the provider
	 */
	public function clear();
}