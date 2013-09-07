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
 * Favorites hooks
 *
 * @package		CoGear
 * @subpackage	Favorites
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	*  Add link-icon to node_info breadcrumb
	*
	* @param	object
	* @return	void
	*/
	function favorites_breadcrumb_compile_($Breadcrumb){
		if($Breadcrumb->name == 'node_title' && acl('favorites manage')){
			$CI =& get_instance();
			$status = !$CI->favorites->check($Breadcrumb->data->id) ? 'add' : 'remove';
			$Breadcrumb->add('<a href="javascript:void(0)"><img class="favorite-action" id="node-'.$Breadcrumb->data->id.'" src="/gears/favorites/img/icon/'.$status.'.png" title="'.t('!favorites '.$status).'" alt="'.t('!favorites '.$status).'"></a>',0);
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Add userinfo_tabs tab for favorites link
	*
	* @param	object
	* @return	void
	*/
	function favorites_panel_compile_($Panel){
		$CI =& get_instance();
		if($Panel->name == 'userinfo_tabs'){
			$count = $CI->cache->get('counters/favorites/users/'.$Panel->data->id,TRUE);
			if($count === FALSE){
				$count = $CI->db->where(array('uid'=>$Panel->data->id))->count_all_results('favorites');
				$CI->cache->tags('users/'.$Panel->data->id)->set('counters/favorites/users/'.$Panel->data->id,$count);
			}
			$count = $count > 0 ? ' ('.$count.')' : '';
			$CI->userinfo_tabs->add(array('name'=>'favorites','text'=>fc_t('!favorites favorite').$count,'link'=>l('/favorites/'.$Panel->data->url_name)));
			if($CI->name == 'favorites') {
				$Panel->set_active('favorites');
			}
		}
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------