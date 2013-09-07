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

/**
 * Gears info model
 *
 * @package		CoGear
 * @subpackage	Installer
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Gears_info extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function Gears_info(){
		parent::Model();
	}

	/**
	* Get info about all gears in GEARS folder
	*
	* @return	array
	*/
	function get($dir = FALSE){
		 d('gears');
		 foreach(glob($dir ? $dir : GEARS.'*/*.info') as $config){
			 $cfg = $this->info->read($config);
			 $gear = basename(dirname($config));
			 $cfg['title'] = at($gear,$cfg['title']);
			 if(empty($cfg['group'])) $cfg['group'] = 'modules';
			 $cfg['description'] = trim(at($gear.'_description',empty($cfg['description']) ? '' : $cfg['description']),'.').'.';
			 $cfg['gear'] = $gear;
			 $cfg['gears'] = ($cfg['group'] == 'core' OR !empty($cfg['enabled'])) ? TRUE : FALSE;
			 $gears[$gear] = $cfg;
		 }
		 d();
		 return $gears;
	}
}
