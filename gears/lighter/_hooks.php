<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package			CoGear
 * @author			CodeMotion, Dmitriy Belyaev
 * @copyright		Copyright (c) 2009, CodeMotion
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Lighter hooks
 *
 * @package		CoGear
 * @subpackage	Geshi
 * @category		Gears hooks
 * @author		CodeMotion, Dmitriy Belyaev
 * @link			http://cogear.ru/user_guide/
 */
	/**
	* Add lighter button to editor
	*
	* @param	object
	* @return	void
	*/
	function lighter_editor_compile_after_($Editor){
		js('/gears/lighter/js/inline/code.button',FALSE,TRUE);
	}