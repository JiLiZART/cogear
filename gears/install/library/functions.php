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
 * Common functions for installer
 *
 * @package		CoGear
 * @subpackage	Install
 * @category		Gears libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Copy directories recursively
	*
	* @param	string
	* @param	string
	* @param	int
	* @return	void
	*/
	function cp($src,$dst) {
	    $dir = opendir($src);
	    @mkdir($dst,0777,TRUE);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . '/' . $file) ) {
	                cp($src . '/' . $file,$dst . '/' . $file);
	            }
	            elseif(file_exists($src . '/' . $file)) {
	                copy($src . '/' . $file,$dst . '/' . $file);
	            }
	        }
	    }
	    closedir($dir);
	} 

	/**
	* Remove directories recursively
	*
	* @param	string
	* @param	boolean
	* @return	void
	*/
	function rm($dir, $deleteRootToo = FALSE)
	{
		if(!$dh = @opendir($dir))
		{
			return;
		}
		while (false !== ($obj = readdir($dh)))
		{
			if($obj == '.' || $obj == '..')
			{
				continue;
			}
	
			if (!@unlink($dir . '/' . $obj))
			{
			   rm($dir.'/'.$obj, true);
			}
		}
	
		closedir($dh);
	   
		if ($deleteRootToo)
		{
			@rmdir($dir);
		}
	   
		return;
	} 
	// ------------------------------------------------------------------------
	
	/**
	 * Parse code and replace with prefix
	 *
	 * @param	string	$dump
	 * @param	string	$prefix
	 * @return	string
	 */
	function parse_db_prefix($dump,$prefix = ''){
		if(!$prefix){
			$CI =& get_instance();
			$prefix = $CI->db->dbprefix;
		}
		$dump = preg_replace('#((TABLE|EXISTS|INTO)\s+`)#imsU','\1'.$prefix,$dump);
		return $dump;
	}
// ------------------------------------------------------------------------