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
 * Node model
 *
 * @package		CoGear
 * @subpackage	Nodes	
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Node extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function Node(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Show node
	*
	* @param	object
	* @param	string
	* @param	boolean
	* @return	mixed
	*/
	function _show($node,$type = 'full',$return = FALSE){
		// Setting node title
		if(!$node) return _404();
		$CI =& get_instance();
		$title = $CI->breadcrumb;
		$title->set('node_title');
		$title->data($node);
		if(!isset($node->link)) $node->link = $this->create_link($node);
		$title->add('<a class="name" href="'.$node->link.'">'.$node->name.'</a>');
		if($this->user->get('id') == $node->aid OR acl('nodes edit_all')){
			if($node->published){
			 $title->add('<a href="'.l('/edit/'.$node->id).'"><img src="/gears/global/img/icon/edit.png" title="'.t('!edit edit').'" alt="'.t('!edit edit').'"></a>',100);
			}
			else {
			 $title->add('<a href="'.l('/edit/'.$node->id).'"><img src="/gears/nodes/img/icon/unpublished.png" title="'.t('!nodes unpublished').'" alt="'.t('!nodes unpublished').'"></a>',100);
			}
		}
		$node->title = $title->compile();
		$CI =& get_instance();
		if($type == 'full') title($node->name);
		$info =& $CI->breadcrumb;
		$avatar = reset(make_icons($node->avatar));
		//'!/gears/nodes/img/icon/time.png! '
		$info->set('node_info')->data($node)
		->add('<span>'.df($node->created_date).'</span>');
		if(acl('nodes views') && $this->gears->nodes->count_views){
			$info->add('<span class="nodes-views">'.t('nodes views',$node->views).'</span>',50);
		}
		$info->add('<a href="'.l('/blogs/'.$node->author_url_name).'"><img class="avatar" src="'.$avatar.'" alt="'.$node->author_url_name.'"></a><a href="'.l('/user/'.$node->author_url_name).'">'.$node->author.'</a>',40);
		$node->info = $info->compile();
		if($type == 'full' && $this->gears->nodes->count_views){
			$this->db->update('nodes',array('views'=>++$node->views),array('id'=>$node->id));
		}
		return $this->_template('nodes node',array('node'=>$node),$return);
	}
	// ------------------------------------------------------------------------

	/**
	* Get node
	*
	* @param	mixed
	* @param	string
	* @return	object
	*/
	function _get($param,$field = 'nodes.id'){
		$this->query();
		if(is_array($param)) $this->db->where($param);
		else $this->db->where($field,$param);
		return $this->db->get('nodes');
	}
	// ------------------------------------------------------------------------
	
	/**
	* Create link for ndoe
	*
	* @param	object
	* @param	string
	* @return	string
	*/
	function _create_link($node,$suffix = FALSE){
		$CI =& get_instance();
		$url = $CI->gears->nodes->node->url;
		return l($this->parse_url($url,$node),$suffix ? $CI->gears->nodes->node->suffix.$suffix : $CI->gears->nodes->node->suffix);
	}
	// ------------------------------------------------------------------------

	/**
	* Parse node url
	*
	* @param	string
	* @param	object
	* @return	string
	*/
	function _parse_url($url,$node){
		$CI =& get_instance();
		$url = strtrim($url,'()');
		preg_match_all('/([\/-]?)%([\w]*)%/i',$url,$matches);
		foreach($matches[2] as $key=>$field){
			if(isset($node->$field)) $url = str_replace($matches[0][$key],$matches[1][$key].$node->$field,$url);
			else {
				$url = str_replace($matches[0][$key],'',$url);
			}
		}
		return $url;
	}
	// ------------------------------------------------------------------------

	/**
	* Node query
	*
	* @param	boolean		$full	
	* @return	void
	*/
	function _query(){
		$CI =& get_instance();
		$CI->db->select('nodes.*',FALSE);
		$CI->db->select('u.avatar,u.url_name as author_url_name,u.name as author');
		$CI->db->join('users u','u.id = nodes.aid','inner');
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------