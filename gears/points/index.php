<?php
/**
* Points controller
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Points
* @version		$Id$
*/
class Index extends Controller
{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();
	}
	
	/**
	* Users top by points
	*
	* @param	int	Page
	* @return	void
	*/
	function index($page = 0){
		$title = t('!points top');
		$this->builder->h1($title,TRUE);
		title($title,TRUE,TRUE);

		$config['per_page'] = $this->gear->top->per_page;
		$page = $this->pager((int)$page, $this->db->count_all_results('users',FALSE),$config);
		$this->db->limit($page['limit'],$page['start']);

		$users = $this->db->order_by('points','desc')->get('users')->result_array();
		foreach($users as &$user){
			$user['avatar'] = reset(make_icons($user['avatar']));
		}
		$header = array(
		'avatar'=>array('','image','5%','class'=>'avatar', 'alt'=>'avatar'),
		'name'=>array(fc_t('!user name'),'link','30%','left','before'=>'<h1>','after'=>'</h1>'),
		'points'=>array(fc_t('!points top_rating'),'text','20%','before'=>'<h1>','after'=>'</h1>'),
		);
		if($this->gear->users->show_voted){
		 $header['points_counter'] = array(fc_t('!points top_voted'),'text','20%','before'=>'<h1>','after'=>'</h1>');
		}
		$info = array(
		'link'=>array('/user'),
		'link_add'=>array('url_name'),
		'noname'=>'true',
		);
		$this->form->grid('top',$header,$users,$info)->compile();
	}
	
	/**
	* Process voting
	*
	* @return json
	*/
	public function vote(){
		$action = $this->input->post('action');
		$type = $this->input->post('type');
		$id = $this->input->post('id');
		if(!$action OR 
		   !$type OR 
		   !$id OR 
		   !in_array($type,array('node','comment','user','community')))
		   { 
			   return _403();
		   }
		/*
		* Set default values
		*/
		$success = FALSE;
		$msg = FALSE;
		$points = 0;
		$charge = 0;
		$table = $type == 'community' ? $type : $type.'s'; // node Ñ> nodes
		/*
		* Set object of interests
		*/
		if(!$object = $this->db->get_where($table,array('id'=>$id))->row()){
			$msg = t('points vote_nothing');
		}
		/*
		* Check if user if logged
		*/
		if(!$this->user->is_logged()){
			$msg = t('points registred_only');
		}
		/*
		* Check self voting
		*/
		elseif($type == 'user' && $object->id == $this->user->get('id') 
		    OR isset($object->aid) && $object->aid == $this->user->get('id')){
			$msg = t('points vote_self');
		}
		/*
		* Check charge
		*/
		elseif(!$this->user->get('charge') && $this->gears->points->charge->enabled){
			$msg = t('points empty_charge');
		}
		/*
		* Check votes duplicate
		*/
		elseif($this->points->check($type,$id)){
			$msg = t('points duplicate');
		}
		if($type == 'node' && $this->gears->points->period->nodes){
			if($period = time() - $this->gears->points->period->nodes*86400 > strtotime($object->created_date)){
				$msg = t('points period');
			}
		}
		elseif($type == 'comment' && $this->gears->points->period->comments){
			if($period = time() - $this->gears->points->period->comments*86400 >  strtotime($object->created_date)){
				$msg = t('points period');
			}
		}
		/*
		* Check if user is able to vote
		*/
	   if($msg === FALSE && $this->points->user_can_vote()) {
			$points = $action == 'zero' ? $object->points : $this->points->calculate_vote_points($action,$object->points,$table);
			// Insert log data
			if($this->db->insert('points',array(
			'type' => $type,
			'tid' => $id,
			'uid' => $this->user->get('id'),
			'points' => $points
			)) && 
			// Update target data
			$this->db->update($table,array(
			'points' => $points,
			'points_counter' => $object->points_counter + 1
			),array('id'=>$object->id))){
				if($this->gears->points->charge->enabled){
					// Update charge info
					$charge = $this->user->get('charge')-1;
					$this->db->update('users',array('charge'=>$charge),array('id'=>$this->user->get('id')));
					$this->user->refresh();
					// Move charge to target user
					if($this->gears->points->charge->gift){
						switch($type){
							case 'node':
							case 'comment':
								$uid = $object->aid;
								break;
							case 'user':
								$uid = $object->id;
								break;
							case 'community':
								$uid = $this->db->get_where('community_users',array('cid'=>$object->id,'role'=>'admin'))->row()->uid;
								break;		
						}
						$this->points->add_charge($uid);
					}
					// Flush node cache by tag
					$this->cache->tags($table.'/'.$object->id)->clear();
				}
				if($type == 'node' && $this->gears->points->index_points){
					   if($object->points < $this->gears->points->index_points && $points >= $this->gears->points->index_points && empty($object->promoted)){
						   $this->indexer->promote($object->id,FALSE);
					   }
					   elseif($object->points >= $this->gears->points->index_points && $points < $this->gears->points->index_points && !empty($object->promoted)){
						   $this->indexer->depromote($object->id);
					   }
				}
				// Flush points
				$this->session->set('votes',FALSE);
				$success = TRUE;
				$msg = t('points success');
			}
		}
		if(!$msg) $msg = t('points failure');
		echo json_encode(array(
		'success' => $success,
		'msg' => $msg,
		'points' => $points,
		'points_counter' => t('points points_counter',$object->points_counter + 1),
		'charge' => $charge,
		'charge_plural' => t('points charge',$charge)
		));
		exit();
	}
	
	/**
	* Add charge to user
	*
	* @return	json
	*/
	public function add_charge(){
		$uid = $this->input->post('uid');
		if($uid && acl('points add_charge') && $this->points->add_charge($uid,$this->input->post('charge'))){
			ajax(TRUE);
		}
		ajax(FALSE);
	}
}