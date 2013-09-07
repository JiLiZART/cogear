<?php
/**
* Index model
*
* It's called indexer, because Index name is reserved for Controller.
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Index
* @version		$Id$
*/
class Indexer extends Model
{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Model();
	}
	
	/**
	* Promote node to index
	*
	* @param	int	Node id
	* @param	boolean Promotion time update.
	* @return	
	*/
	public function promote($id = 0, $update_time = FALSE){
		if($node = $this->db->get_where('nodes',array('id'=>$id))->row()){
			if($node->promoted_date != '0000-00-00 00:00:00' && $node->promoted) return FALSE;
			else {
				$data['promoted'] = 'true';
				$time = strtotime($node->promoted_date);
				if($update_time OR empty($time) OR $node->promoted_date == '0000-00-00 00:00:00' OR empty($node->promoted_date)) $data['promoted_date'] = date('Y-m-d H:i:s');
				$this->db->update('nodes',$data,array('id'=>$node->id));
				$this->flush($node->id);
				return TRUE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Depromote node from index
	*
	* @param	int	Node id
	* @return
	*/
	public function depromote($id = 0){
		if($node = $this->db->get_where('nodes',array('id'=>$id))->row()){
			if(empty($node->promoted)) return FALSE;
			else {
				$this->db->update('nodes',array('promoted'=>NULL),array('id'=>$node->id));
				$this->flush($node->id);
				return TRUE;
			}
		}
		else {
			return FALSE;
		}	
	}
	
	/**
	* Flush cache
	*
	* @param	int	Node id.
	* @return	void
	*/
	public function flush($id){
		$last_promoted = $this->db->order_by('promoted_date','desc')->limit(1)->get('nodes')->row();
		$this->cache->tags(array(
		'nodes/'.$id,
		'nodes/'.$last_promoted->id
		))->clear();
	}
}