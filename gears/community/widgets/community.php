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
 * Community widget
 *
 * @package		CoGear
 * @subpackage	Community
 * @category		Gears widgets
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Community widget
	*
	* @param	object
	* @param	array
	* @return	string
	*/
	function community_widget($CI,$config){
		$communities = retrieve('sidebar/widgets/community');
		if($communities === FALSE){
		$CI->community->query();
		if($communities = $CI->db->order_by('nodes_num','desc')->limit($config->num)->get('community')->result()){
			$output = array();
			$CI->builder->stop();
			foreach($communities as $community){
				$CI->breadcrumb->set('community_widget','')->data($community);
				// This thing will be avatar in allcomments page. Do not delete.
				if($community->icon){
					$icon = '<img class="avatar" width="24" src="'.reset(make_icons($community->icon,$CI->gears->community->logo->size)).'">';
				}
				else $icon = '&nbsp;';
				$CI->breadcrumb->add('<td width="10%" align="center">'.$icon.'</td><td width="40%"><a href="'.l('/community/'.$community->url_name).'">'.$community->name.'</a></td>');
				$CI->breadcrumb->add('<td align="center"><a href="'.l('/community/'.$community->url_name.'/members').'">'.$community->users_num.'</a></td>');
				$CI->breadcrumb->add('<td align="center"><a href="'.l('/community/'.$community->url_name).'">'.$community->nodes_num.'</a></td>');
				$output[] = '<tr>'.$CI->breadcrumb->compile().'</tr>';
			}
			$output = $CI->builder->table($CI->builder->thead('<td colspan=2></td><td>'.fc_t('!community members').'</td><td>'.fc_t('!gears nodes').'</td>').$CI->builder->tbody(implode('',$output)));
			$output .=$CI->builder->div($CI->builder->a(t('!community all_communities'),l('/community/all/')),'tright');
			$communities = $CI->builder->div($output,FALSE,'community_widget');
		}
		store('sidebar/widgets/community',$communities,FALSE,'community,nodes');
		}
		if(!$communities)	 return t('!errors empty').$CI->builder->div($CI->builder->a(t('!community all_communities'),l('/community/all/')),'tright');
		return $communities;
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------