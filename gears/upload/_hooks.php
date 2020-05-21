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

/**
 *  Upload hooks
 *
 * @package		CoGear
 * @subpackage	Upload
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add image upload button
	*
	* @param	object
	* @return	void
	*/
	function upload_editor_compile_after_($Editor){
		static $image_update_initialized = FALSE;
		if(!$image_update_initialized && acl('upload images')){
			js('/gears/upload/js/inline/fileuploader',FALSE,TRUE);
			css('/gears/upload/css/inline/fileuploader',FALSE,TRUE);
			js('/gears/upload/js/inline/upload',FALSE,TRUE);
			$image_update_initialized = TRUE;
			$CI =& get_instance();
			d('upload_image');
			//js('/gears/upload/js/inline/upload',FALSE,TRUE);

/*
			$CI->form->set('image-link-form','hidden')->
			textarea('links')->
			buttons('upload')
			->compile();
*/
		}
	}
