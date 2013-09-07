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
 * Video hooks
 *
 * @package		CoGear
 * @subpackage	Video
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add video button to editor
	*
	* @param	object
	* @return	void
	*/
	function video_editor_compile_after_($Editor){
		if(acl('editor video')){
			js('/gears/video/js/inline/video.button',FALSE,TRUE);
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Add parser rule
	*
	* @param	object
	* @return	void
	*/
	function video_parser_construct_($Parser){
		if(!acl('editor video')){
			array_insert($Parser->prepare['textarea'],'trim_video',4);
			array_insert($Parser->prepare['comment'],'trim_video',4);
		}
		array_insert($Parser->process['textarea'],'parse_video',4);
		array_insert($Parser->process['comment'],'parse_video',4);
	}
	// ------------------------------------------------------------------------

	/**
	* Trim video
	*
	* @param	string
	* @return	string
	*/
	function trim_video($value){
		$value = preg_replace('/[\[|\<]video=?(.[^\]]*)?[\]|\>](.[^\[]+)[\[|\<]\/video[\]|\>]/ism','',$value);
		$value = preg_replace('/[\[|\<]video=?(.[^\]]*)?[\]|\>]/ism','',$value);
		return $value;
	}


	/**
	* Parse video
	*
	* @param	string
	* @return	string
	*/
	function parse_video($value){
		$value = str_ireplace(array('[video]', '[/video]'), array('[media]', '[/media]'), $value);
		$CI =& get_instance();
		$CI->load->library('video Autoembed', 'autoembed');
		$value = $CI->autoembed->embed($value);
		return $value;
	}

// ------------------------------------------------------------------------