<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package	CoGear
 * @author		CodeMotion, Dmitriy Belyaev
 * @copyright	Copyright (c) 2009, CodeMotion
 * @license		http://cogear.ru/license.html
 * @link			http://cogear.ru
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Debug Functions
 *
 * Simple functionality for developers.
 *
 * @package		CoGear
 * @subpackage	core
 * @category		Debug
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
 
/**
* Debug Handler
*
* Easliy view preformatted output for arrays && objects
*
* @access	public
* @return	void
*/
function debug(){
	if(function_exists('get_instance')){
		$CI =& get_instance();
	}
	// catching args
    $args = func_get_args();
    // sending header quietly
    // CoGear encoding is UTF-8 and if server isn't - it would have to show UTF-8 
    @header('Content-Type:text/html; charset=UTF-8',TRUE);
    // Pre for preformatted text
    echo '<pre>';
    if(count($args) > 1){
		// If last arg is bool
		// Example: debug($array,$object,TRUE);
		if(is_bool(end($args))){
			$print_r = array_pop($args);
			foreach($args as $arg){
				$print_r ? print_r($arg) : var_dump($arg);
				echo '<hr>';
			}
			if($print_r) die();
		}
		else var_dump($args[0]); echo '<hr>';
    }
    // If there is only one argument - var_dump it and die
    else {
		var_dump($args[0]); 
		die();
	}
}
// ------------------------------------------------------------------------