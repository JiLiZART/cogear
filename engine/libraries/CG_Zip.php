<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Zip
 *
 * Extends base CI class to archive only current dir path, not the root path as it had to done before
 *
 * @package		CoGear
 * @subpackage	ZIP
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class CG_Zip extends CI_Zip{
	/**
	*  Constructor
	*
	* @return	void
	*/
	function CG_Zip(){
		parent::CI_Zip();
	}
	// ------------------------------------------------------------------------

	/**
	*  Read_dir
	*  Override default method, because it archive folders making making path from the root
	*
	* @param	string	$path
	* @param	boolean	$folders
	* @param	string	$route
	* @return	void
	*/
	function read_dir($path,$folders = FALSE, $route = FALSE)
	{	
		if(!$folders){
			$CI =& get_instance();
			$CI->load->helper('directory');
			$folders = directory_map($path);
			$route = trim(basename($path),'./').'/';
		}
		if(!is_array($folders)) return FALSE;
		foreach($folders as $key=>$item){
			if(is_numeric($key)){
				$this->add_data(str_replace("\\", "/", $route).$item, file_get_contents(dirname($path).'/'.$route.$item));
			}
			else if(is_string($key) && is_dir(dirname($path).'/'.$route.$key.'/')) {
				$this->read_dir($path,$item,$route.$key.'/');
			}
		}
		return TRUE;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------
