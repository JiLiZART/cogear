<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Loginza Model
 *
 * @package		CoGear
 * @subpackage	Loginza
 * @category	Gears model
 * @author		CodeMotion, Aleksey Konovalov aka Avalak
 * @link		http://cogear.ru/user_guide/
 */
class Loginza extends Model
{
	private $_userProfile;
	private $_loginzaProfile;
	private $_token;
	private $_name;
	
	/**
	* Constructor
	*
	* @return	void
	*/
	function Loginza(){
		parent::Model();
	}


	/*
	 * getUserProfile function
	 * try get UserProfile via LoginzaAPI.class or return false
	 *
	 * @return string $this->_userProfile
	 * or
	 * @return boolean false
	 */
	function getUserProfile()
	{
		if(!isset($this->_userProfile))
			if($this->getToken() != false)
				$this->_userProfile=$this->loginzaapi->getAuthInfo($this->getToken());
			else
				return false;
		return $this->_userProfile;
	}

	/*
	 * getToken function
	 * try get Loginza Token via $_POST or return false
	 *
	 * @return string $this->_token
	 * or
	 * @return boolean false
	 */
	
	function getToken()
	{
		if(!isset($this->_token))
			if(isset($_POST['token']))
				$this->_token=$_POST['token'];
			else
				return false;
		return $this->_token;
	}

	function isValid()
	{

	}

	/*
	 * hasError function
	 * 
	 * true if has error, false if hasn't
	 * 
	 * @return boolean 
	 */

	function hasError()
	{
		if(!empty($this->getUserProfile()->error_type))
			return true;
		return false;
	}

	/*
	 * validate function
	 * main model function
	 */
	function validate()
	{
		if($this->getUserProfile()!=false)
		{
			if(!$this->hasError())
				return true;
			else
				return false;
		}
		else
			return false;
	}

	function getIdentity()
	{
		if(isset($this->_userProfile->identity))
			return $this->_userProfile->identity;
		return false;
	}

	/*
	 * getOpenID functin
	 * PS в user->login проблема с обработкой openid. убираем http:// и / в конце.
	 * надеюсь скоро перепуишут
	 */
	function getOpenID()
	{
		$tmp=$this->getIdentity();
		$tmp=str_replace('http://','',$tmp);
		if($tmp[strlen($tmp)-1]==='/')
		{
			$tmp[strlen($tmp)-1]=' ';
			$tmp=trim($tmp);
		}

		return $tmp;
	}


	function getUrlName()
	{
		 return $this->getName();				
	}

	function getName()
	{

		if(!isset($this->_name))
		$this->_name=$this->loginzauserprofile->genNickname();
		return $this->_name;
	}
	
	/*
	 *
	 */
	function login()
	{
		
		$login = $this->getOpenID();
		if($user = $this->db->select('u.*',FALSE)->join('users_openid uo','uo.uid = u.id','inner')->where('uo.openid = "'.$login.'"')->get('users u')->row())
		{
			$this->user->refresh($user);
			return TRUE;
		}
		else
		{
			$this->loginzauserprofile->loadProfile($this->loginza->getUserProfile());
			if($this->user->is_logged()){
				if($this->db->where(array('uid'=>$this->user->get('id'),'openid'=>$login))->get('users_openid')->row()){
					return TRUE;
				}
				$user = $this->user->get();
			}
			else {
				$name=$this->loginzauserprofile->genNickname();
				
				if($existing_user = $this->db->where("name = '{$name}' OR url_name = '{$name}'")->get('users')->row()){
					msg(t('loginza user_exists',$existing_user->name),FALSE);
					redirect('/user/'.$existing_user->url_name.'/');
				}				
				$user = array(
					'name' => $name,
					'url_name' => $this->getUrlName($name),
					'validate_code' => $this->session->get('session_id'),
					'last_visit' => date('Y-m-d H:i:s'),
					'ip' => $this->session->get('ip_address'),
					'is_validated' => 'true',
					//'avatar'=>'/uploads/avatars/openid/openid.png'
				);
				$this->db->insert('users',$user);
				$user['id'] = $this->db->insert_id();
				$user = array2object($user);
			}
			if($this->db->get_where('users_openid',array('openid'=>$login))->row()){
				msg(t('loginza busy',$login),FALSE);
				return FALSE;
			}
			$this->db->insert('users_openid',array('uid'=>$user->id,'openid'=>$login));
			$this->session->destroy();
			$this->user->erase_cookies();
			$this->user->login($user->name,FALSE,TRUE,TRUE);
		}

	    return TRUE;
	}

}



?>
