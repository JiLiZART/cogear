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
 * Mail config controller
 *
 * @package		CoGear
 * @subpackage	Mail
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function _Admin(){
		parent::Controller();
		d('mail');
	}
	// ------------------------------------------------------------------------
	
	/**
	* Create tabs
	*
	* @param	string $active Set active tab
	* @return	void
	*/
	private function tabs($active = 'index'){
		$this->panel->links_base = '/admin/mail/';
		$this->panel->name = 'admin/mail';
		$this->panel->template = '!global tabs';
		$this->panel->add(array('name'=>'index','text'=>fc_t('%settings'),'index'=>TRUE));
		$this->panel->add(array('name'=>'sender','text'=>fc_t('!mail_sender title')));
		$this->panel->set_active($active);
		$this->panel->compile(12);	
	}
	// ------------------------------------------------------------------------

	
	/**
	* Edit mail config
	*
	* @return	void
	*/
	function index(){
		$this->tabs();
		$this->form->set('mail_settings','centered')->title('%settings',FALSE,FALSE,TRUE)->fieldset('mail_settings')
		->input('check_time')
		->input('per_page')
		->input('site_email',array('validation'=>'required|valid_email','js_validation'=>'required|email'))
		->input('from_name',array('validation'=>'required','js_validation'=>'required'))
		->checkbox('mail_comment_reply')
		->checkbox('mail_comment_post_author')
		->buttons('save')
		->set_values($this->gears->mail);
		if($result = $this->form->result()){
			if($this->info->set(GEARS.$this->gears->mail->url.'/mail')->change($result)->compile()){
				msg('!form saved');
				redirect('admin/mail');
			}
		}
		else {
			$this->form->compile();
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	*	Mailling list sender
	*
	* @return	void
	*/
	function sender(){
		$this->tabs('sender');
		$user_groups = array4key($this->db->order_by('id','asc')->get('user_groups')->result_array(),'id','name');
		unset($user_groups[0]);
		d('mail_sender');
		$this->form->set('mail_sender');
		$this->form->key = FALSE;
		$this->form->action('/admin/mail/sender/')
		->div('slider')
		->fieldset('query')
		->input('name')
		->select('user_group',array('multiple'=>TRUE,'options'=>$user_groups))
/*
		->input('nodes_num')
		->input('comm_num')
*/
		->input('points')
		->datetime('reg_date',array('range'=>'2007-'.date('Y'),'value'=>date('Y-m-d H:i:s',time()-3600*24*3000)))
		->datetime('last_visit',array('range'=>'2007-'.date('Y'),'value'=>date('Y-m-d H:i:s',time()-3600*24*3000)))
		->select('mail_at_once',array('options'=>array_reverse(array_combine(array('10','50','100','250','500','750','1000'),array('10','50','100','250','500','750','1000')))))
		->input('start',array('value'=>0))
		->fieldset()
		->br()
		->input('subject',array('validation'=>'required','js_validation'=>'required'))
		->editor('body',array('validation'=>'required','js_validation'=>'required'))
		->div()
		->buttons('preview','submit');
		if($result = $this->form->result()){
			$this->db->select('users.*',FALSE);
			if($result['name']){
				$names = _explode(',',$result['name']);
				$this->db->where_in('name',$names);
			}
			if(isset($result['user_group'])){
				$user_group = $result['user_group'];
				$this->db->where_in('user_group',$user_group);
			}
/*
			if(isset($result['nodes_num'])){
				$this->db->select('(SELECT COUNT(*) FROM nodes WHERE nodes.aid = users.id) as nodes_num',FALSE)->where('nodes_num > '.intval($result['nodes_num']));
			}
			if(isset($result['comm_num'])){
				$this->db->select('(SELECT COUNT(*) FROM comments WHERE comments.aid = users.id) as comm_num',FALSE)->where('comm_num > '.intval($result['comm_num']));
			}
*/
			if($result['points'] !== ''){
				$this->db->where('points >= \''.$result['points'].'\'');
			}
			if(isset($result['reg_date'])){
				$this->db->where('reg_date > \''.$result['reg_date'].'\'');
			}
			if(isset($result['last_visit'])){
				$this->db->where('last_visit > \''.$result['last_visit'].'\'');
			}
			$count = $this->db->count_all_results('users',FALSE);
			$users = $this->db->limit($result['mail_at_once'],$result['start'])->get('users')->result();
			if($users){
				$i = 0;
				foreach($users as $user){
					$body = str_replace('%username%',$user->name,$result['body']);
					$this->mail->send($user,FALSE,FALSE,$result['subject'],array($body));
					$i++;
				}
				ajax(TRUE,t('!mail_sender sent',$i,$result['start'],$count),array('start'=>($result['mail_at_once']+$result['start'])));
			}
			ajax(FALSE,t('!mail_sender finish',$count));
		}
		else $this->form->compile();
		js('/gears/mail/js/inline/sender',FALSE,TRUE);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------