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
 * Parser controller
 *
 * @package		CoGear
 * @subpackage	Parser
 * @category		Gears  controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
	}
	// ------------------------------------------------------------------------

	/**
	* Show preview for textarea
	*
	* @return	json
	*/
	function preview(){
		if($body = $this->input->post('body')){
			$type = $this->input->post('pid') ? 'comment' : 'textarea';
			$result = $this->parser->prepare($body,$type);
			$result = $this->parser->parse($result,$type);
			ajax(TRUE,$result);
		}
		ajax(FALSE,t('!parser no_text'));
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------