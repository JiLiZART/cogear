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
 * Control Panel controller for ACL gear
 *
 * @package		CoGear
 * @subpackage	Access Control Level
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller {
	/**
	*  Constructor
	*
	* @return	void
	*/
	function _Admin(){
		parent::Controller();
		$this->table = 'acl';
	}
	// ------------------------------------------------------------------------

	/**
	*  Index method - loads by default route. Show list of gears rules.
	*
	* @return	void
	*/
	function index(){
		// Get gears acl from acl model
		$gears = $this->acl->rules();
		// Exclude admin, because it has super-powers
		$user_groups = $this->user_groups->get_list(1,0);
		foreach($user_groups as $gid=>$group){
			// Get rules for groups
			$rules[$gid] = $this->acl->group($gid,TRUE);
		}
		// Set form
		$this->form->set($this->table)
		->action = '/admin/acl/ajax/';		
		  $info = array(
		  'primary' => 'name',
		  'ajax' => TRUE,
		  'no_class' => TRUE
		  );
		// Seek through gears and make params for Grid view for every gear and it rules
		foreach($this->gears as $gear_name=>$gear){
		  // If there rules for gear
		  if(isset($gears[$gear_name])){
			// Set name. If it has translation - use it, otherwise - use default title.
			$name = has_t('!gears '.$gear_name) ? t('!gears '.$gear_name) : $gear->title;
			// header - and array of grid header info
			$header = array();
			// data for grid
			$data = array();
			// For more information look into form model -> grid method.
			$header['translation'] = array('','text','20%');
			
			foreach($user_groups as $group){
				$header[$group['name']] = array(t('!user_groups '.$group['name']),'checkbox','20%');	
			}
			$header['empty'] = array('','text','100%');
			$i = 0;
			foreach($gears[$gear_name] as $rule=>$value){
				$data[$i]['name'] = $rule;
				$data[$i]['translation'] = t('acl '.$rule);
				foreach($user_groups as $group){
					if(isset($rules[$group['id']][$gear_name][$rule])) $data[$i][$group['name']] = TRUE;
				}
				$i++;
			}
			$info['check_array_name'] = $gear_name;
			// Set grid
			$this->form->grid($name,$header,$data,$info);
		}		 
	  }
	  // Set butons with no value - there will be hidden
	  $this->form->buttons();
	  // Compile form for output
      $this->form->compile();
	}
	// ------------------------------------------------------------------------

	/**
	*  Because all acl managment there is made by ajax, this method will catch and process all ajax requests
	*
	* @return	json
	*/
	function ajax(){
		// No POST data? 
		if(count($_POST) < 1) return _404();
		// Get group
		$group = reset(array_keys($_POST));
		// Get gear
		$gear = reset(array_keys($_POST[$group]));
		// Set rule
		$rule = $_POST[$group][$gear];
		// Get group_id by it name
		$gid = $this->user_groups->get_id_by_name($group);
		// Set data array for database
		$data = array(
			'gid'=>$gid,
			'gear'=>$gear,
			'rule'=>$rule,
		);
		$this->cache->clear('acl/groups/'.$gid);
		// If rule is exist - delete it
		if($this->db->get_where($this->table,$data)->num_rows() > 0 && $this->db->delete($this->table,$data)){
			ajax(TRUE);			
		}
		// otherwise - add new rule for group
		elseif($this->db->insert($this->table,$data)){
			ajax(TRUE);			
		}
		else {
			ajax(FALSE);
		}
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------