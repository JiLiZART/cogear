<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author		CodeMotion, Dmitriy Belyaev
 * @copyright	Copyright (c) 2009, CodeMotion
 * @license		http://cogear.ru/license.html
 * @link		http://cogear.ru
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Search widget
 *
 * @package		CoGear
 * @subpackage	Search
 * @category	Gears widgets
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
	/*
	* Search widget
	*
	* @param object
	* @param array
	* @return void
	*/
	function search_widget($CI,$config = FALSE){
		return '
	<form method="get" id="search_widget" action="'.l('/search/').'">
		<div class="field"><input type="text" class="text" name="query" id="query"><input type="submit" id="search-submit"/></div>
	</form>
';
		}

	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------