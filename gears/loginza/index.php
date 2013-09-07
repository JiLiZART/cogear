<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Loginza controller
 *
 * @package		CoGear
 * @subpackage	Loginza
 * @category	Gears controllers
 * @author		CodeMotion, Aleksey Konovalov aka Avalak
 * @link		http://cogear.ru/user_guide/
 */
class Index extends Controller
{
	/*
	 * __counstruct
	 *
	 * @return	void
	 */
	function __construct()
	{
		parent::Controller();
		d('loginza');

	}

	/*
	 * index action
	 *
	 * @param	int	$tpm
	 * @return
	 */
	function index($tpm=0)
	{
			if($this->loginza->validate())
			{
				$login = $this->loginza->getOpenID();
				
				if($this->loginza->login($login,'',FALSE,TRUE))
				{
					$name = substr($login,0,strpos($login,'.'));
					if($user = $this->db->where("name = '{$name}' OR url_name = '{$name}'")->get('users')->row())
					{
						if($user->openid == $login)
						{
							$name = $user->name;
						}
						else
						{
							$name = $user->name == $name ? $login : $name;
						}
					}
					$email = $this->user->get('email');
					info(t('complite'));
					redirect('/user/'.$this->user->get('url_name').'/edit/');
				}
			}
			elseif(!empty($this->loginza->getUserProfile()->error_type))
			{
					info(t('error'));				
			}
	}
	
	/**
	 * Detouch account
	 *
	 * @param	int	$id
	 * @return	JSON
	 */
	public function detouch($id){
		if($row = $this->db->get_where('users_openid',array('id'=>$id))->row()){
			if($row->uid == $this->user->get('id') OR acl('loginza detouch_all')){
				if(!$this->user->get('password') OR !$this->user->get('email')){
					$count_all = $this->db->where('uid',$this->user->get('id'))->count_all_results('users_openid');
					if($count_all == 1){
						ajax(FALSE);
					}
				}
				$this->db->where('id',$id)->delete('users_openid');
				ajax(TRUE);
			}
		}
		ajax(FALSE);
	}
}
