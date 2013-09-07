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
 * Meta install model
 *
 * @package		CoGear
 * @subpackage	Meta
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Install extends Installer{
	/**
	* Constructor
	*
	* @return	void
	*/
	function _install(){
		parent::Installer();
	}
	// ------------------------------------------------------------------------

	/**
	* Installer
	*
	* @return	void
	*/
	function install(){
		
		$fields = $this->db->list_fields('nodes');
		if(count(array_intersect($fields,array('keywords','description'))) > 0) {
			return TRUE;
		}
		else {
			$this->db->query('ALTER TABLE  `nodes` ADD  `keywords` VARCHAR( 255 ) NOT NULL AFTER  `url_name`');
			$this->db->query('ALTER TABLE  `nodes` ADD  `description` VARCHAR( 255 ) NOT NULL AFTER  `keywords');
		}	
		return TRUE;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------