<?php
/**
* Tags cloud widget
*
* 
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Tags
* @version		$Id$
*/
function tags_widget($CI,$config){
	$output = retrieve('sidebar/widgets/tags');
	if($output === FALSE){
		$tags = $CI->db->select('COUNT(nodes_tags.nid) as count, tags.name')->join('tags','tags.id = nodes_tags.tid','inner')->group_by('tid')->order_by('count','desc')->limit(empty($config->max) ? 50 : $config->max)->get('nodes_tags')->result();
		$output = array();
		$max = 0;
		foreach($tags as $tag){
			if($tag->count > $max) $max = $tag->count;
		}
		usort($tags,'alphabetic_tags_sort');
		foreach($tags as $tag){
			$rating = floor($tag->count/$max*100);
			$font_size = str_replace(',','.',(($rating < 100 ? '1.'.$rating : '2')/2).'em');
			$level = round($rating/10);
			$output[] = '<a href="'.l('/tags/'.$tag->name).'" class="l'.$level.'" style="font-size: '.$font_size.'">'.$tag->name.'</a>';
		}
		$output = implode(' ',$output);
		$output .=$CI->builder->div($CI->builder->a(t('tags all_tags'),l('/tags/')),'tright');
		store('sidebar/widgets/tags',$output,FALSE,'tags');
	}
	return $output ? $output : t('tags empty');
}
/**
* Sort tags alphabetically
*
* @param	object	Tag one
* @param	object	Tag two
* @return
*/
function alphabetic_tags_sort($a,$b){
	return strnatcasecmp($a->name,$b->name);
}