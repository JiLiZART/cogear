<?php
/**
* Points hooks
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Points
* @version		$Id$
*/

/**
* Add points counters.
*
* @param	object	Breadcrumb
* @return	void
*/
function points_breadcrumb_compile_($Breadcrumb){
	$CI =& get_instance();
	if($Breadcrumb->name == 'node_title' && $CI->gears->points->nodes->enabled){
		$Breadcrumb->add($CI->points->code('node',$Breadcrumb->data,$CI->gears->points->nodes->show_points,$CI->gears->points->nodes->show_votes),100);
	}
	// Add charge votes to user	
	else if($Breadcrumb->name == 'userinfo_panel'){
		if(acl('points add_charge') && $CI->gears->points->charge->enabled){
			$Breadcrumb->add($CI->_template('points add_charge',array('user'=>$Breadcrumb->data),TRUE),2);
		}
		if($CI->gears->points->users->enabled){
			$Breadcrumb->add($CI->points->code('user',$Breadcrumb->data,$CI->gears->points->users->show_points,$CI->gears->points->users->show_votes),100);
		}
	}
	else if($Breadcrumb->name == 'comment_header' && $CI->gears->points->comments->enabled && $CI->name != 'mail'){
		$Breadcrumb->add($CI->points->code('comment',$Breadcrumb->data,$CI->gears->points->comments->show_points,$CI->gears->points->comments->show_votes),100);
	}
	else if($Breadcrumb->name == 'community_header' && $CI->gears->points->community->enabled){
		$Breadcrumb->add($CI->points->code('community',$Breadcrumb->data,$CI->gears->points->community->show_points,$CI->gears->points->community->show_votes),7);
	}
}

/**
* Add points data into cpanel
*
* @param	object $CI
* @return	void
*/
function points_header($CI){
	if(!$CI->user->get('id')) return;
	if($CI->gears->points->users->enabled){
		$points = array(
		'data' => intval($CI->user->get('points')),
		'type'=>'text',
		'id'=>'cpanel-points',
		'link'=>l('/points/'),
		);
		if($CI->user->get('points') > 0){
		 $points['class'] = 'good';
		}
		elseif($CI->user->get('points') < 0){
		 $points['class'] = 'bad';
	      }
		else {
		 $points['class'] = ''; 
		}
		$points['text'] = '<span style="white-space: nowrap;">'.t('points rating',$points['data']).'</span>';
		$CI->cpanel->add($points,3);
	}
	if($CI->gears->points->charge->enabled){
		$charge = array(
		'data' => intval($CI->user->get('charge')),
		'type'=>'text',
		'id'=>'cpanel-charge',
		);
		$charge['text'] = '<span style="white-space: nowrap;">'.t('points charge',$charge['data']).'</span>';
		$CI->cpanel->add($charge,4);
	}
}