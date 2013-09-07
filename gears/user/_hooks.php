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
 * User hooks
 *
 * @package		CoGear
 * @subpackage	User
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add cpanel elements for user and guest
	*
	* @param	object
	* @return	void
	*/
	function user_header($CI){
		$CI->cpanel = new Panel('cpanel',FALSE,FALSE,'global cpanel');
		$user = $CI->user->get();
		if($user){
			$avatar = reset($user->avatar);
			$CI->cpanel->add(array('text'=>$user->name,'data'=>$avatar,'link'=>l('/user/'.$user->url_name.'/'),'class'=>'panel-icon','data_class'=>'avatar','width'=>'10%'),0);
			$CI->cpanel->add(array('text'=>t('user logout'),'data'=>'/gears/user/img/loginout.png','link'=>l('/user/logout/'.$user->key.'/'),'width'=>'10%'),100);
		}
		else {
			if($CI->gears->user->registration) $CI->cpanel->add(array('text'=>t('user register'),'data'=>'/gears/user/img/register.png','link'=>l('/user/register/')),99);
			$CI->cpanel->add(array('text'=>t('user login_submit'),'data'=>'/gears/user/img/loginout.png','link'=>l('/user/login/'),'onclick'=>"loader.elem('login-form',640,420);return false;"),100);
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Compile user cpanel
	*
	* @param	object
	* @return	void
	*/
	function user_footer($CI){
		if(empty($CI->content['cpanel'])) $CI->content['cpanel'] = $CI->cpanel->compile(TRUE);
	}
	// ------------------------------------------------------------------------

	/**
	* Set default avatar if user doesn't has
	*
	* @param	object
	* @return	void
	*/
	function user_user_construct_($user){
		$CI =& get_instance();
		//if($user->is_logged() && $user->get()->avatar == '') $user->update('avatar',$CI->gears->user->avatar->default);
	}
	// ------------------------------------------------------------------------

	/**
	* Show user panel into profile
	*
	* @param	object
	* @return	void
	*/
	function user_user_profile($CI,$user){
		$CI->user->head($user);
	}
	// ------------------------------------------------------------------------

	/**
	* Parser enhance
	*
	* @param	object
	* @return	void
	*/
	function user_parser_construct_(&$Parser){
		$CI =& get_instance();
		$Parser->process['textarea'][] = 'parse_user';
		$Parser->process['comment'][] = 'parse_user';

	}
	// ------------------------------------------------------------------------

	/**
	* Parse user bb-code to link
	*
	* @param	string
	* @return	string
	*/
	function parse_user($value){
		$value = preg_replace_callback('/\[user=([\w-_]+)\]/iU','preg_replace_user',$value);
		$CI =& get_instance();
		$user = $CI->user->is_logged() ? $CI->user->get('name') : t('user guest');
		$value = str_replace('%username%', $user, $value);
		return $value;
	}
	// ------------------------------------------------------------------------
	/**
	* Parse user callback
	*
	* @param	array
	* @return	string
	*/
	function preg_replace_user($matches){
	  $CI =& get_instance();
	  return $CI->builder->a($matches[1],l('/user/'.url_name($matches[1])),'user');
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------