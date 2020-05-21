<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Cache Base class
*
* @package		CoGear
* @author		CodeMotion, Dmitriy Belyaev
* @copyright	Copyright (c) 2009, CodeMotion
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @since			Version 1.0
* @filesource
*/
abstract class Cache{
	 // Time to live
	 protected $ttl = FALSE;
	 // Tags ttl
	 protected $tags_ttl = 5;
	 // Cache prefix
	 protected $prefix = 'cache_prefix_';
	 // Tags
	 private $tags = array();
	 
	 /**
	 * Constructor
	 *
	 * @param	array	$config
	 */
	 protected function __construct($config = FALSE){
		 $this->prefix = $_SERVER['SERVER_NAME'].'/';
		 if($config && is_array($config)){
			 foreach($config as $key=>$value){
				 $this->$key = $value;
			 }
		 }
	 }
	 
	/**
	* Set tags
	*
	* @param	mixed	Tags
	* @return	object	this
	*/
	function tags($tags){
		if(is_string($tags)){
			$tags = explode(',',str_replace(' ','',$tags));
		}
		$this->tags = $tags;
		return $this;
	}
	
	/**
	* Set cache key.
	*
	* @param	string	Key.
	* @param	mixed	Value.
	* @param	integer	Time to live.
	* @param	mixed	Tags.
	* @return	boolean
	*/
	public function set($key,$value,$ttl = FALSE,$tags = FALSE){
			$ttl = time() + ($ttl ? $ttl : ($this->ttl ? $this->ttl : time() + 2592000));
			if(!$tags){
			 $tags = $this->tags;
			 $this->tags = array();
			}
			$data = array(
			'value' => @serialize($value),
			'ttl' =>  $ttl,
			'tags' => $tags ? $this->prepare_tags($tags,$ttl) : FALSE
			);
			$data = @serialize($data);
			$this->write($key,$data);
	}
	
	/**
	* Get cache key.
	*
	* @param	string	Key.
	* @return	mixed	Value.
	*/
	public function get($key){
			if($data = $this->read($key)){
				if($data = @unserialize($data)){
					if(isset($data['ttl']) && time() > $data['ttl']) return FALSE;
					if(isset($data['tags']) && $data['tags'] && $tags = @unserialize($data['tags'])){
						foreach($tags as $tag){
							if($tag_ttl = $this->read('tags/'.$tag)){
								if($tag_ttl > $data['ttl'] + $this->tags_ttl) return FALSE;
							}
							else return FALSE;
						}
					}
					return @unserialize($data['value']);
				}
			}
			return FALSE;
	}
	
	/**
	* Clear cache by key
	*
	* @param	string	Key.
	* @return	boolean	Result.
	*/
	public function clear($key = FALSE){
		if(!$key){
		 if($this->tags){
			 foreach($this->tags as $tag){
				 $this->remove('tags/'.$tag);
			 }
			 $this->tags = array();
			 return;
		 }
		 else {
			 return $this->flush();
		 }
		}
		else return $this->remove($key);
	}
	
	
	/**
	* Prepare tags
	*
	* @param	mixed	Tags.
	* @param	integet	Time to live.
	* @return	string	Serialized data		
	*/
	private function prepare_tags($tags,$ttl){
		if(is_string($tags)){
			// remove all whitespaces and explode via comma
			$tags = explode(',',str_replace(' ','',$tags));
		}
		elseif(!is_array($tags)){
			$tags = (array)$tags;
		}
		foreach($tags as $tag){
			$this->write('tags/'.$tag,$ttl);
		}
		return @serialize($tags);		
	}
	
	/**
	* Write cache.
	*/
	abstract protected function write($key,$value);
	
	/**
	* Read cache.
	*/
	abstract protected function read($key);
	
	/**
	* Remove cache key.
	*/
	abstract protected function remove($key);
	
	/**
	* Flush all keys cache.
	*/
	abstract protected function flush();
	
	/**
	* Check key
	*/
	abstract protected function check($key);
}