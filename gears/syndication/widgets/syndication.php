<?php
/**
* Syndication widget
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Syndication
* @subpackage	Widgets
* @version		$Id$
*/

function syndication_widget($CI,$config = null){
	if(!$content = retrieve('sidebar/widgets/syndication') && FALSE){
		$items = $CI->db
		->select('syndication_sources.name as sname, syndication_sources.favicon, syndication_sources.link as slink, 
		syndication_items.name,syndication_items.link,syndication_items.created_date
		',FALSE)
		->join('syndication_sources','syndication_items.sid = syndication_sources.id','inner')
		->order_by('created_date','desc')
		->limit($config->items_num)
		->get('syndication_items')
		 ->result();
		 $content = '<dl>';
		 foreach($items as $item){
			 $content .= '<dt><a href="'.$item->slink.'"><img src="'.$item->favicon.'" width="16" height="16" border="0" alt="'.$item->sname.'"/></a></dt><dd><a href="'.$item->link.'">'.$item->name.'</a> <small>'.df($item->created_date).'</small></dd>';
		 }
		 $content .= '</dl>';
		 store('sidebar/widgets/syndication',$content,$config->cache_lifetime);		
	}
	return $content.' <p align="right"><a href="/syndication/">'.t('syndication more').'</a></p>';
}