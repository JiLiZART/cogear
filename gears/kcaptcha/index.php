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
 * KCaptcha controller
 *
 * @package		CoGear
 * @subpackage	KCaptcha
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
	}
	// ------------------------------------------------------------------------

	/**
	* Genereate new KCaptcha
	*
	* @return	json
	*/
	function index(){
			$this->session->set('kcaptcha',$this->kcaptcha->getKeyString());
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------