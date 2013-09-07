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
 * kcaptcha hooks
 *
 * @package		CoGear
 * @subpackage	kcaptcha
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Set form class method for kcaptcha
	*
	* @param	object
	* @param	string
	* @param	mixed
	* @return	void
	*/
	function form_kcaptcha_($Form,$name,$config = FALSE){
		$config['type'] = 'kcaptcha';
		$CI =& get_instance();
		$config['label'] = t('kcaptcha.label');
		$config['description'] = t('kcaptcha.description');
		$config['image'] = '<img src="/kcaptcha/" border="0">';
		$config['validation'] = 'required|kcaptcha';
		$config['js_validation'] = 'required';
		$config['template'] = GEARS."kcaptcha/templates/kcaptcha.tpl";
		$Form->add($name,$config);
	}
	// ------------------------------------------------------------------------

	/**
	*  Add kcaptcha if form id is in config
	*
	* @param	object
	* @return	void
	*/
	function kcaptcha_form_result_($Form){
		$CI =& get_instance();
		if($CI->gears->kcaptcha->mistakes && $CI->session->get('form_fail') <= (int)$CI->gears->kcaptcha->mistakes) return;
		if(in_array($Form->name,explode(',',$Form->gears->kcaptcha->forms))){
			$Form->kcaptcha('kcaptcha');
		}
	}
	// ------------------------------------------------------------------------

	/**
	*  Set validation method
	*
	* @param	string
	* @return	boolean
	*/
	function kcaptcha($kcaptcha){
		$CI =& get_instance();
		if($CI->session->get('kcaptcha') == $kcaptcha){
			return TRUE;
		}
		else {
			$CI->form_validation->set_message('kcaptcha', t('kcaptcha wrong'));
			return FALSE;
		}
		
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------