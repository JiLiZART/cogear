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
 * Nodes model
 *
 * @package		CoGear
 * @subpackage	Nodes
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Nodes extends Model{
	public $published = TRUE;
	public $count = 0;
	/**
	* Constructor
	*
	* @return	void
	*/
	function Nodes(){
		parent::Model();
	}
	// ------------------------------------------------------------------------
	
	/**
	* Get nodes.
	*
	* @param	int	Page.
	* @param	int	Position.
	* @param	mixed	Params.
	* @param	boolean If there is no need to set order.
	* @param	mixed	Tags		
	* @return	void
	*/
	function _get($page = 0,$position = FALSE, $param = TRUE,$order_is_set = FALSE, $tags = FALSE){
		$CI =& get_instance();
		$CI->node->query();
		if($this->published) {
			$CI->db->where('nodes.published IS NOT NULL');
		}
		$CI->db->where('nodes.created_date < \''.date('Y-m-d H',time()+3800).'\'');
		// Make count all nodes extremely 
		// Swop all queries exclude WHERE, that really takes acion
		if(is_array($param)){
		 $this->count = count($param)-1;
		}
		else {
			$query = $this->db->_compile_select('SELECT COUNT(nodes.id) as numrows');
			$this->count = retrieve('count_all/'.md5($query));
			if(!$this->count){
				if($param !== TRUE){
					$this->db->swop('where,where_in');
				}
				$this->count = $this->db->count_all_results('nodes',FALSE);
				store('count_all/'.md5($query),$this->count,FALSE,'nodes/count_all');
				if($param !== TRUE){
					$this->db->swop();
				}
			}
		}
		$page_num = $page;
		$page = $CI->pager($page, $this->count);
		if(is_array($param)){
			rsort($param);
			$keys = array_slice($param,$page['start'] == 0 ? 0 : $page['start']+$page['limit'],$page['limit']);
			$this->db->where_in('nodes.id',$keys);
		}
		else {
			if(!$order_is_set){
				$this->db->order_by('nodes.created_date','desc');
			}
			$this->db->limit($page['limit'],$page['start']);
		}
		$query = $this->db->_compile_select();
		$nodes = FALSE;
		//retrieve('nodes_views/'.md5($query));
		if(!$nodes){
			$nodes = array4key($this->db->get('nodes')->result_array(),'id');
/*
			if(!is_array($tags)){
				$tags = explode(',',$tags);
			}
			$tags = $tags + array('nodes_views','nodes');
			foreach($nodes as $node){
				$tags[] = 'nodes/'.$node['id'];
			}
*/
			//store('nodes_views/'.md5($query),$nodes,FALSE,$tags);
		}
		else {
			$this->db->_reset_select();
		}
		if(is_array($param)){
			krsort($nodes);
		}
		if(count($nodes) > 0){
			$nodes = array2object($nodes);
			$this->show($nodes,$page,$position);
		}
		else {
			info(FALSE,FALSE,50);	
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Show nodes
	*
	* @param	object
	* @param	int
	* @param	int
	* @return	void
	*/
	function _show($nodes,$page,$position){
		$CI =& get_instance();
		foreach($nodes as $node){
			$CI->node->show($node,'short',$position);
			if(is_numeric($position)) $position++;
		}
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------