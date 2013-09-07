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
 * Users online widget
 *
 * @package		CoGear
 * @subpackage	Users
 * @category		Gears widgets
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Shows users online
	*
	* @param	object
	* @return	string
	*/
	function online_widget($CI,$config){
		$online = retrieve('sidebar/widgets/online');
		if(!$online){
			$CI->db->join('users','online.uid = users.id','left');
			$time = '\''.date('Y-m-d H:i:s',(time()-$config->interval)).'\'';
			$CI->db->group_by('session_id');
			$data = $CI->db->where('time > '.$time)->get('online')->result();
			$CI->db->delete('online','time <= '.$time);
			$output = '';
			$guests = 0;
			$total = 0;
			$users = array();
			foreach($data as $user){
				if(preg_match('#(yandex|google|rss|bot|rambler|pubsub|parser|spider|feed)#ism',$user->user_agent)) continue;
				if($user->uid && $user->uid != 0) {
					if(isset($users[$user->uid]) OR empty($user->name)) continue;
					$output .= $CI->builder->a($user->name,l('/user/'.$user->url_name),'user').' ';
					$users[$user->uid] = TRUE;
				}
				else {
					$guests++;
				}
				$total++;
			}
			$online = str_replace('<p></p>', '',t('!widgets online_data',$output,$guests, $total));
			store('sidebar/widgets/online',$online,$config->refresh,'widgets');
		}
		$flag = time() - $CI->session->get('online_refresh') > $config->refresh ? TRUE : FALSE;
		if(!preg_match('#(yandex|google|rss|bot|rambler|pubsub|parser|spider|feed)#ism',$CI->session->get('user_agent')) && $flag){
			$CI->db->insert('online',array('uid'=>$CI->user->get('id') ? $CI->user->get('id') : 0,'session_id'=>$CI->session->get('session_id'),'user_agent'=>$CI->session->get('user_agent')));			
			$CI->session->set('online_refresh',time());
		}
		return $online;
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------