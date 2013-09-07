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
 * Captcha controller
 *
 * @package		CoGear
 * @subpackage	Captcha
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller {
	/**
	*  Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		$this->load->model('captcha captcha');
	}
	// ------------------------------------------------------------------------

	/**
	* Genereate new captcha
	*
	* @return	json
	*/
	function index(){
			$captcha = $this->captcha->init(TRUE);
			ajax($captcha,TRUE);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------