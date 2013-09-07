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
 * Captcha CP controller
 *
 * @package		CoGear
 * @subpackage	Captcha
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller{
	/**
	*  Constructor
	*
	* @return	void
	*/
	function _Admin(){
		parent::Controller();
		d('captcha');
	}
	// ------------------------------------------------------------------------

	/**
	* Edit captcha config
	*
	* @return	void
	*/
	function index(){
		$this->form->set('captcha_settings','centered')
		->title(t('!global settings'),FALSE,FALSE,TRUE)
		->fieldset('capthca')
		->textarea('words')
		->input('forms')
		->fieldset()
		->buttons('save');
		if($result = $this->form->result()){
			$this->info->set(GEARS.'captcha/captcha')->change($result)->compile();
			msg('!form saved');
			$this->form->set_values($result);
		}
		else {
			$this->form->set_values($this->gears->captcha)	;
		}
		$this->form->compile();
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------