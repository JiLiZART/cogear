<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Memcache class
*
* @package		CoGear
* @author		CodeMotion, Dmitriy Belyaev
* @copyright	Copyright (c) 2009, CodeMotion
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @since			Version 1.0
* @filesource
*/
class MemcacheCache extends Cache
{
	// Memcache instance
	private $memcache;
	// Memcache host
	private $host = 'localhost';
	// Memcache port
	private $port = 11211;
	
	/**
	* Constructor
	*
	* @param	array	Configuration
	*/
	function __construct($config = FALSE){
	  parent::__construct($config);
        $this->memcache = new Memcache;
        @$this->memcache->pconnect($this->host, $this->port);
	}
	
	/**
	* Write cache
	*
	* @param	string	Key.
	* @param	string	Value.
	* @return	boolean	Result.
	*/
	protected function write($key,$value){
		return @$this->memcache->set($this->prefix.$key,$value);
	}
	
	/**
	* Read cache
	*
	* @param	string	Key.
	* @return	mixed	Value.
	*/
	protected function read($key){
		return @$this->memcache->get($this->prefix.$key);
	}
	
	/**
	* Remove cache key.
	*
	* @param	string	Key.
	* @return	boolean
	*/
	protected function remove($key){
		return @$this->memcache->delete($this->prefix.$key);
	}
	
	/**
	* Flush all data.
	*
	* @return	boolean
	*/
	public function flush(){
		return @$this->memcache->flush();
	}	
	
	/**
	* Check cache elem for existance
	*
	* @param	string	Key
	* @return	boolean
	*/
	public function check($key){
		return @$this->memcache->get($key) ? TRUE : FALSE;
	}
}