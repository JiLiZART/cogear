<?php
/**
* Install hooks.
*
* @author			Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2010, Dmitriy Belyeav
* @license			http://cogear.ru/license.html
* @link				http://cogear.ru
* @package			Install
* @subpackage		Hooks
* @version			$Id$
*/

/**
 * Show new patches on the main Control Panel page
 */
function check_patch_filter($file){
	if($content = file_get_contents($file)){
		return strpos($content,'-- Patched') === FALSE ? TRUE : FALSE;
	}
	return FALSE;
} 
function install_admin_index(){
	if($patches = array_filter(glob(GEARS.'*/patch.sql'),'check_patch_filter')){
		$patches = array_map(create_function('$a','return basename(dirname($a));'),$patches);
		$CI =& get_instance();
		$CI->_template('install patches',array('gears'=>$patches));	
	}
}