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
 * Community controller
 *
 * @package		CoGear
 * @subpackage	Community
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		d('community');
	}
	// ------------------------------------------------------------------------

	/**
	* Show communities list or members or nodes
	*
	* @param	string
	* @param	mixed
	* @param	mixed
	* @return	void
	*/
	function index($url_name = FALSE, $action = FALSE, $subaction = FALSE){
		if($url_name == 'all') {
			$this->builder->h1(t('!gears community').(acl('community create') ? ' '.$this->builder->a($this->builder->img('/gears/global/img/icon/edit.png',FALSE,FALSE,t('edit create')),l('/community/create/')) : FALSE),TRUE);
			$this->community->query();
			$config['per_page'] = $this->gears->community->per_page_list;
			$page = $this->pager((int)$action, $this->db->count_all_results('community',FALSE),$config);
			$this->db->limit($page['limit'],$page['start']);
			$all = $this->db->get('community')->result_array();
			if(!$all) info();
			else {
				foreach($all as &$community){
					if($community['icon']) {
						$community['icon'] = make_icons($community['icon']);
						$community['icon'] = $community['icon']['24x24'];
					}
					$community['class'] = 'avatar';
				}
				$header = array(
				'icon'=>array('','image','5%','class'=>'avatar'),
				'name'=>array(fc_t('community'),'link','30%',FALSE,'left','before'=>'<h1>','after'=>'</h1>'),
				'aname'=>array(fc_t('admin'),'link','10%','before'=>'<span class="user">','after'=>'</span>'),
				'users_num'=>array(fc_t('members'),'text','20%','before'=>'<h1>','after'=>'</h1>'),
				'nodes_num'=>array(fc_t('!gears nodes'),'text','20%','before'=>'<h1>','after'=>'</h1>')
				);
				$info = array(
				'link'=>array('/community','/user'),
				'link_add'=>array('url_name','aurl_name'),
				'noname'=>'true',
				);
				$this->form->grid('communities',$header,$all,$info)->compile();
			}
		}
		elseif($url_name && !is_numeric($url_name)){
			$this->community->show($url_name,$action,$subaction);
		}
		else {
			$this->db->where('nodes.cid != 0');
			$this->nodes->get((int)$url_name,FALSE,TRUE);
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Link to createdit method
	*
	* @return	void
	*/
	function create(){
		$this->createdit();
	}
	// ------------------------------------------------------------------------

	/**
	* Link to createdit method
	*
	* @return	void
	*/
	function edit($url_name = FALSE){
		if(!$url_name) show404();
		$this->createdit($url_name);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Create and edit communities
	*
	* @param	string	$url_name
	* @return	void
	*/
	function createdit($url_name = FALSE){
		if(!acl('community create')) return _403();
		$this->form->set('community_create')
		->input('name',array('validation'=>'required|callback_check_name','js_validation'=>'required|length[50]','ajax'=>array('name'=>t('!edit check'),'url'=>'/community/check_name/','where'=>'name.after')))
		->input('url_name',array('validation'=>'required|callback_check_url_name','js_validation'=>'required|length[15]','ajax'=>array('name'=>t('!edit check'),'url'=>'/community/check_url_name/','where'=>'url_name.after')))
		->image('icon',array(
			 'max_size' => 204,
			 'upload_path'=>_mkdir(ROOTPATH.'/uploads/community/logo/'),
			 'thumbs'=>isset($this->gears->community->logo->size) ? $this->gears->community->logo->size : $this->gears->user->avatar->size,
			 'width'=>'200',
			 'height'=>'200',
			 'file_name'=>url_name($this->input->post('url_name')),
			 'overwrite'=>TRUE
		 ))
		->textarea('description')
		->input('keywords')
		->checkbox('invites_only')
		->checkbox('private');
		if($url_name && $community = $this->db->get_where('community',array('url_name'=>$url_name))->row()){
			if(!acl('community edit_all') && $this->community->check($community->id) != 'admin') return _403();
			$this->session->set('form/data',$community);
			$this->form->title(fc_t('!edit edit').' '.t('community').' &laquo;'.$community->name.'&raquo;',-1,FALSE,TRUE)
			->set_values($community)
			->buttons('save',acl('community delete') ? 'delete|!community shure_delete' : FALSE);
		}
		else {
			$this->form->title(fc_t('!edit create').' '.t('community'),-1,FALSE,TRUE)
			->buttons('create');
			$community = FALSE;
		}
		if($result = $this->form->result()){
			$this->cache->tags('community')->clear();
			if(isset($community) && is_object($community)){
				$this->cache->clear('community/id/'.$community->id);
				$this->cache->clear('community/url_name/'.$community->url_name);
			}
			$this->session->remove('form/data');
			
			if($result['action'] == 'delete' && acl('community delete') && isset($community)){
				$this->form->delete('community',array('id'=>$community->id));
				$this->db->update('nodes',array('cid'=>0),array('cid'=>$community->id));
				$this->cache->tags('nodes')->clear();
				$this->db->delete('community_users',array('cid'=>$community->id));
				$this->community->refresh_user();
				redirect('/'.$this->name.'/');
			}
			if($community && $this->form->update('community',$result,array('id'=>$community->id))){
				redirect('/'.$this->name.'/'.$community->url_name);
			}
			else if($this->form->save('community',$result)){
				$this->db->insert('community_users',array('uid'=>$this->user->get('id'),'cid'=>$this->form->insert_id,'role'=>'admin'));
				$this->community->refresh_user();
				redirect('/'.$this->name.'/'.$result['url_name']);
			}
			$this->form->set_values($result);
		}
		$this->form->compile();
	}
	// ------------------------------------------------------------------------
	
	/**
	* Community name check
	*
	* @param	string
	* @return	boolean
	*/
	function check_name($name = FALSE){
		if(!$name){
			$this->form->set('community_check_name');
			$this->form->input('data',array('validation'=>'required|min_length[3]','label'=>t('!edit name')));
			$result = $this->form->result();
			if(!$result){
				$name_field = $this->form->find_by_name('data');
				ajax($name_field['error'],TRUE);				
			}
			$name = $result['data'];
			if($this->session->get('form/data')) $this->db->where('id !=',$this->session->get('form/data')->id);
			if(is_numeric($name)){
				ajax(t('!community name_numeric'),TRUE);
			}
			if(in_array($name,get_class_methods($this)) OR $this->db->where(array('name'=>$name))->get('community')->num_rows() > 0){
				ajax(t('!community name_already_owned'),TRUE);
			}
			else {
				ajax(TRUE,t('!community name_aviable'));
			}
		}
		else {		
			if(is_numeric($name)){
				$this->form_validation->set_message('check_name', t('!community name_numeric'));
				return FALSE;
			}
			else {
				if($this->session->get('form/data')) $this->db->where('id !=', $this->session->get('form/data')->id);
				$this->form_validation->set_message('check_name', t('!community name_already_owned'));
				return ($this->db->where(array('name'=>$name))->get('community')->num_rows() > 0 OR in_array($name,get_class_methods($this))) ? FALSE : 
				TRUE;
			}
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Community url_name check
	*
	* @param	string
	* @return	boolean
	*/
	function check_url_name($url_name = FALSE){
		if(!$url_name){
			$this->form->set('community_check_url_name');
			$this->form->input('data',array('validation'=>'required','label'=>t('!edit url_name')));
			$result = $this->form->result();
			if(!$result){
				$url_name_field = $this->form->find_by_name('data');
				ajax($url_name_field['error'],TRUE);				
			}
			$url_name = $result['data'];
			if($this->session->get('form/data')) $this->db->where('id !=',$this->session->get('form/data')->id);
			if(is_numeric($url_name)){
				ajax(t('!community name_numeric'),TRUE);
			}
			if(in_array(url_name($url_name),get_class_methods($this)) OR $this->db->where(array('url_name'=>url_name($url_name)))->get('community')->num_rows() > 0){
				ajax(t('!community url_name_already_owned'),TRUE);
			}
			else {
				ajax(TRUE,t('!community url_name_aviable'));
			}
		}
		else {		
			if(is_numeric($url_name)){
				$this->form_validation->set_message('check_name', t('!community name_numeric'));
				return FALSE;
			}
			else {
				if($this->session->get('form/data')) $this->db->where('id !=',$this->session->get('form/data')->id);
				$this->form_validation->set_message('name_already_owned', t('!community url_name_already_owned'));
				return ($this->db->where(array('url_name'=>url_name($url_name)))->get('community')->num_rows() > 0 OR in_array($url_name,get_class_methods($this))) ? FALSE : TRUE;
			}
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Change user state into community via ajax
	*
	* @param	string
	* @return	json
	*/
	function state($request = FALSE){
		$action = $request ? $request : $this->input->post('action');
		$cid = $this->input->post('cid');
		if(!$action OR !$cid) $request ? ajax(FALSE) : _404();
		if($this->community->check($cid) == 'admin') ajax(FALSE);
		$this->community->refresh_user();
		switch($action){
			case 'join':
				$this->community->flush($cid);
				if($request){
				 $pid = $this->input->post('pid');
				 if(!$pid) _404();
				 $this->db->delete('pm',array('id'=>$pid));
				 $this->db->update('community_users',array('approved'=>'true'),array('uid'=>$this->user->get('id'),'cid'=>$cid));
				 msg(t('joined'));
				 redirect('/community/'.$this->community->get($cid)->url_name);
				}
				else {
				 $this->db->insert('community_users',array('uid'=>$this->user->get('id'),'cid'=>$cid));
				 ajax(TRUE,t('joined'));
				}
			break;
			case 'leave':
				$this->db->delete('community_users',array('uid'=>$this->user->get('id'),'cid'=>$cid));
				 $this->community->flush($cid);
				if($request){
				 msg(t('leaved'));
				 redirect('/community/'.$this->community->get($cid)->url_name);
				}
				else {
				 ajax(TRUE,t('leaved'));
				}
			break;
		}
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------