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
 * Blogs gear hooks
 *
 * @package		CoGear
 * @subpackage	Blogs
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	*  Add link to blog into node title
	*
	* @param	object $Breadcrumb
	* @return	void
	*/
	function blogs_breadcrumb_compile_(&$Breadcrumb){
		$CI =& get_instance();
		if($Breadcrumb->name == 'node_title'){
			$node = $Breadcrumb->data;
			$Breadcrumb->add(!in_array($CI->name,array('nodes','community')) ? ' '.$CI->gears->nodes->node->title_separator.' '.$CI->builder->a(t('!blogs blog').' '.$node->author,l('/blogs/'.$node->author_url_name)) : '',1);
		}
	}
	// ------------------------------------------------------------------------

	/**
	*  Add link to user blog into userinfo panel
	*
	* @param	object $Panel
	* @return	void
	*/
	function blogs_panel_compile_($Panel){
		$CI =& get_instance();
		if($Panel->name == 'userinfo_tabs'){
			$count = $CI->cache->get('counters/blogs/'.$Panel->data->id,TRUE);
			if($count === FALSE){
				$CI->db->swop();
				$count = $CI->db->where('published IS NOT NULL')->where('aid',$Panel->data->id)->count_all_results('nodes',FALSE);
				$CI->db->swop();
				$CI->cache->tags('users/'.$Panel->data->id)->set('counters/blogs/'.$Panel->data->id,$count);
			}
			$count = $count > 0 ? ' ('.$count.')' : '';
			$CI->userinfo_tabs->add(array('name'=>'blog','text'=>fc_t('!blogs blog').$count,'link'=>l('/blogs/'.$Panel->data->url_name)));
			if($CI->name == 'blogs') {
				$Panel->set_active('blog');
			}
		}
	}
	// ------------------------------------------------------------------------

	/**
	*  Add userinfo panel to full node view
	*
	* @param	object
	* @param	object
	* @param	string
	* @return	void
	*/
	function blogs_node_show_($CI,$node,$type){
		if($type == 'full'){
			$user = $CI->user->info($node->aid);
			$CI->user->head($user);
		}
	}
	// ------------------------------------------------------------------------

	/**
	*  Add blog path to node link 
	*
	* @param	object
	* @param	string
	* @param	object
	* @return	array
	*/
	function blogs_node_parse_url_($Node,$url,$node){
		$url = '/blogs/'.$node->author_url_name.''.$url;
		return func_get_args();
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------
