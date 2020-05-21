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
 * Access Control Level model
 *
 * Manage access control system
 *
 * @package		CoGear
 * @subpackage	Access Control Level
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
 class Acl extends Model{
	 /**
	 * List of all rules
	 *
	 * @var	array
	 */
	 private $rules = array();
	 /**
	 * List of current user rules
	 *
	 * @var	array
	 */
	 private $user_rules = array(); 
	 
	 /**
	 * Constructor
	 *
	 * @return	void
	 */
	 function __construct(){
		 parent::Model();
		 $this->init();
	 }
	 
	/**
	* Initialize acl model
	*
	* @return	void
	*/
	 function init(){
		 $CI =& get_instance();
		 $this->rules();
		 if($CI->user->is_logged()){
			 $this->group($CI->user->get('user_group'));
		 }
	 }
	 
	 /**
	 * Get all rules
	 *
	 * @return	array
	 */
	 function rules(){
		 if($this->rules) return $this->rules;
		 $rules = $this->cache->get('acl/rules',TRUE);
		 $this->db->swop();
		 if($rules === FALSE && $data = $this->db->select('gear,rule')->order_by('gear')->get('acl_rules')->result_array()){
			 foreach($data as $rule){
						 $rules[$rule['gear']][$rule['rule']] = TRUE;
			 } 
	 		 $this->cache->set('acl/rules',$rules);
 		 }
		 $this->db->swop();
 		 return $this->rules = $rules;
	 }
	 
	 /**
	 * Get group acl
	 *
	 * @param	int		Group id
	 * @param	booelan	Force get
	 * @return	mixed	Group rules list or FALSE if there are no rules.
	 */
	 function group($gid = 100, $force = FALSE){
		 if($gid == 1) return $this->user_rules = TRUE;
		 elseif($this->user_rules && !$force) return $this->user_rules;
		 else {
			 $group_acl = $this->cache->get('acl/groups/'.$gid,TRUE);
			 if($group_acl === FALSE){
				 $this->db->swop();
				 if($rules = $this->db->select('gear,rule')->get_where('acl',array('gid'=>$gid))->result_array()){
					 foreach($rules as $rule){
						 $group_acl[$rule['gear']][$rule['rule']] = FALSE;
					 } 
					 $this->cache->set('acl/groups/'.$gid,$group_acl);
				 }
				 $this->db->swop();
			 }
			 return $this->user_rules = $group_acl;
		 }
	 }
	 
	 /**
	 * Check rule 
	 *
	 * @param	string	rule
	 * @return	boolean	
	 */
	 function _check($rule){
		 $a = $rule;
		 $rule = explode(' ',$rule);
		 // Check -- if rule isn't in database
		 if(count($rule) == 2 && !isset($this->rules[$rule[0]][$rule[1]]) && !in_array($rule[1],array('access','admin'))){
			 $where = array('gear'=>$rule[0],'rule'=>$rule[1]);
			 $this->db->swop();
			 if(!$this->db->get_where('acl_rules',$where)->row()){
				$this->db->insert('acl_rules',$where);
				$this->cache->clear('acl/rules');
			 }
			 $this->db->swop();
		 }
		 else if(count($rule) < 2) $rule[1] = 'access';
		 if($this->user_rules === TRUE){
			 return TRUE;
		 }
		 if($this->user_rules !== FALSE){
			 return isset($this->user_rules[$rule[0]][$rule[1]]) ? TRUE : FALSE;
		 }
		 else {
			 return $rule[1] == 'access' && $rule[0] != 'admin' ? TRUE : FALSE;
		 }	
	 }
 }