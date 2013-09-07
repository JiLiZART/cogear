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
 * Personal Messaging model
 *
 * @package		CoGear
 * @subpackage	Mail
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Pm extends Model{
	public $alert = TRUE;
	public $name = FALSE;
	public $data = FALSE;
	public $to = FALSE;
	public $from = FALSE;
	public $copy = FALSE;
	public $subject;
	public $body;
	public $redirect = TRUE;
	/**
	* Constructor
	*
	* @return	void
	*/
	public function Pm(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Initialization
	*
	* @param	string
	* @param	mixed
	* @return	object
	*/
	public function set($name = FALSE,$data = FALSE){
		if($name) $this->name = $name;
		if($data) $this->data($data);
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set model data
	*
	* @param	mixed
	* @return	object
	*/
	public function data($data){
		$this->data =& $data;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Send pm
	*
	* @param	mixed
	.....................
	* @return	object
	*/
	public function send(){
			$CI =& get_instance();
			$args = func_get_args();
			if(is_array($args[0])){
				foreach($args[0] as $key=>$value){
					if($value) $this->$key = $value;
				}
			}
			else {
				$fields = array('to','from','subject','body','system');
				foreach($args as $key=>$value){
					if(isset($fields[$key]) && $value){
						$field = $fields[$key];
						$this->$field = $value;
					}
				}
			}
		     $this->to = trim($this->to," ,");
			 $to = explode(',',$this->to);
			 foreach(array_unique($to) as $info){
				$info = trim($info);
				if(is_numeric($info)){
					$this->db->where('id',$info);
				}
				else {
					$this->db->where('url_name',url_name(trim($info)))->or_where('name',trim($info));
				}
				if($user = $this->db->get('users')->row()){
					if(!$this->_hook('pm','user','get',$user)){
						continue;
					}
					if($user->id != 1 && $user->id == $this->user->get('id')){
						msg('mail message_to_yourself','gears mail');
						$CI->form->set_values(get_object_vars($this));
						continue;
					}
					$users[] = $user->id;
					if($this->gears->mail->mode == 'line'){
						$data = array(
							'pm.to'=>$user->id,
							'pm.from'=>$this->from ? $this->from : $this->user->get('id'),
							'subject'=>$this->subject,
							'body'=>$this->body,
							'system'=>empty($this->system) ? NULL : 'true',
							'last_update'=>date('Y-m-d H:i:s'),
						);
						if($this->db->insert('pm',$data)){
							$this->insert_id = $this->db->insert_id();
							$data['owner'] = 'from';
							$this->db->insert('pm',$data);
							$this->user->refresh($user->id);
							$this->_hook('pm','sent','success',$user,$data);
							if($this->alert) msg(t('mail message_sent',$user->name));
						}
						else {
							if($this->alert) msg(t('mail message_sent_failure',$user->name),FALSE);
						}
					}
				}	
				else {
					if($this->alert) msg(t('mail user_not_found',$info),FALSE);
				}	
			 }
			 if($this->gears->mail->mode == 'inbox' && !empty($users)){
						$data = array(
							'pm.to'=>implode(',',$users),
							'pm.from'=>$this->from ? $this->from : $this->user->get('id'),
							'subject'=>$this->subject,
							'body'=>$this->body,
							'owner'=>'to',
							'has_read'=>$this->from ? $this->from : $this->user->get('id'),
							'system'=>empty($this->system) ? NULL : 'true',
							'last_update'=>date('Y-m-d H:i:s'),
						);
						if($this->db->insert('pm',$data)){
							$this->insert_id = $this->db->insert_id();
							foreach($users as $user){
								$this->user->refresh($user);
							}
							$this->_hook('inbox','sent','success',$users,$data);
							
							if($this->alert) msg(t('mail inbox_sent'),'gears mail');
						}
						else {
							if($this->alert) msg(t('mail inbox_sent_failure'),FALSE);
						}
						if($this->redirect) redirect(l('/'.$this->gears->mail->url.'/read/'.$this->insert_id));
			 }
			 return $this;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------