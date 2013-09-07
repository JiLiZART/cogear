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
 *  Favorites model
 *
 * @package		CoGear
 * @subpackage	Favorites
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
 class Favorites extends Model{
 	 /**
	 *	List of user favorites
	 *
	 * @var	array
	 */
	 public $favorites = array();  
	 
	 /**
	 * Constructor
	 *
	 * @return	void
	 */
	 function __construct(){
		 parent::Model();
	 	$this->get();
 	 }
 	 
 	 /**
 	 * Get favorites
 	 *
 	 * @param	integer	User id.
 	 * @return	array
 	 */
 	 function get($uid = FALSE){
	 	 $CI =& get_instance();
	 	 if(!$uid){
		 	 $uid = $CI->user->get('id');
	 	 }
	 	 $favorites = $this->cache->get('favorites/'.$uid,TRUE);
	 	 if($favorites === FALSE){
		 	 if($favorites = $this->db->select('nid')->order_by('id','desc')->get_where('favorites',array('uid'=>$uid))->result_array()){
			 	 $favorites = array4key($favorites,'nid','nid');
		 	 }
		 	 else $favorites = array();
		 	 $this->cache->set('favorites/'.$uid,$favorites);
	 	 } 
	 	 if($CI->user->get('id') == $uid){
		 	 $this->favorites = $favorites;
	 	 }
	 	 return $favorites;
 	 }
 	 
 	 /**
 	 * Check for favorite
 	 *
 	 * @param	integer	Node id.
 	 * @return	boolean
 	 */
 	 function check($nid){
	 	 return isset($this->favorites[$nid]) ? TRUE : FALSE;
 	 }
 	 
 	 /**
 	 * Manage favorites
 	 *
 	 * @param	integer	Node id.
 	 * @return	boolean	Result.
 	 */
 	 function manage($nid){
		 $CI =& get_instance();
		 $uid = $CI->user->get('id');
		 if(isset($this->favorites[$nid])){
			 $this->db->delete('favorites',array('uid'=>$uid,'nid'=>$nid));
			 unset($this->favorites[$nid]);
			 $result = FALSE;
		 }	 	 
		 else {
			 $where = array('uid'=>$uid,'nid'=>$nid);
			 // Prevent double posting
			 if(!$this->db->get_where('favorites',$where)->row()){
				 $this->db->insert('favorites',$where);
			 }
			 $this->favorites[$nid] = $nid;
			 $result = TRUE;
		 }
		 $this->cache->set('favorites/'.$uid,$this->favorites);
		 return $result;
 	 }
}
