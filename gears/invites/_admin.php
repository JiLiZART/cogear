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
 * Invites CP controller
 *
 * @package		CoGear
 * @subpackage	Invites
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
		 d('invites');
		 $this->invites_tabs = new Panel('invites_tabs',FALSE,FALSE,'tabs');
		 $this->invites_tabs->set_title = TRUE;
		 $this->invites_tabs->links_base = '/admin/invites/';
		 $this->invites_tabs->add(array('name'=>'index','text'=>fc_t('%settings'),'index'=>TRUE));
		 $this->invites_tabs->add(array('name'=>'gift','text'=>fc_t('gift')));
 		 $this->invites_tabs->set_active(empty($this->uri->segments['3']) ? 'index' : $this->uri->segments['3']);
		 $this->invites_tabs->compile(12);	
	}
	// ------------------------------------------------------------------------
	
	/**
	* Index method
	*
	* @return
	*/
	function index(){
		$this->form->set('admin_invites')
		->checkbox('access_mode')
		->buttons('save')
		->set_values($this->gears->invites);
		if($result = $this->form->result(TRUE)){
			$this->info->set(GEARS.'invites/invites')->change('access_mode',$result['access_mode'])->compile();
			msg(t('form saved'));	
			$this->form->set_values($result);
		}		
		$this->form->compile();
	}
	
	/**
	* Show invites statistics, create invites
	*
	* @return	void
	*/
	function gift(){
		$this->form->set('invites')
		->input('users',array('validation'=>'required'))
		->input('count',array('validation'=>'required|is_natural_no_zero','js_validation'=>'required|digit'))
		->checkbox('grab')
		->buttons('create');
		if($result = $this->form->result(TRUE)){
			if(trim($result['users']) == '*'){
				$users = array4key($this->db->select('url_name')->order_by('id','asc')->get('users')->result_array(),FALSE,'url_name');
			}
			else $users = explode(',',$result['users']);
			$this->load->library('encrypt');
			foreach($users as $user){
				if($user = $this->user->info(url_name($user))){
					if($result['grab']){
							$this->db->limit($result['count'])->delete('invites',array('invites.from'=>$user->id,'invites.to'=>NULL));
					} else {
						for($i = 0; $i < $result['count']; $i++){
							$data = array(
							'datetime'=>date('H:i:s d.m.Y'),
							'username'=>$user->name,
							'count'=>$i
							);
							$invite = $this->encrypt->sha1(serialize($data));
							$this->db->insert('invites',array('invites.from'=>$user->id,'invite'=>$invite));		
						}
					}
				}
			}
			msg(t('!invites '.(isset($result['grab']) ? 'grabbed' : 'added')),TRUE);
		}
		$this->form->compile();
		$total_invited = $this->db->where('invites.to IS NOT NULL')->count_all_results('invites');
		$this->builder->div(TRUE,'line',TRUE);
		$this->builder->div(t('total_invited'),'left',TRUE);
		$this->builder->div($total_invited,'right',TRUE);
		$this->builder->div(FALSE,'line',TRUE);
		$total_invites = $this->db->where('invites.to IS NULL')->count_all_results('invites');
		$this->builder->div(TRUE,'line',TRUE);
		$this->builder->div(t('total_invites'),'left',TRUE);
		$this->builder->div($total_invites,'right',TRUE);
		$this->builder->div(FALSE,'line',TRUE);
		$this->builder->div('','clear',TRUE);
		d();
	}
	// ------------------------------------------------------------------------

}

// ------------------------------------------------------------------------