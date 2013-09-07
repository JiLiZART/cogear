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
 * Captcha hooks
 *
 * @package		CoGear
 * @subpackage	Captcha
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Set form class method for captcha
	*
	* @param	object
	* @param	string
	* @param	mixed
	* @return	void
	*/
	function form_captcha_($Form,$name,$config = FALSE){
		$config['type'] = 'captcha';
		$CI =& get_instance();
		$config['label'] = t('captcha.label');
		$config['description'] = t('captcha.description');
		$config['image'] = $CI->captcha->init();
		$config['validation'] = 'captcha';
		$config['js_validation'] = 'required';
		$config['template'] = GEARS."captcha/templates/captcha.tpl";
		$Form->add($name,$config);
	}
	// ------------------------------------------------------------------------

	/**
	*  Add captcha if form id is in config
	*
	* @param	object
	* @return	void
	*/
	function captcha_form_result_($Form){
		if($CI->gears->captcha->mistakes && $CI->session->get('form_fail') <= (int)$CI->gears->captcha->mistakes) return;
		if(in_array($Form->name,explode(',',$Form->gears->captcha->forms))){
			$Form->captcha('captcha');
		}
	}
	// ------------------------------------------------------------------------

	/**
	*  Set validation method
	*
	* @param	string
	* @return	boolean
	*/
	function captcha($captcha){
		$CI =& get_instance();
		if(isset($CI->session->get('captcha')->word) && $CI->session->get('captcha')->word == $captcha){
			return TRUE;
		}
		else {
			$CI->form_validation->set_message('captcha', t('!captcha wrong'));
			$captcha = $CI->form->find('captcha');
			$captcha['image'] = $CI->captcha->init(TRUE);
			$CI->form->elements[$captcha['key']] = $captcha;
			return FALSE;
		}
		
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------