<?php  if ( ! defined('COGEAR')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author			CodeMotion, Dmitriy Belyaev
 * @copyright		Copyright (c) 2009, CodeMotion
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * Cache class
 *
 * Store cache in files or memcached if it's installed on the server and works
 *
 * @package		CoGear
 * @subpackage	Cache
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
 class CI_Cache 
 {
	 // Cache state
	 public $enabled = FALSE;
	 // Driver instance
	 private $driver;
	 // Drivers
	 private $drivers = array();
	 // Queries counter
	 public $counter = 0;
	 
	 /**
	 * Constructor
	 */
	 function __construct(){
		 require_once(ENGINE.'libraries/drivers/Cache.php');
		 $this->select('files');
		 if(class_exists('Memcache')) $this->select('memcache');
	 }
	 
	 /**
	 * Select driver
	 *
	 * @param	string	Driver.
	 * @return	mixed
	 */
	 function select($driver = 'files'){
		 $file = ENGINE.'libraries/drivers/cache/'.ucfirst($driver).EXT;
		 if(file_exists($file)){
			 require_once($file);
			 $class = ucfirst($driver).'Cache';
			 if(class_exists($class) && !isset($this->drivers[$driver])){
				 $this->drivers[$driver] = new $class();
				 $this->driver =& $this->drivers[$driver];
			 }
		 }
		 return $this->driver;
	 }
	 
	 /**
	 * Choose driver
	 *
	 * @param	string
	 */
	 function driver($name = 'files'){
		return isset($this->drivers[$name]) ? $this->drivers[$name] : FALSE;
	 }
	 
	 /**
	 * Method call
	 *
	 * @param	string	Name.
	 * @param	array		Arguments.
	 * @return	mixed
	 */
	 function __call($name, $args){
		 if(method_exists($this->driver,$name)){
			 if($name == 'get' && count($args) == 1){
				 ++$this->counter;
				 if(!$this->enabled) return FALSE;
			 }
			 return call_user_func_array(array($this->driver,$name),$args);
		 }
		 else return FALSE;
	 }
 }
 
/**
* Store data to Storage
*
* @param	string	Key.
* @param	mixed	Value.
* @param	integer	Time to live.
* @param	array		Tags.	
* @return	void
*/
function store($name,$value,$ttl = FALSE,$tags = FALSE){
	$CI =& get_instance();
	if($CI->cache->driver('memcache')) $CI->cache->driver('memcache')->set('storage/'.$name,$value,$ttl,$tags);
	else $CI->cache->driver('files')->set('storage/'.$name,$value,$ttl,$tags);
}
// ------------------------------------------------------------------------

/**
* Retrieve data from Storage
*
* @param	string	Key.
* @return	mixed
*/
function retrieve($name){
	$CI =& get_instance();
	//&& $CI->cache->driver('files')->check('storage/'.$name)
	if($CI->cache->driver('memcache')){
		return $CI->cache->driver('memcache')->get('storage/'.$name,TRUE);
	}
	return $CI->cache->driver('files')->get('storage/'.$name,TRUE);
}
// ------------------------------------------------------------------------

/**
* Remove data from Storage
*
* @param	string	Key
* @return	void
*/
function remove($name){
	$CI =& get_instance();
	$name = str_replace('*','',$name);
	$CI->cache->driver('files')->clear('storage/'.$name);	
}