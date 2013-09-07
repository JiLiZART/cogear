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
 * Common functions
 *
 * @package		CoGear
 * @subpackage	Meta
 * @category		Gears libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add meta data
	*
	* @param	mixed
	* @param	string
	* @param	boolean
	* @return	void
	*/
	function meta($data,$type = 'keywords',$rewrite = FALSE){
		$CI =& get_instance();
		switch($type){
			case 'keywords':
				if(!is_array($data)) $data = _explode(',',$data);
				foreach($data as $key=>$item){
					$data[$key] = trim($item);
				}
				if($rewrite) $CI->content['meta']['keywords'] = implode(',',$data);
				else {
					$old = _explode(',',$CI->content['meta']['keywords']);
					$data = array_merge($old,$data);
					shuffle($data);
					$CI->content['meta']['keywords'] = implode(', ',array_unique($data));
				}
			break;
			case 'description':
				if($rewrite) $CI->content['meta']['description'] = $data;
				else $CI->content['meta']['description'] = rtrim($data,'.!?').'. '.$CI->content['meta']['description'];
			break;
		}
		return TRUE;
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------