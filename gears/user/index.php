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
 * User controller
 *
 * @package		CoGear
 * @subpackage	User
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller {
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
	    d('user');
	}
	// ------------------------------------------------------------------------

	/**
	* Show user profile, edit
	*
	* @param	string
	* @param	string
	* @return	void
	*/
	function index($url_name = FALSE,$action = FALSE){
		if(!$url_name) redirect('user/login');
		if($user = $this->user->info($url_name)){
		$this->form->data =& $user;
		switch($action){
		 case 'edit':
		 title(t('Edit_profile').' '.$user->name,TRUE);
		 if($this->user->get('user_group') != 1 && $this->user->get('id') != $user->id) {
			 _403();
		  }
		 $this->form->set('user_profile');
		 $this->form->errors_location = 3;
		 $this->form->div('tabs')
		 ->title(t('!user_profile settings'))
		 ->fieldset('settings')
		 ->image('avatar',array(
			 'max_size' => 204,
			 'upload_path'=>_mkdir(ROOTPATH.'/uploads/avatars/'.$user->id),
			 'thumbs'=>$this->gears->user->avatar->size,
			 'width'=>'200',
			 'height'=>'200',
			 'overwrite'=>TRUE
		 ))
		 ->input('email',(acl('user change_email') OR empty($user->email)) ? array('validation'=>'required|valid_email','js_validation'=>'required|email') : array('disabled'=>TRUE))
		 ->input('secemail',array('validation'=>'valid_email','js_validation'=>'email'));
		 if($this->gears->user->openid){ 
			 $openids = $this->db->where(array('uid'=>$user->id))->get('users_openid')->result();
			 $output = '<ul><label>'.t('user_profile loginza_connect').'</label>';
			 $stop = empty($user->password) OR empty($user->email);
			 $i = 0;
			 foreach($openids as $openid){
				 $output .= '<li style="list-style-image: url(http://'.parse_url('http://'.$openid->openid,PHP_URL_HOST).'/favicon.ico)"><a href="http://'.$openid->openid.'">'.$openid->openid.'</a>';
				 if($stop && $i){
					 $output .= ' <small>'.t('loginza only_one').'</small>';
				 }
				 else {
					  $output .= ' <a href="/loginza/detouch/'.$openid->id.'/" class="loginza-delete">x</a></li>';
				 }
				 $i++;
			 }
			 $output .= '</ul>';
			 $output .= '
			 <script src="http://'.$this->site->url.'/gears/user/js/inline/loginza.js" type="text/javascript"></script>
			 <script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
<iframe src="https://loginza.ru/api/widget?overlay=loginza&token_url=http://'.$this->site->url.'/loginza/" 
style="width:359px;height:190px;" scrolling="no" frameborder="no"></iframe>';
			 $this->form->description($output);
/* 			 $this->form->input('openid',array('label'=>t('openid.openid'),'disabled'=>empty($user->email))); */
		 }
		 $this->form->input('password', array('label'=>t('new_password'),'description'=>t('new_password_description'),'validation'=>(empty($user->password) ? 'required|' : ''). 'min_length[5]','js_validation'=>(empty($user->password) ? 'required|' : ''). 'length[5,-1]','ajax'=>array('name'=>t('!edit generate'),'url'=>'/user/passgen/','where'=>'password.after','update'=>'password'),'stop_reset'=>TRUE))
		->fieldset()
		 ->br();
		 if($this->user->get('id') == 1 && $user->id != 1){
			 $this->form->buttons('save','delete');
		 }		 
		 else {
			 $this->form->buttons('save');
		 }
		 if($this->user->get('user_group') == 1){
			 $this->form->input('name',array('js_validation'=>'required|length[3,-1]','validation'=>'min_length[3]'),4);
			 $user_groups = array();
			 foreach($this->user_groups->get_list(0) as $group){
				 $user_groups[$group['id']] = t('!user_groups '.$group['name']);
			 }
			 if($user->id != 1) $this->form->select('user_group',array('label'=>t('!user_groups user_group'),'options'=>$user_groups),5);
		 }
		 if($result = $this->form->result()){
			 switch($result['action']){
			  case 'delete':
					$this->db->delete('users',array('id'=>$user->id));
					msg(t('!form deleted'));
					redirect('/users/');			  
				  break;

			  default:
				  if(!isset($result['secemail'])) $result['secemail'] = 'NULL';
				  if(isset($result['name'])) $result['url_name'] = url_name($result['name']);
				  $this->form->data =& $user;
				  if(!empty($result['password'])) $result['password'] = md5(md5($result['password']));
				  if(!empty($result['openid'])){
					   $result['openid'] = trim(str_replace(array('http://','www','https://'),'',$result['openid']),' /');
					   if($this->db->where('openid_reg','true')->where('openid',"'{$result['openid']}'",FALSE)->get('users')->row()){
						   msg(t('openid.is_owned'),FALSE);
						   unset($result['openid']);
					   }
					   else if(!empty($user->openid) && $user->openid != $result['openid']) {
						   $result['openid_reg'] = 'NULL';
					   }
				  }
				  $this->form->update('users',$result,array('id'=>$user->id));
/* 				  if(isset($result['password'])) $result['password'] = md5(md5($result['password'])); */
				  if($this->user->get('id') == $user->id){
					  $this->user->update($result);
					  if(isset($result['password'])){
						  $this->user->logout('/user/login/');
					  }
				  }
				  redirect('/user/'.$user->url_name.'/edit/');
			 }
		 }			 
		 $userdata = (array) $user;
		 $userdata['avatar'] = $user->avatar["original"];
		 $userdata['password'] = '';
		 $this->form->set_values($userdata);
		 $this->form->compile(7);
/*
		 $data[] = array('name'=>t('!user_profile settings'),'content'=>
		 $this->layout->name = 'edit_profile_tabs';
		 $this->layout->show('SimpleTabs',array('tabs'=>$data),10);
*/
		 
		 break;
		 default:
			 title(t('!user user').' '.$user->name,TRUE);
			 $this->profile = new Panel('userinfo_profile',FALSE,FALSE,'!user profile');
			 $this->profile->data =& $user;
			 d('user_profile');
			 
			 $item['type'] = 'line';
			 $item['left'] = t('register_date');
			 $item['right'] = df($user->reg_date);
			 $this->profile->add($item,0);
			 $item['left'] = t('last_date');
			 $item['right'] = t('last_date_description',df($user->last_visit));
			 $this->profile->add($item,1);
			 if($this->user->get('user_group') == 1){
					 $item['left'] = 'IP';
					 $item['right'] = $user->ip;
					 $this->profile->add($item);
			 }
			 $this->profile->compile(10);
			 break;
		 }
		 $this->_hook('user','profile',FALSE,$user);
		}
		else redirect('/users/');
	}

	// ------------------------------------------------------------------------
	
	/**
	* Login
	*
	* @return	void
	*/
	function login(){
	    title(t('auth'));
 	    $this->form->set('user/login')->title(t('auth'));
		if(!$this->user->is_logged()){
		$this->form
	     ->input('login', array('validation'=>'required','js_validation'=>'required'))
	    ->password('password',array('size'=>25,'validation'=>'required','js_validation'=>'required'))
	    ->checkbox('save_cookies')
	    ->buttons(array(
	    'submit' => array('value'=>t('login_submit')),
	    'button' => array('value'=>t('lost_password'),'onclick'=>"document.location.href='".l('/user/lostpassword/')."'")
	    ))
		->action('/user/login/');
		}
		else {
			return info(t('already_auth'));
		}
		if(!empty($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'],'user/login')) $this->session->set('referer',$_SERVER['HTTP_REFERER']);
		if($result = $this->form->result()){
			$result['login'] = addslashes($result['login']);
			$user = array2object($result);
			// In case of password transfered via air by cookies - it will be deleted after all
			if($this->user->login($user->login,md5(md5($user->password)),$user->save_cookies)){
				if($referer = $this->session->get('referer')){
					redirect($referer);	
				}
				redirect('/user/'.$this->user->get('url_name'));
			}
			else {
				if($referer = $this->session->get('referer')){
					redirect($referer);	
				}
				redirect('/user/login/');
			}
		}
		else {
			$this->form->description('			<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
<iframe src="https://loginza.ru/api/widget?overlay=loginza&token_url=http://'.$this->site->url.'/loginza/" 
style="width:359px;height:190px;" scrolling="no" frameborder="no"></iframe>
');
			$this->form->compile();		
		}
	}
	// ------------------------------------------------------------------------
	
	
	/**
	* Logout
	*
	* @return	void
	*/
	function logout(){
		$this->user->logout('');
	}
	// ------------------------------------------------------------------------

	
	/**
	* Registration
	*
	* @param	string	validation code
	* @return	void
	*/
	function register($code = FALSE){
		if(!$this->gear->registration){
			return info(t('user registration_disabled'));
		}
		// If it's a first try, no email confirm
		if($this->user->is_logged()) return info(t('already_auth'));
		if(!$code){
			$this->session->set('form_fail',100);
			$this->form->set('register');
			$this->form->title(fc_t('register'),FALSE,TRUE,TRUE);
			$this->form->input('email', array('validation'=>'required|valid_email|callback_check_email','js_validation'=>'required|email','ajax'=>array('name'=>t('!edit check'),'url'=>'/user/check_email/','where'=>'email.after')));
			$this->form->input('name', array('validation'=>'required|min_length[3]|alpha_numeric|callback_check_name','js_validation'=>'required|lengthmin[3]|alphanum','ajax'=>array('name'=>t('!edit check'),'url'=>'/user/check_name/','where'=>'name.after')));
			//,'md5'=>TRUE,'via_cookie'=>TRUE
			$this->form->password('password',array('size'=>25,'validation'=>'required|min_length[5]','js_validation'=>'required|length[5,-1]','ajax'=>array('name'=>t('!edit generate'),'url'=>'/user/passgen/','where'=>'password.after','update'=>'password')));
			$this->form->password('password_confirm',array('size'=>25,'validation'=>'required|matches[password]','js_validation'=>'required|confirm[password]'));
			$this->form->checkbox('agree_license',array('validation'=>'required','js_validation'=>'required'),99);
			$this->form->buttons(array(
			'submit'=>array('value'=>t('to_register'))
			));
			if($result = $this->form->result()){
				$user = array(
					'name' => $result['name'],
					'url_name' => url_name($result['name']),
					'email' => $result['email'],
					'password' => md5(md5($result['password'])),
					'validate_code' => $this->session->get('session_id'),
					'last_visit' => date('Y-m-d H:i:s'),
					'ip' => $this->session->get('ip_address')
				);
				if($this->gears->user->email_activate == FALSE){
					$user['is_validated'] = 'true';
				}
				$user = array_merge($result,$user);
				if($this->form->save('users',$user)){
					if($this->gears->user->email_activate){
						$this->_template('global result',array(
						'header' => fc_t('register'),
						'message' => t('register_success',$user['email']),
						));
						
						$user = array2object($user);
						$data = array(
						'name'=>$user->name,
						'link'=>l('/user/register/'.$this->session->get('session_id').'/'),
						'ip'=>$this->session->get('ip_address')
						);
						
						$this->mail->s($user->email,t('register',$this->site->url),'user.register',$data);
					}
					else {
						redirect('/user/register/'.$user['validate_code']);
					}
				}
			}
			else {
				$this->form->compile();
			}
		}
		else {
			if($user = $this->db->get_where('users',array('validate_code'=>$code))->row()){

				$this->db->update('users',array('is_validated'=>'true','validate_code'=>''),array('id'=>$user->id));
				$this->user->login($user->email,$user->password,TRUE,TRUE);
				msg('activation_success','Activation');
				redirect('/user/'.$user->url_name.'/edit/');
			}
			else show404();
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Email validation for existance
	*
	* @param	string
	* @return	boolean/json
	*/
	function check_email($email = FALSE){
		if(!$email){
			//$this->form->clear();
			$this->form->set('check_email');
			$this->form->input('data',array('validation'=>'required|min_length[3]|valid_email','label'=>t('email')));
			$result = $this->form->result();
			if(!$result){
			    $email_field = $this->form->find_by_name('data');
				ajax(FALSE,$email_field['error']);				
			}
			$email = $result['data'];			
			if($this->db->get_where('users',array('email'=>$email))->num_rows() > 0){
				ajax(t('email_already_owned'),TRUE);
			}
			else {
				ajax(TRUE,t('email_aviable'));
			}
		}
		else {		
			$this->form_validation->set_message('check_email', t('email_already_owned'));
			return $this->db->get_where('users',array('email'=>$email))->num_rows() > 0 ? FALSE : TRUE;
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Name validation for existance
	*
	* @param	string
	* @return	boolean/json
	*/
	function check_name($name = FALSE){
		if(!$name){
			$this->form->set('check_name');
			$this->form->input('data',array('validation'=>'required|min_length[3]','label'=>t('name')));
			$result = $this->form->result();
			if(!$result){
				$name_field = $this->form->find_by_name('data');
				ajax($name_field['error'],TRUE);				
			}
			$name = $result['data'];
			if(is_numeric($name)){
				ajax(t('no_numeric'),TRUE);
			}
			if(in_array($name,get_class_methods($this)) OR $this->db->where(array('name'=>$name))->or_where(array('url_name'=>url_name($name)))->get('users')->num_rows() > 0){
				ajax(t('name_already_owned'),TRUE);
			}
			else {
				ajax(TRUE,t('name_aviable'));
			}
		}
		else {		
			$this->form_validation->set_message('check_name', t('name_already_owned'));
			if(is_numeric($name)){
				$this->form_validation->set_message('check_name', t('no_numeric'));
				return FALSE;
			}
			return ($this->db->where(array('name'=>$name))->or_where(array('url_name'=>url_name($name)))->get('users')->num_rows() > 0 OR in_array($name,get_class_methods($this))) ? FALSE : TRUE;
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Generate password
	*
	* @return	json
	*/
	function passgen(){
		ajax(substr(md5(microtime().$this->session->get('id')),0,rand(5,10)),TRUE);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Check email for existance
	*
	* @param	string
	* @return	boolean
	*/
	function check_email_exists($email = FALSE){
		if(!$email){
			$email = $this->input->post("data");
			$this->form->clear();
			$this->form->set('check_email_exists');
			$this->form->input('data',array('validation'=>'required|valid_email','label'=>t('email')));
			if(!$this->form->result()){
				$element = reset($this->form->elements);
				ajax($element['error'],TRUE);				
			}
			
			if($this->db->get_where('users',array('email'=>$email))->num_rows() > 0){
				ajax(TRUE,t('email_exists'));
			}
			else {
				ajax(t('email_not_exists'),TRUE);
			}
		}
		else {		
			$this->form_validation->set_message('check_email_exists', t('email_not_exists'));
			return $this->db->get_where('users',array('email'=>$email))->num_rows() > 0 ? TRUE : FALSE;
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Password recovery
	*
	* @param	string
	* @return	void
	*/
	function lostpassword($code = FALSE){
		if($code) $this->register($code);
		$this->form->set('lostpassword');
		$this->form->title('Password_recovery',FALSE,TRUE,TRUE);
		$this->form->description('Password_recovery_description');
		$this->form->input('email', array('validation'=>'required|valid_email|callback_check_email_exists','js_validation'=>'required|email','ajax'=>array('name'=>t('!edit check'),'url'=>'/user/check_email_exists/','where'=>'email.after')));
		$this->form->buttons(array(
		'submit'=>array('value'=>t('!edit send'))
		));
		if($result = $this->form->result()){
			if($user = $this->db->get_where('users',array('email'=>$result['email']))->row()){
				$this->db->update('users',array('validate_code'=>$this->session->get('session_id')),array('id'=>$user->id));
				$data = array(
				'name'=>$user->name,
				'link'=>l('/user/lostpassword/'.$this->session->get('session_id').'/'),
				'ip'=>$this->session->get('ip_address')
				);
				$this->mail->s($user->secemail ? $user->secemail : $user->email,t('Password_recovery'),'password.recovery',$data);
				$this->_template('global result',array('header'=>t('Password_recovery'),'message'=>t('Password_recovery_result',$user->email)));
			}
			else {
				msg('email_not_exists',FALSE);
				redirect('/user/lostpassword/');
			}
		}
		else {
			$this->form->compile();
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Give user names for autocompleter
	*
	* @param
	* @param
	* @param
	* @return
	*/
	public function autocomplete(){
		if(!$value = $this->input->post('value')) return _404();
		$users = $this->db->select('name')->like('name',$value,'after')->or_like('url_name',$value,'after')->get('users',10)->result_array();
		foreach($users as $user){
			$output[] = $user['name'];
		}
		ajax(json_encode($output),TRUE);
		exit();
	}
}
// ------------------------------------------------------------------------