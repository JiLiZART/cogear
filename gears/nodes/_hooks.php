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
 * Nodes hooks
 *
 * @package		CoGear
 * @subpackage	Nodes
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add create button to cpanel
	*
	* @param	object
	* @return	void
	*/
	function nodes_header($CI){
		if($CI->user->is_logged() && acl('nodes create')){
			$CI->cpanel->add(array('text'=>t('!nodes create'),'data'=>'/gears/nodes/img/icon/create.png','link'=>l('/create/'),'class'=>'panel-icon'),1);
		}
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------