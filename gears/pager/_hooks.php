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
 * Pager hooks 
 *
 * @package		CoGear
 * @subpackage	Pager
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Create controller method
	*
	* @param	object
	* @param	int
	* @param	int
	* @param	array
	* @return	array
	*/
	function _pager($CI,$page = 0,$count = FALSE,$config = FALSE){
			$page = str_replace($CI->gears->pager->prefix,'',$page);
			if($page < 0) $page = 0;
			$CI->page_num = $page;
			$CI->total_rows = $count;
			if(isset($config['per_page'])){
				$per_page = $config['per_page'];
			} 
			else {
				$per_page = isset($CI->gear->per_page) ? $CI->gear->per_page : $CI->site->per_page;
			}
			$num_pages = ceil($count/$per_page);
			$real_page = $page == 0 ? 0 : $num_pages - $page;
/*
			if($page != 0 && $page != $num_pages) $start = $count - ($per_page*$page);
			else 
*/
			$start = $real_page*$per_page;
			if($start < 0) $start = 0;
			if($config) $CI->pager_config = $config;
			$data = array('start'=>$start,'limit'=>(int)$per_page);
			//->order_by('id','desc')
			$CI->db->limit($data['limit'],$data['start']);
			return $data;
	}
	// ------------------------------------------------------------------------

	/**
	*  Show pager
	*
	* @param	object
	* @return	void
	*/
	function pager_after($CI){
		   if(!isset($CI->page_num)) return;
		   $pieces = array_search($CI->method,$CI->uri->segments) ? array_slice($CI->uri->segments,0,array_search($CI->method,$CI->uri->segments)) : $CI->uri->segments;
		   if(is_numeric(end($pieces)) OR strpos(end($pieces),$CI->gears->pager->prefix) !== FALSE) array_pop($pieces);
		   $config['base_url'] = l('/'.implode('/',$pieces).'/');
		   $config['per_page'] = isset($CI->gear->per_page) ? $CI->gear->per_page : $CI->site->per_page; 
		   $config['total_rows'] = $CI->total_rows;
		   $config['cur_page'] = $CI->page_num;
		   if(isset($CI->pager_config)) $config = array_merge($config,$CI->pager_config);
		   $CI->_template(array($CI->pager->set($config)->create_links()),800); 
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------