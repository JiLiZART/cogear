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
 * Main controller
 *
 * Shows admin control panel.
 *
 * @package		CoGear
 * @subpackage	Admin Control Panel
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
	*  Show layout for gears options.
	*
	* @return	void
	*/
	function index(){
		// Get gears groups
		$groups = $this->load->get_groups();
		// Simple reorder groups
		array_push($groups,array_shift($groups));
		
		foreach($groups as $group){
			// Get group gears
			$group_gears = array2object($this->load->get_gears($group));
			$gears = array();
			// Set info for layout
			foreach($group_gears as $gear_name=>$gear){
				if(file_exists(GEARS.$gear_name.'/_admin'.EXT)){
					$gear->icon64 = $gear->icon;
					$gear->name = $gear_name;
					$gear->link = l('/admin/'.$gear_name);
					$gear->title = at('gears '.$gear_name,$gear->title);
					$gear->description = at('gears '.$gear_name.'_description',$gear->description);
					$gears[] = $this->_template("PolyGon",array('item'=>$gear),TRUE);
				}
			}
			// Switch core name to settings - better user contact
			if($group == 'core') $group = 'settings';
			if(count($gears) > 0) $data[] = array('name'=>fc_t('!global '.$group),'content'=>$this->_template('PolyGrid',array('items'=>$gears,'in_row'=>2),TRUE))	;
			unset($gears);
        }
		if(isset($data)) $this->_template('SimpleTabs',array('tabs'=>$data));
	}
	// ------------------------------------------------------------------------

}
// ------------------------------------------------------------------------