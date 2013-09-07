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
 * Buddy model
 *
 * @package		CoGear
 * @subpackage	Buddy
 * @category	Gears models
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
class Buddy extends Model{
	public $data = FALSE;
	private $buddies = array();
	/*
	* Constructor
	*
	* @return void
	*/
	function Buddy(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/*
	* Check buddies relationship via database
	*
	* @param int
	* @param int
	* @param boolean
	* @return mixed
	*/
	function check_db($to,$from,$pm = FALSE){
		$CI =& get_instance();
		// Link to exact pm by id
		if($pm && is_numeric($pm)) $this->db->where('pm',$pm);
		// No additional alerts after pm send
		$CI->pm->alert = FALSE;
		$this->db->where("(buddies.to = {$to} AND buddies.from = {$from})");
		$this->db->or_where("(buddies.from = {$to} AND buddies.to = {$from})");
		$this->data = $this->db->get('buddies')->row();
		if($this->data){
			if($this->data->approved){
				return t('!buddy already_friends');
			}
			else {
				return t('!buddy wait_for_approve');
			}
		}
		return FALSE;
	}
	// ------------------------------------------------------------------------

	/*
	* Add buddy
	*
	* @param int
	* @param int
	* @param string
	* @return boolean
	*/
	function add($to,$from,$body){
		if($result = $this->check_db($to,$from)) return $result;
		$CI =& get_instance();
		$CI->pm->copy = TRUE;
		$CI->pm->redirect = FALSE;
		if($CI->pm->send($to,$from,t('buddy add_title'),$body,TRUE)){
			$data['buddies.to'] = $to;
			$data['buddies.from'] = $from;
			$data['pm'] = $CI->pm->insert_id;
			if($this->db->insert('buddies',$data)){
				return TRUE;
			}
			else return FALSE;
		}
		else return FALSE;
	}
	// ------------------------------------------------------------------------

	/*
	*  Remove buddies relationships
	*
	* @param int
	* @param int
	* @param string
	* @return boolean
	*/
	function remove($to,$from,$body){
		$CI =& get_instance();
		$CI->pm->alert = FALSE;
		$CI->pm->copy = TRUE;
		$CI->pm->redirect = FALSE;
		if($id = $CI->pm->send($to,$from,t('!buddy remove_title'),$body,TRUE)){
			$data['buddies.to'] = $to;
			$data['buddies.from'] = $from;
			if($this->db->delete('buddies',$data)){
				$this->refresh($to);
				$this->refresh($from);
				return TRUE;
			}
			else return FALSE;
		}
		else return FALSE;
	}
	// ------------------------------------------------------------------------

	/*
	* Approve relationships
	*
	* @param int
	* @param int
	* @param int
	* @return boolean
	*/
	function approve($to,$from,$pm){
		$CI =& get_instance();
		if($this->check_db($to,$from,$pm) && $this->data){
			if($this->db->update('buddies',array('approved'=>'true'),array('id'=>$this->data->id)) && $this->db->delete('pm',array('id'=>$pm))){
				$this->refresh($to);
				$this->refresh($from);
				return TRUE;
			}
			else return FALSE;
		}
		else return FALSE;
	}
	// ------------------------------------------------------------------------

	
	/*
	* Delete buddies relationships and pm with buddy request
	*
	* @param int
	* @param int
	* @param int
	* @return boolean
	*/
	function destroy($to,$from,$pm){
		if($this->check_db($to,$from,$pm) && $this->data){
			if($this->db->delete('buddies',array('id'=>$this->data->id)) && $this->db->delete('pm',array('id'=>$pm))){
				$this->refresh($to);
				$this->refresh($from);
				return TRUE;
			}
			else return FALSE;
		}
		else return FALSE;
	}
	// ------------------------------------------------------------------------

	/*
	*  Get user buddies
	*
	* @param int
	* @return array
	*/
	function get($uid = FALSE){
		$CI =& get_instance();
		if(!$uid) $uid = $CI->user->get('id');
		elseif($this->buddies) return $this->buddies;
		$buddies = $CI->cache->get('buddies/'.$uid,TRUE);
		if($buddies === FALSE){
			$CI->db->swop();
			$buddies = array_keys(array4key(
			array_merge(
			$CI->db->select('buddies.from as id')->where('to',$uid)->get('buddies')->result_array(),
			$CI->db->select('buddies.to as id')->where('from',$uid)->get('buddies')->result_array()
			),'id'));
			if($buddies){
				unset($buddies[$uid]);
				$CI->db->select('id, name, url_name')->where_in('id',$buddies);
				$buddies = $CI->db->get('users')->result_array();
				$buddies = array4key($buddies,'id');
			}
			$CI->db->swop();
			$CI->cache->set('buddies/'.$uid,$buddies);
		}
		return $uid ? $buddies : $this->buddies = $buddies;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Refresh data
	*
	* @param	int	User id.
	*/
	function refresh($uid = FALSE){
		$CI =& get_instance();
		if(!$uid){
			$uid = $CI->user->get('uid');
		}
		$this->user->refresh($uid);
		$this->cache->clear('buddies/'.$uid);
	}

	/*
	* Check user for relationship
	*
	* @param mixed
	* @return void
	*/
	function check($buddy){
		$CI =& get_instance();
		$buddies = $this->get();
		if(!$buddies) return FALSE;
			$field = 'url_name';
			switch(gettype($buddy)){
				case 'array':
				$param = $buddy['name'];
				break;
				case 'object':
				$param = $buddy->name;
				break;
				case 'string':
				$param = $buddy;
				break;
				case 'integer':
				$param = $buddy;
				$field = 'id';
				break;
			}
			foreach($buddies as $user){
				if($param == $user[$field]){
					return $user;	
				}
			}
		return FALSE;		
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------