<?php
/**
* Users rating widget
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Points
* @version		$Id$
*/
/**
* Top users widget
*
* @param	object	$CI
* @param	object	$config
* @return	string
*/
function top_users_widget($CI,$config){
	$top_users = retrieve('sidebar/widgets/top_users');
	if($top_users === FALSE){
		$users = $CI->db->select('users.name,users.url_name,users.points,users.avatar')->limit($config->limit)->order_by('points','desc')->get('users')->result();
		$output = array();
		foreach($users as $user){
			$CI->breadcrumb->set('sidebar/widgets/top_users')->data($user);
			$avatar = make_icons($user->avatar);
			$CI->breadcrumb->add('<td style="text-align: center;"><a href="'.l('/user/'.$user->url_name).'"><img src="'.$avatar['24x24'].'" width="24" alt="'.$user->name.'" class="avatar"></a></td>');
			$CI->breadcrumb->add('<td align="center"><a href="'.l('/user/'.$user->url_name).'">'.$user->name.'</a></td>');
			$class = $user->points >= 0 ? ($user->points > 0 ? 'good' : 'zero') : 'bad';
			$CI->breadcrumb->add('<td align="center"><span class="'.$class.'">'.$user->points.'</span></td>');
			$output[] = '<tr>'.$CI->breadcrumb->compile().'</tr>';
		}
		$top_users = '<table cellpadding="0" cellspacing="5" width="100%" border="0">
		<thead><tr><td></td><td style="text-align: center;">'.t('gears user').'</td><td align="center">'.t('points top_rating').'</td></tr></thead>
		'.implode('',$output).'
		</table>';
		$top_users .=$CI->builder->div($CI->builder->a(t('points show_all'),l('/points/')),'tright');
		store('sidebar/widgets/top_users',$top_users,3600);
	}
	return $top_users;
}