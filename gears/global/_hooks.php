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
 * Global hooks
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Set meta data and process js/css to assets model
	*
	* @param	object
	* @return	void
	*/
	function global_header($CI){
		if($CI->site->debug && $CI->user->get('user_group') == 1 && !isset($CI->no_profiler)){
		 $CI->output->enable_profiler(TRUE);
		}
		if($CI->name != 'admin' && !$CI->site->url OR trim($CI->site->url) == ''){
			$CI->site->url = $_SERVER['SERVER_NAME'];
		}
		$CI->content['scripts'] = '';
		$CI->content['css'] = '';
	}
	// ------------------------------------------------------------------------

	/**
	* Show messages, set scripts and css
	*
	* @param	object	$CI
	* @return	void			
	*/
	function global_after($CI){
		 $js = array_filter(glob(GEARS.'*/js/*.js'),array($CI->load,'filter'));
		 $CI->assets->add($js);
		 $css = array_filter(glob(GEARS.'*/css/*.css'),array($CI->load,'filter'));
		 $CI->assets->add($css);
		 $CI->assets->add_dir('/templates/'.$CI->site->template.'/js/');
		 $CI->assets->add_dir('/templates/'.$CI->site->template.'/css/');
		 $CI->content['scripts'] .= $CI->assets->output("js");
		 $CI->content['css'] .= $CI->assets->output("css");
		 $CI->msg->render();
	}
	// ------------------------------------------------------------------------
	
	/**
	* Show mem_usage
	*
	* @param	object
	* @return	void
	*/
	function global_footer_after($CI){
	 $CI->content['mem_usage'] = round(memory_get_usage()/1024/1024,2)."Мб"; 
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------