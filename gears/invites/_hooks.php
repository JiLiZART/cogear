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
 * Invites hooks
 *
 * @package		CoGear
 * @subpackage	Invites
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Redirect to login form if 'access_mode' param is enabled and user is not registered
	*
	* @param	object
	* @return	void
	*/
	function invites_header($CI){
		if($CI->gears->invites->access_mode && !$CI->user->get('id')){
			if(!in_array($CI->name,array('user')) && strpos($CI->name,'captcha') === FALSE && !in_array('ajax',$CI->uri->segments)){
				redirect('/user/login');
			}
			else {
				title(t('invites title'),TRUE,TRUE);
			}
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Add invite field into register form
	*
	* @param	object
	* @return	void
	*/
	function invites_form_result_($Form){
		$CI =& get_instance();
		if($Form->name == 'register' && !$CI->user->get('id')){
			d('invites');
			$Form->input('invite',array('validation'=>'required|check_invite','js_validation'=>'required|length[5,-1]'),6);
			d();
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Add user id to invites table after registration is complete
	*
	* @param	object
	* @param	array
	* @return	void
	*/
	function invites_form_save_after_($Form,$return,$table,$result){
		$CI =& get_instance();
		if($Form->name == 'register'){
			$CI->db->update('invites',array('to'=>$Form->insert_id),array('invite'=>$result['invite']));
		}
	}
	
	/**
	* Check for invite
	*
	* @param	string
	* @return	boolean
	*/
	function check_invite($invite){
		$CI =& get_instance();
		$CI->form_validation->set_message('check_invite', t('!invites no_such_invite'));
		return $CI->db->get_where('invites',array('invite'=>$invite,'to'=>NULL))->row() ? TRUE : FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	* Show user invites in profile
	*
	* @param	object
	* @return	void
	*/
	function invites_panel_compile_($Panel){
			if($Panel->name == 'userinfo_profile'){
				$CI =& get_instance();
				$item['type'] = 'line';
				if($CI->user->get('id') == $Panel->data->id){				
					$item['left'] = fc_t('!invites invites');
					$query = $CI->db->select('invite')->get_where('invites',array('from'=>$CI->user->get('id'),'to'=>NULL))->result_array();
					if($query){
						foreach($query as $invite){
							$invites[] = $invite['invite'];
						}
						$item['right'] = isset($invites) ? $CI->builder->div(implode('<br>',$invites),'hide-show') : t('!invites no_invites');
						$Panel->add($item,6);			
						$item['left'] = fc_t('!invites invited');
						$query = $CI->db->select('users.name,users.url_name')->join('users','users.id = invites.to','inner')->where('to IS NOT NULL')->get_where('invites',array('from'=>$CI->user->get('id')))->result_array();
					}
					if($query){
						foreach($query as $invite){
							$invited[] = $CI->builder->a($invite['name'],l('/user/'.$invite['url_name']),'user '.($CI->buddy->check($invite['name']) !== FALSE ? 'buddy' : FALSE));
						}
						$item['right'] = isset($invited) ? implode(', ',$invited) : t('!invites no_invited');
						$Panel->add($item,7);			
					}
				}
				
			}
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------