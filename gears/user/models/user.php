<?php  if ( ! defined('BASEPATH')) exit('No SUBDIRect script access allowed');
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
 * User model
 *
 * @package		CoGear
 * @subpackage	User
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class User extends Model {
	/**
	* Auth via cookies
	*
	* @var boolean
	*/
	public $via_cookies = FALSE;
	/**
	* Current user data
	*
	* @var object
	*/	
	private $userinfo;
	
	/**
	 * Variable shows user login
	 *
	 * @var boolean
	 */
	public $is_logged = FALSE; 
	
	/**
	* Constructor
	*
	* @return	void
	*/
	function User(){
		parent::Model();
		$this->table = 'users';
		if($this->autologin()) $this->init();
	}

	/**
	* Login.
	*
	* @param	string	$login
	* @param	string	$password
	* @param	boolean	$save_me	Store user authorization in cookies for two weeks.
	* @param	boolean $force_login
	* @return	boolean
	*/
	public function login($login,$password,$save_me = FALSE,$force_login = FALSE){
		d('user');
		// Preparing where conditions for query
		// If user doesn't use cookie auth
		$this->db->where('(email = "'.$login.'" OR name = "'.$login.'" OR url_name="'.$login.'") AND password != ""');
		$this->db->where('is_validated = true');
		if($user = $this->db->get($this->table)->row()){
			if($force_login OR $user->password === $password){
			    $this->refresh($user);
			    if($save_me){
					set_cookie("id",$user->id,3600*24*14,$this->cookie_domain,$this->cookie_path);
                    set_cookie("s",$this->salt($user->name,$user->password),3600*24*14,$this->cookie_domain,$this->cookie_path);
			    }
			    return TRUE;
			}
			else {
				msg('incorrect_login_or_password',FALSE);
				$this->erase_cookies();
				return FALSE;
			}
		}
		else {
				msg('incorrect_login_or_password',FALSE);
				$this->erase_cookies();
				return FALSE;
		}
	}

	/**
	* Login via cookies
	*
	* @param	int		$id
	* @param	string	$salt
	* @return	boolean
	*/
	public function cookies_login($id,$salt){
		$this->db->where(array("id"=>$id,'is_validated'=>TRUE));
		if($result = $this->db->get($this->table)){
			$user = $result->row();
			if($user && $this->salt($user->name,$user->password) === $salt){
			    $this->refresh($user);
			    return TRUE;
			}
			else {
				$this->erase_cookies();
				return FALSE;
			}
		}
		else {
				$this->erase_cookies();
				return FALSE;
		}
	}
	
	/**
	* Destroy user cookies
	*
	* @return void
	*/
	public function erase_cookies(){
		delete_cookie("id",$this->cookie_domain,"/");
	      delete_cookie("s",$this->cookie_domain,"/");
		delete_cookie("id",'.'.$this->cookie_domain,"/");
	      delete_cookie("s",'.'.$this->cookie_domain,"/");	
	}
	
	/**
	* Logout
	*
	* @param	boolean
	* @return	void
	*/
	function _logout($redirect = FALSE){
		$CI =& get_instance();
		//if($this->user->check_key()){
			$this->session->destroy();
			$this->erase_cookies();
		//}
		//else msg(t('user restricted_logout'),FALSE);
        redirect($redirect ? $redirect : '/');
	}
	// ------------------------------------------------------------------------

	/**
	* Check user is logged
	*
	* @return	object
	*/
	function is_logged(){
        return $this->session->get("uid");
	}

    
	/**
	* Automatic login
	*
	* @return	boolean
	*/
	function _autologin(){
	  if(!$this->session->get("uid")) {
			// If user !is_logged, but has cookies
		$id = $this->input->cookie('id');
		$salt = $this->input->cookie('s');
	      if($id && $salt){
	          return $this->cookies_login($id,$salt);
	      }
	      // If no cookies and no session data
	      else {
	          return FALSE;
	      }
	  }
	  // If user is logged by session
	  else {
	      return TRUE;
	  }
	}

	/**
	* Refresh user data
	*
	* @param	mixed	$uid
	* @return	object	$user
	*/
	function _refresh($uid = FALSE){
		if(is_object($uid)){
			$this->init($uid);
		}    
	    elseif(is_numeric($uid)){
		    $this->cache->clear('users/'.$uid);
	    } 
	    elseif($uid = $this->session->get('uid')){
			    $this->db->update('users',array('last_visit'=>date('Y-m-d H:i:s'),'ip'=>$this->session->get('ip_address')),array('id'=>$uid));
			    $user = $this->db->get_where('users',array('id'=>$uid))->row();
			    $this->update($user);
			    return $user;
	    }
	}

	/**
	* Create cookie salt
	*
	* @param	string
	* @param	string
	* @return	string
	*/
	private function salt($login = FALSE,$password = FALSE){
		$CI =& get_instance();
		$auth_field = isset($CI->gears->user->auth_field) ? $CI->gears->user->auth_field : 'email';
		
		if(!$login && !$password){
			$login = $this->$auth_field;
			$password = $this->password;
		}
	  return md5(sha1($login.$password).date("Y-m"));
	}

	/**
	* Update user info
	*
	* @param	string
	* @param	mixed
	* @return	void
	*/
    function _update($name = FALSE,$value = FALSE){
		if($uid = $this->session->get('uid')){
			if(is_array($name) OR is_object($name)){
				foreach($name as $k=>$val){
					$this->userinfo->$k = $val;
				}
			}
			else if(gettype($name) === 'string'){
					$this->userinfo->$name = $value;
			}
			if(count((array)$this->userinfo) > 0) $this->cache->set('users/'.$uid,$this->userinfo);
		}
    }
	
	/**
	* Initialize user from cache
	*
	* @param	mixed $user
	* @return	void
	*/
	function _init($user = FALSE){
		if(is_object($user)){
		 $uid = $user->id;
		 $this->session->set('uid',$uid);
		 $user = (array) $user;
		 $this->update($user);
		}
	    else if($uid = $this->session->get('uid')){
		    if(!$user = $this->cache->get('users/'.$uid,TRUE)){
			    $user = $this->refresh();
		    }
		    foreach($user as $key=>$value){
				    $this->userinfo->$key = $value;
		    }
		    if(!is_array($this->get('avatar'))) $this->update('avatar',make_icons($this->get('avatar')));
		    $this->secret_keygen();
	     }
	    $this->is_logged = TRUE;
	}
	
	/**
	 * Generate user secret key.
	 *
	 * @return	void
	 */
	public function secret_keygen(){
		$this->update('key',substr(md5($this->get('name').$this->get('password').date('H d.m.Y')),0,5));
	} 
	
	/**
	 * Check secret key
	 */
	public function check_key($key = FALSE){
		if(!$key) $key = end($this->uri->segments);
		return $key == substr(md5($this->get('name').$this->get('password').date('H d.m.Y')),0,5);
	}
	 
	
	/**
	* Get user info
	*
	* @param	string
	* @param 	boolean
	* @return	object
	*/
    function get($var = FALSE,$as_array = FALSE){
			$output = $var ? (isset($this->userinfo->$var) ?  $this->userinfo->$var : FALSE) : ($this->userinfo ? $this->userinfo : FALSE);
			if($as_array) $output = (array)$output;
			return $output;
    }

	/**
	* Set user info
	*
	* @param	string
	* @param	mixed
	* @return	object
	*/
	function set($var,$value){
		$this->update($var,$value);
		return $this;
	}
	
	/**
	* Remove data
	*
	* @param	string	Key.
	*/
	function remove($var){
		$this->update($var,FALSE);
	}

	/**
	* Get info about some user
	*
	* @param	mixed
	* @param	string
	* @return	mixed
	*/
	function info($param){
		$CI =& get_instance();
		if(is_numeric($param)){
			if($info = $this->cache->get('users/'.$param,TRUE)){
				return (object)$info;
			}
			else {
				$this->db->where('users.id',$param);
			}
		}
		else {
			if(is_array($param)) $this->db->where($param);
			else $this->db->where('users.url_name',$param);
		}
		$user = $this->db->select('users.*',FALSE)->get('users')->row();
		if($user){
			if(trim($user->avatar) == '') {
				$user->avatar = $CI->gears->user->avatar->default;
			}
			$user->avatar = make_icons($user->avatar,$this->gears->user->avatar->size); 
			$this->cache->set('users/'.$user->id,$user);
			return $user;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Show head panel with userinfo
	*
	* @param	object
	* @param	string
	* @return	void
	*/
	function head($user,$active = 'profile'){
		 d('user');
		 $CI =& get_instance();
		 $CI->breadcrumb->set('userinfo_panel')->wrapper();
		 $CI->breadcrumb->data($user);
		 if(empty($user->avatar['64x64'])){
			 $user->avatar = make_icons($user->avatar);
		 }
		 $CI->breadcrumb->add('<a href="'.$user->avatar['original'].'"><img src="'.$user->avatar['64x64'].'" class="avatar" alt="'.$user->name.'"></a><h1><a href="'.l('/user/'.$user->url_name).'">'.$user->name.'</a></h1>');
		 if($CI->user->get('user_group') == 1 OR $CI->user->get('id') == $user->id){
			 $CI->breadcrumb->add('<a href="'.l('/user/'.$user->url_name.'/edit/').'"><img src="/gears/global/img/icon/edit.png" title="'.t('!edit edit').'" alt="'.t('!edit edit').'"></a>');
		 }
		 $CI->breadcrumb->compile(4);
		 $CI->userinfo_tabs = new Panel('userinfo_tabs',FALSE,FALSE,'tabs');
		 $CI->userinfo_tabs->data =& $user;
		 $CI->userinfo_tabs->set_title = FALSE;
		 $CI->userinfo_tabs->links_base = '/user/'.$user->url_name.'/';
		 $CI->userinfo_tabs->add(array('name'=>'profile','text'=>t('!user Profile'),'index'=>TRUE));
		 $CI->userinfo_tabs->set_active($active);
		 $CI->userinfo_tabs->compile(5);
		 d();
	}
}