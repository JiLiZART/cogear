<?php
/**
* Points model
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Points
* @version		$Id$
*/
class Points extends Model
{
	/**
	* User points
	*
	* @array
	*/
	private $votes = array();
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Model();
		if($this->user->is_logged() && !$this->votes = $this->session->get('votes',TRUE)){		
			$this->votes = $this->user_votes($this->user->get('id'));
			$this->session->set('votes',$this->votes);
			if($this->gears->points->charge->enabled){
				$last_charge_bonus = $this->user->get('last_charge_bonus');
				$day = time() - 24*60*60;
				if(!$last_charge_bonus OR strtotime($last_charge_bonus) < $day &&
				   !empty($this->gears->points->charge->bonus_points_require) && 
				   $this->gears->points->charge->bonus_points_require <= $this->user->get('points'))
			      {
				      $this->add_charge($this->user->get('id'),$this->gears->points->charge->bonus_amount);
				}
			}
		}
	}
	
	/**
	* Generate html code for vote
	*
	* @param	string	Type
	* @param	object	Target object
	* @param	boolean	Show points before vote
	* @param	boolean	Show votes count
	* @param	boolean Make numeric value round
	* @return	string	Output code
	*/
	public function code($type = 'node',$object = FALSE, $show_points = TRUE, $show_votes = TRUE,$round = TRUE){
		$case = $type == 'community' ? $type : $type.'s';
		if(!$this->user->is_logged() && empty($this->gears->points->$case->show_to_guests)){
			return '';
		}
		$tid = $object->id;
		$period = FALSE;
		switch($type){
			case 'node':
				if($this->gears->points->period->nodes){
					$period = time() - $this->gears->points->period->nodes*86400 > strtotime($object->created_date);
				}
			case 'comment':
				if($this->gears->points->period->comments){
					$period = time() - $this->gears->points->period->comments*86400 > strtotime($object->created_date);
				}
				$is_owner = $object->aid == $this->user->get('id') ? TRUE : FALSE;
			break;
			case 'community':
				$is_owner = $this->community->check($object) == 'admin' ? TRUE : FALSE;
			break;
			case 'user':
				$is_owner = $object->id == $this->user->get('id') ? TRUE : FALSE;
		}
		return $this->_template('points > points',array(
		'type' => $type,
		'id' => $tid,
		'is_owner' => $is_owner, 
		'show_points' => $show_points,
		'show_votes' => $show_votes,
		'voted' => isset($this->votes[$type][$tid]) ? $this->votes[$type][$tid]['points'] : FALSE,
		'votes' => $round ? round($object->points) : $object->points,
		'points_counter' => $object->points_counter,
		'period' => $period,
		),TRUE);
	}
	
	/**
	* Get user votes data
	*
	* @param	int		User id
	* @return	array
	*/
	private function user_votes($id = FALSE){
		if($result = $this->db->get_where('points',array('uid'=>$id))->result_array()){
			foreach($result as $point){
				$votes[$point['type']][$point['tid']] = array(
				'points'=>$point['points'],
				'created_date'=>$point['created_date']
				);
			}
			return $votes;		
		}
		return array();
	}
	
	/**
	* Check if user has voted
	*
	* @param	string	Type
	* @param	int		Target id
	* @return	mixed
	*/
	public function check($type = 'node', $tid = FALSE){
		return isset($this->votes[$type][$tid]);
	}
	
	/**
	* Calculate points based on user authority
	*
	* @param	string		Action
	* @param	numeric		Current points
	* @param	string		Type of point.
	* @return	numeric
	*/
	public function calculate_vote_points($action,$points,$table = 'nodes'){
		if($this->gears->points->$table->round){
			$add = 1+round($this->user->get('points')/100*$this->gears->points->strength);
		}
		else {
			$add = 1+round($this->user->get('points')/100*$this->gears->points->strength,2);
		}
		return $action == 'up' ? $points + $add : $points - $add;
	}
	
	/**
	* Check if user have vote ability
	*
	* @return	boolean
	*/
	public function user_can_vote(){
		return $this->user->get('points') > ($this->gears->points->min_to_vote ? $this->gears->points->min_to_vote : -1);
	}
	
	/**
	* Add charge to user
	*
	* @param	int	User id
	* @param	int	Votes
	* @return	void
	*/
	public function add_charge($uid,$votes = 1){
		if($user = $this->user->info($uid)){
			if($this->gears->points->charge->max && $user->charge >= $this->gears->points->charge->max) return TRUE;
			$this->db->update('users',array('charge'=>$user->charge+$votes,'last_charge_bonus' => date('Y-m-d H:i:s')
),array('id'=>$user->id));
			$this->user->refresh($user->id);
			return TRUE;
		}
		return FALSE;
	}
}