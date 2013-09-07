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
 * Community hooks
 *
 * @package		CoGear
 * @subpackage	Community
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add community select to node_createdit form
	*
	* @param	object
	* @return	void
	*/
	function community_form_result_($Form){
		$CI =& get_instance();
		if($Form->name == 'node_createdit'){
			if(acl('community change_node_all_communities')){
				$communities = $CI->db->order_by('name','asc')->get('community')->result_array();
			}
			else $communities = $CI->community->get_roles();
			if($communities){
				$options = array();
				foreach($communities as $community){
					if(is_array($community)) $community = array2object($community);
					$options[$community->id] = $community->name;
				}
				// We need to sort options the way personal blog be the first 
				// and all communities sort into alphabetical order
				asort($options);
				$keys = array_keys($options);
				array_unshift($keys,0);
				$values = array_values($options);
				array_unshift($values,fc_t('!blogs personal_blog'));
				$options = array_combine($keys,$values);
				if(acl('community change_node_community') OR empty($Form->data->cid)){
					$Form->select('cid',array(
					'options'=>$options,
					'label'=>fc_t('!community posting'),
					'description'=>fc_t('!community posting_description'),
					'validation'=>'required'
					),3);
					// Now if you click on "Create new topic" while browsing some community
					// it will be automatically chosen in community list.
					if(isset($_SERVER['HTTP_REFERER']) && $uri_string = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_PATH)){
						$uri_segments = explode('/',trim($uri_string,'/ '));
						if(count($uri_segments) >= 2 && $uri_segments[0] == 'community'){
							$community_url_name = $uri_segments[1];
							if($community = $CI->community->get($community_url_name)){
								$Form->data = new StdClass();
								$Form->data->cid = $community->id;
							}
							
						}
					}
					
					if(isset($Form->data->cid)) $Form->set_values(array('cid'=>$Form->data->cid));
					else $Form->set_values(array('cid'=>0));
				}
				else if(!empty($Form->data->cid)){
					 $Form->input('cid',array(
					'value'=>$CI->community->get($Form->data->cid)->name,
					'label'=>fc_t('!community community'),
					'disabled'=>TRUE
					),3);
				}
				if(isset($Form->data->cid)) $CI->cache->tags('community,community/'.$Form->data->cid)->clear();
				else $CI->cache->tags('community')->clear();
			}
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Extend node query for get community data
	*
	* @param	object
	* @return	void
	*/
	function community_nodes_get_($Node){
		if(!acl('community view_private')){
			$CI =& get_instance();
			$where = 'community.private IS NULL';
			if($CI->community->roles){
				$keys = array_keys($CI->community->roles);
				$where = '( '.$where.' OR community.id IN ('.implode(',',$keys).') )';
			}			
			$Node->db->where($where);
		}
		$Node->db->join('community','community.id = nodes.cid','left');
	}
	// ------------------------------------------------------------------------
	
	/**
	* Add breadcrumbs community buttons
	*
	* @param	object
	* @return	void
	*/
	function community_breadcrumb_compile_(&$Breadcrumb){
		$CI =& get_instance();
		if($Breadcrumb->name == 'node_title' && $Breadcrumb->data->cid != 0){
			if($CI->community->info && isset($Breadcrumb->data->community)){
				if($community = $Breadcrumb->data->community){
					if($community->icon){
					 $icon = '<img src="'.reset(make_icons($community->icon,$CI->gears->community->logo->size)).'">';
					}
					else $icon = FALSE;
					$Breadcrumb->add(!in_array($CI->name,array('nodes','community')) ? ' '.$CI->gears->nodes->node->title_separator.' '.$icon.' <a href="'.l('/community/'.$community->url_name).'">'.$community->name.'</a>' : '',1,TRUE);
				}
			}
			else {
				unset($Breadcrumb->elements[1]);
			}
		}
		else if($Breadcrumb->name == 'comments_widget' && isset($Breadcrumb->data->community->id)){
			$CI =& get_instance();
			$Breadcrumb->add('<a href="'.l('/community/'.$Breadcrumb->data->community->url_name).'">'.$Breadcrumb->data->community->name.'</a>',2,TRUE);
		}
		if(in_array($Breadcrumb->name,array('pm_reply','inbox_read')) && !empty($Breadcrumb->data->comm_id)){
			$CI =& get_instance();
			$CI->no_comments = TRUE;
			$CI->form->set('community_pm')
			->hidden('pid',array('value'=>$Breadcrumb->data->id))
			->hidden('cid',array('value'=>$Breadcrumb->data->comm_id))
			->buttons(array('submit'=>array(
			'value'=>fc_t('!community join'))));
			$CI->form->action = l('/community/state/join/');
			$Breadcrumb->add($CI->form->compile(TRUE),0,TRUE);
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Show user community roles in profile
	*
	* @param	object
	* @return	void
	*/
	function community_panel_compile_($Panel){
			if($Panel->name == 'userinfo_profile'){
				$CI =& get_instance();
				$roles = $CI->community->get_roles($Panel->data->id);
				if(!$roles) return FALSE;
				$item['type'] = 'line';
				$item['left'] = fc_t('!community participate_in');
				foreach($roles as $role){
					$role_class = $CI->community->check($role['id']);
					if($Panel->data->id == $CI->user->get('id') && $role_class != 'admin'){
						$role_class = FALSE;
					}
					$communities[] = $CI->builder->a($role['name'],l('/community/'.$role['url_name']),'community '.$role_class);
				}
				$item['right'] = implode(', ',$communities);
				$Panel->add($item,5);			
			}
	}
	// ------------------------------------------------------------------------

	/**
	*  Show community panel before node if it belongs to
	*
	* @param	object
	* @param	object
	* @param	string
	* @return	void
	*/
	function community_node_show_($Node,$node,$type){
		$CI =& get_instance();
		if($node->cid != 0){
			$node->community = $CI->community->get((int)$node->cid);
		}
		if($type == 'full' && isset($node->community)){
			$CI->community->show($node->community->url_name,TRUE);
			// Mixin meta
			if(!empty($node->community->keywords)) meta($node->community->keywords,'keywords');
			if(!empty($node->community->description)) meta($node->community->description,'description');

		}
	}
	// ------------------------------------------------------------------------

	/**
	* Check pm to invite user into community
	*
	* @param	object
	* @param	object
	* @return	boolean
	*/
	function community_pm_user_get_($Pm,$user){
		if($Pm->name == 'community_invite'){
			$CI =& get_instance();
			if($CI->db->get_where('community_users',array(
				'uid'=>$user->id,
				'cid'=>$Pm->data->id
			))->row()){
				msg(t('!community invited_already',$user->name),FALSE);
				return FALSE;
			}
			return TRUE;
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Link user to community without approval after invite pm send success
	*
	* @param	object
	* @param	object
	* @param	array
	* @return	void
	*/
	function community_pm_sent_success_($Pm,$user,$data){
		if($Pm->name == 'community_invite'){
			$CI =& get_instance();
			$community =& $Pm->data;
			$CI->db->insert('community_users',array(
				'uid'=>$user->id,
				'cid'=>$community->id,
				'approved'=>NULL,
				'pm'=>$Pm->insert_id		
			));
		}
	}
	
	function community_inbox_sent_success_($Pm,$users,$data = FALSE){
		if($Pm->name == 'community_invite'){
			$CI =& get_instance();
			$community =& $Pm->data;
			$users = explode(',',$users);
			foreach($users as $uid){
				$CI->db->insert('community_users',array(
					'uid'=>$uid,
					'cid'=>$community->id,
					'approved'=>NULL,
					'pm'=>$Pm->insert_id		
				));
			}
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Enhance mail query to catch invite pm
	*
	* @param	object
	* @return	void
	*/
	function community_mail_view($CI){
		$CI->db->select('community_users.approved as comm_approved,community_users.cid as comm_id',FALSE);
		$CI->db->join('community_users','community_users.pm = pm.id','LEFT');
	}

	function community_mail_read($CI){
		$CI->db->select('community_users.approved as comm_approved,community_users.cid as comm_id',FALSE);
		$CI->db->join('community_users','community_users.pm = pm.id','LEFT');
		/* $CI->db->where('pm.from !=',$CI->user->get('id')); */
	}
	
/*
	function community_mail_construct($CI){
		$CI->db->swop();
		if($no_approved_invites = $CI->db->join('pm p','p.id = cu.pm AND p.from = '.$CI->user->get('id'))->get('community_users cu')->result()){
			$CI->db->swop();
			$ids = array();
			foreach($no_approved_invites as $invite){
				$ids[] = $invite->pm;
			}
			if($ids) $CI->db->where_not_in('pm.id',$ids);
		}
		else	$CI->db->swop();
	}
*/
	// ------------------------------------------------------------------------

	
	/**
	*  Extend node url with community path
	*
	* @param	object
	* @param	string
	* @param	object
	* @return	array
	*/
	function community_node_parse_url_($Node,$url,$node){
		$CI =& get_instance();
		if($node->cid != 0){
		     if(!isset($node->community)) $node->community = $CI->community->get($node->cid);
		     if(!empty($node->community)) $url = '/community/'.$node->community->url_name.'/'.substr($url,strrpos($url,'/')+1);
		}
		return func_get_args();
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------