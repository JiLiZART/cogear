<?php
/**
* Points control panel.
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Points
* @version		$Id$
*/
class _Admin extends Controller
{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();
		 $this->points_tabs = new Panel('points_tabs',FALSE,FALSE,'tabs');
		 $this->points_tabs->set_title = TRUE;
		 $this->points_tabs->links_base = '/admin/points/';
		 $this->points_tabs->add(array('name'=>'index','text'=>fc_t('%settings'),'index'=>TRUE));
		 $this->points_tabs->add(array('name'=>'bonuses','text'=>fc_t('points_admin bonuses')));
 		 $this->points_tabs->set_active(isset($this->uri->segments[3]) ? $this->uri->segments[3] : 'index');
		 $this->points_tabs->compile(12);	
	}
	
	/**
	* Show options
	*
	* @return void
	*/
	public function index(){
		d('points_admin');
		$this->form->set('admin_points')->label_after(TRUE)
		->input('min_to_vote',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->input('index_points',array('vadlidation'=>'integer','js_validation'=>'digit'))
		// Charge
		->fieldset('charge',t('charge'))
		->checkbox('charge[enabled]')
		->input('charge[bonus_amount]',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->input('charge[bonus_points_require]',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->input('charge[max]',array('vadlidation'=>'integer','js_validation'=>'digit'))
		// Nodes
		->fieldset('nodes',t('gears nodes'))
		->checkbox('nodes[enabled]')
		->checkbox('nodes[show_points]')
		->checkbox('nodes[show_votes]')
		->checkbox('nodes[show_to_guests]')
		->checkbox('nodes[round]')
		->fieldset()
		// Comments
		->fieldset('comments',t('gears comments'))
		->checkbox('comments[enabled]')
		->checkbox('comments[show_points]')
		->checkbox('comments[show_votes]')
		->checkbox('comments[show_to_guests]')
		->checkbox('comments[round]')
		->fieldset()
		// Users
		->fieldset('users',t('gears users'))
		->checkbox('users[enabled]')
		->checkbox('users[show_points]')
		->checkbox('users[show_votes]')
		->checkbox('users[show_to_guests]')
		->checkbox('users[round]')
		->fieldset()
		// Community
		->fieldset('community',t('gears community'))
		->checkbox('community[enabled]')
		->checkbox('community[show_points]')
		->checkbox('community[show_votes]')
		->checkbox('community[show_to_guests]')
		->checkbox('community[round]')
		->fieldset()
		->buttons('save')
		->set_values($this->gears->points);
		if($result = $this->form->result(TRUE)){
			foreach(array('charge','nodes','comments','users','community') as $type){
				foreach($this->gears->points->$type as $param=>$value){
					if(!isset($result[$type][$param])) $result[$type][$param] = FALSE;
				}
			}
			$this->info->set('gears/points/points')->change($result)->compile();
			msg(t('form updated'));
			$this->form->set_values($result);
		}
		$this->form->compile();
	}
	
	/**
	* Manage bonuses
	*
	* @return void
	*/
	public function bonuses(){
		foreach($this->user_groups->get_list(0) as $id=>$group){
			$options[$id] = $group['name'];
		}
		d('points_admin_bonuses');
		$this->form->set('admin/points/bonuses')
		->input('names',array('autocomplete'=>array('url'=>'/user/autocomplete/','multiple'=>TRUE)))
		->select('user_groups',array('multiple'=>TRUE,'options'=>$options))
		->input('points',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->select('points_logic',array('options'=>array('<' => t('smaller'),'<=' => t('smaller_equals'),'=' => t('equals'),'>' => t('bigger'),'>=' => t('bigger_equals'))))
		->input('points_counter',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->select('points_counter_logic',array('options'=>array('<' => t('smaller'),'<=' => t('smaller_equals'),'=' => t('equals'),'>' => t('bigger'),'>=' => t('bigger_equals'))))
		->input('charge',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->select('charge_logic',array('options'=>array('<' => t('smaller'),'<=' => t('smaller_equals'),'=' => t('equals'),'>' => t('bigger'),'>=' => t('bigger_equals'))))
		->datetime('reg_date',array('value' => '2008-1-1 00:00:00','range'=>'2008-'.date('Y')))
		->datetime('last_visit',array('value' => '2008-1-1 00:00:00','range'=>'2008-'.date('Y')))
		->input('charge_gift',array('vadlidation'=>'integer','js_validation'=>'digit'))
		->buttons('gift');
		if($result = $this->form->result()){
			extract($result);
			if(!empty($names)){
				$names = _explode(',',$names);
				$names = array_unique($names);
				$users = $this->db->where_in('name',$names)->get('users')->result();

			}
			else {
				if(!empty($user_groups)){
					$this->db->where_in('user_group',$user_groups);
				}
				if(!empty($points)){
					$this->db->where('points '.$points_logic.' '.$points);
				}
				if(!empty($points_counter)){
					$this->db->where('points_counter '.$points_counter_logic.' '.$points_counter);
				}
				if(!empty($charge)){
					$this->db->where('charge '.$charge_logic.' '.$charge);
				}
				if(!empty($reg_date)){
					$this->db->where('reg_date > "'.$reg_date.'"');
				}
				if(!empty($last_visit)){
					$this->db->where('last_visit > "'.$last_visit.'"');
				}
				$users = $this->db->get('users')->result();
			}
			if(!empty($users) && !empty($charge_gift)){
				foreach($users as $user){
					$this->db->update('users',array('charge'=>$user->charge+$charge_gift),array('id'=>$user->id));
					$this->user->refresh($user->id);
				}
				msg(t('charge_gifted'));
			}
		}
		$this->form->compile();		
	}
}