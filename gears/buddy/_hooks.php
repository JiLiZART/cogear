<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author		CodeMotion, Dmitriy Belyaev
 * @copyright	Copyright (c) 2009, CodeMotion
 * @license		http://cogear.ru/license.html
 * @link		http://cogear.ru
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Buddy hooks
 *
 * @package		CoGear
 * @subpackage	Buddy
 * @category	Gears hooks
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
	/*
	* Add icon-button to userinfo panel (with form to write buddy-pm) and add link-button to approve relationship into pm_reply form
	*
	* @param object
	* @return void
	*/
	function buddy_breadcrumb_compile_($Breadcrumb){
		$CI =& get_instance();
		if($Breadcrumb->name == 'userinfo_panel' && $Breadcrumb->data->id != $CI->user->get('id') && $CI->user->get('id')){
			$action = 'add';
			if($CI->buddy->check($Breadcrumb->data->url_name,TRUE)){
				$action = 'remove';
			}
			$Breadcrumb->add('<a href="'.l($CI->uri->uri_string).'" id="buddy-'.$Breadcrumb->data->id.'"><img src="/gears/buddy/img/icons/'.$action.'.png" title="'.t('!buddy '.$action).'"></a>',3);
			js('/gears/buddy/js/buddy.js');
			$CI->form->set('buddy')
			->title('!buddy '.$action.'_title',FALSE,FALSE,FALSE)
			->textarea('msg',array('js_validation'=>'required','label_hidden'=>TRUE,'description'=>' ','class'=>'no-grow'))
			->buttons('submit')
			->set_values(array('msg'=>t('!buddy '.$action.'_msg')));
			$CI->builder->div($CI->form->compile(TRUE).'<br>','exact hidden','buddy-holder',TRUE);
			d();
		}
		if(in_array($Breadcrumb->name,array('pm_reply','inbox_read')) && isset($Breadcrumb->data->buddy_from)){
			$CI->no_comments = TRUE;
			$CI->form->set('buddy_pm')
			->hidden('pm',array('value'=>$Breadcrumb->data->id))
			->hidden('from',array('value'=>$Breadcrumb->data->from))
			->hidden('approved',array('value'=>!$Breadcrumb->data->approved))
			->buttons(array('submit'=>array(
			'value'=>t('!buddy '.(!$Breadcrumb->data->approved ? 'add' : 'remove')))
			));
			$CI->form->action = l('/buddy/approve/');
			$Breadcrumb->add($CI->form->compile(TRUE),0,TRUE);
		}
	}
	// ------------------------------------------------------------------------

	
	/*
	* Extends pm view query
	*
	* @param object
	* @return void
	*/
	function buddy_mail_view($CI){
		$CI->db->select('buddies.from as buddy_from, buddies.to as buddy_to, buddies.approved as approved',FALSE);
		$CI->db->join('buddies','buddies.pm = pm.id','LEFT');
	}
	
	function buddy_mail_read($CI){
		$CI->db->select('buddies.from as buddy_from, buddies.to as buddy_to, buddies.approved as approved',FALSE);
		$CI->db->join('buddies','buddies.pm = pm.id','LEFT');
		//$CI->db->where('pm.from !=',$CI->user->get('id'));
	}
	
/*
	function buddy_mail_construct($CI){
		$CI->db->swop();
		if($no_approved_buddies = $CI->db->get_where('buddies',array('from'=>$CI->user->get('id'),'approved'=>NULL))->result()){
			$CI->db->swop();
			$ids = array();
			foreach($no_approved_buddies as $buddy){
				$ids[] = $buddy->pm;
			}
			if($ids) $CI->db->where_not_in('pm.id',$ids);
		}
		else	$CI->db->swop();

	}
*/
	// ------------------------------------------------------------------------

	
	/*
	* Add buddies list into userinfo profile
	*
	* @param object
	* @return void
	*/
	function buddy_panel_compile_($Panel){
			if($Panel->name == 'userinfo_profile'){
				$CI =& get_instance();
				$buddies = $CI->buddy->get($Panel->data->id);
				if(!$buddies) return FALSE;
				$item['type'] = 'line';
				$item['left'] = t('!gears buddy');
				foreach($buddies as $user){
					$users[] = $CI->builder->a($user['name'],l('/user/'.$user['url_name']),'user '.($CI->buddy->check($user['name']) !== FALSE && $CI->user->get('id') != $Panel->data->id ? 'buddy' : FALSE));
				}
				$item['right'] = implode(', ',$users);
				$Panel->add($item,4);			
			}
	}
	// ------------------------------------------------------------------------
	
	/*
	*  Extends create_mail and community_invite form with autocompleter.
	*
	* @param object
	* @return void
	*/
	function buddy_form_compile_($Form){
		if($Form->name == 'create_mail'){
			$to = $Form->find('to');
			$to['autocomplete'] = array('url'=>'/buddy/search/ajax/','multiple'=>TRUE);
			$Form->elements[$to['key']] = $to;
		}
		if($Form->name == 'community_invite'){
			$to = $Form->find('invite_users');
			$to['autocomplete'] = array('url'=>'/buddy/search/ajax/','multiple'=>TRUE);
			$Form->elements[$to['key']] = $to;
		}
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------