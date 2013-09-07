<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Files class
*
* @package		CoGear
* @author		CodeMotion, Dmitriy Belyaev
* @copyright	Copyright (c) 2009, CodeMotion
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @since			Version 1.0
* @filesource
*/
class FilesCache extends Cache
{
	// Path to store
	private $path = './engine/cache/';
	// Files extension
	private $ext = '.txt';
	
	/**
	* Constructor
	*
	* @param	array	Configuration
	*/
	function __construct($config = FALSE){
		parent::__construct($config);
		if(!file_exists($this->path)) mkdir($this->path,0777,TRUE);
	}
	
	/**
	* Write cache
	*
	* @param	string	Key.
	* @param	string	Value.
	* @return	boolean	Result.
	*/
	protected function write($key,$value){
		$file = $key.$this->ext;
		$dir = dirname($this->path.$file);
		if(!file_exists($dir)) @mkdir($dir,0777,TRUE);		
		return file_put_contents($this->path.$file,$value);
	}
	
	/**
	* Read cache
	*
	* @param	string	Key.
	* @return	mixed	Value.
	*/
	protected function read($key){
		$file = $key.$this->ext;
		if(file_exists($this->path.$file)){
			return file_get_contents($this->path.$file);
		}
		return FALSE;
	}
	
	/**
	* Remove cache key.
	*
	* @param	string	Key.
	* @return	boolean
	*/
	protected function remove($key){
		$file = $key.$this->ext;
		if(file_exists($this->path.$key.$this->ext)){
			return unlink($this->path.$key.$this->ext);
		}
		elseif(is_dir($this->path.$key)){
			return rmdir_recurse($this->path.$key);
		}
		return FALSE;
	}
	
	/**
	* Flush all data.
	*
	* @return	boolean
	*/
	public function flush(){
		return rmdir_recurse($this->path);
	}	
	
	/**
	* Check cache elem for existance
	*
	* @param	string	Key
	* @return	boolean
	*/
	function check($key){
		return file_exists($this->path.$key.$this->ext) ? TRUE : FALSE;
	}
}