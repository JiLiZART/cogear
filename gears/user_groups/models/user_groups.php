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
 * User groups model
 *
 * @package		CoGear
 * @subpackage	User groups
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class User_groups extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function User_groups(){
		parent::Model();
		$this->table = 'user_groups';
	}
	// ------------------------------------------------------------------------

	
	/**
	* Create user group
	*
	* @param	string
	* @param	int
	* @return	boolean
	*/
	function _create($name,$id = FALSE){
		$data['name'] = $name;
		if($id) $data['id'] = $id;
		
		if($this->db->insert($this->table,$data)){
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Destroy user group
	*
	* @param	mixed
	* @return	boolean
	*/
	function _remove($param){
		if(is_array($param)){
				$result = FALSE;
				foreach($param as $p){
					$result = $this->_remove($p);
				}
				return $result;
		}
		
		switch(gettype($param)){
			case 'integer':
				$where = array('id'=>$param);
			break;
			case 'string':
				$where = array('name'=>$param);
			break;	
		}
		
		if($this->db->delete($this->table,$where)){
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Get user group by id
	*
	* @param	int
	* @return	object
	*/
	function _get($id){
		return $this->db->get_where($this->table,array('id'=>$id));
	}
	// ------------------------------------------------------------------------

	
	/**
	* Get user groups list
	*
	* @return	array
	*/
	function _get_list(){
		$groups = array4key($this->db->order_by('id','asc')->get($this->table)->result_array(),'id');
		$exclude = func_get_args();
		if(count($exclude) > 0) {
			foreach($groups as $key=>$group){
				if(in_array($key,$exclude)) unset($groups[$key]);
			}
		}	
		return $groups;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Get user groups id by name
	*
	* @param	string
	* @return	mixed
	*/
	function _get_id_by_name($name = ''){
		$query = $this->db->select('id')->get_where($this->table,array('name'=>$name));
		if($query->num_rows() > 0){
			return reset($query->row_array());
		}
		else {
			return FALSE;
		}
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------