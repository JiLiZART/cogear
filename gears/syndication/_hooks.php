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
 * Syndication hooks
 *
 * @package		CoGear
 * @subpackage	Syndication
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add RSS meta-link and change output for rss
	*
	* @param	object	$Nodes
	* @param	object	$nodes
	* @param	int		$page
	* @param	int		$position
	* @return	void
	*/
	function syndication_nodes_show_($Nodes,$nodes,$page,$position){
		$CI =& get_instance();
		$CI->content['meta']['info'] .= $CI->builder->link(FALSE,array(
		'type'=>'application/rss+xml',
		'rel'=>'alternate',
		'href'=>l('rss/'.implode('/',$CI->uri->segments))
		));
		if(in_array('rss',$CI->uri->segments) && $CI->uri->segments[1] == 'rss'){
			setlocale(LC_TIME,'en_US.UTF-8');
			array_shift($CI->uri->segments);
			$data = array(
			'title' => implode(' / ',array_reverse($CI->content['meta']['title'])),
			'link' => l('/'.implode('/',$CI->uri->segments)),
			'lang' => $CI->site->lang,
			'creator' => l('/'),
			'items'=>&$nodes
			);
			foreach($nodes as $node){
				$CI->node->show($node,'short',TRUE);
				$node->body = str_replace('/uploads','http://'.$CI->site->url.'/uploads',$node->body);
				$node->body .= $node->extra;
				$node->title = str_replace($CI->gears->nodes->node->title_separator,'/',strip_tags($node->title));
			}
			$CI->output->_display('<?xml version="1.0" encoding="utf-8"?>
'."\n".$CI->_template('syndication rss2',$data,TRUE));
			exit();
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Comments rss
	*
	* @param	object	$Comments
	* @param	boolean	$result
	* @param	array		$comments
	* @param	mixed	$return
	* @return	void
	*/
	function syndication_comments_show_after_($Comments,$result,$comments,$return){
		if($return){
				$CI =& get_instance();
				$CI->content['meta']['info'] .= $CI->builder->link(FALSE,array(
				'type'=>'application/rss+xml',
				'rel'=>'alternate',
				'href'=>l('/rss/'.rtrim(implode('/',$CI->uri->segments)),FALSE)
				));
				if(in_array('rss',$CI->uri->segments) && $CI->uri->segments[1] == 'rss'){
					setlocale(LC_TIME,'en_US.UTF-8');
					array_shift($CI->uri->segments);
					usort($result,'syndication_date_sort');
					$result = array2object($result);
					foreach($result as $comment){
						$comment->body = str_replace('/uploads','http://'.$CI->site->url.'/uploads',$comment->body);
						$comment->title = $comment->cauthor;
						$comment->link = l('/'.implode('/',$CI->uri->segments),'#comment-'.$comment->id);
					}
					$data = array(
						'title' => implode(' / ',array_reverse($CI->content['meta']['title'])),
						'link' => l('/'.implode('/',$CI->uri->segments)),
						'lang' => $CI->site->lang,
						'creator' => l('/'),
						'items'=>&$result
					);
					$CI->output->_display('<?xml version="1.0" encoding="utf-8"?>
		'."\n".$CI->_template('syndication rss2',$data,TRUE));
					exit();
				}
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Sorting comments by date desc
	*
	* @param	array
	* @param	array
	* @return	void
	*/
	function syndication_date_sort($a,$b){
		return strtotime($a['created_date']) > strtotime($b['created_date']) ? -1 : 1;
	}
	// ------------------------------------------------------------------------


	/**
	* RSS icon to nodes info to subscribe comments
	*
	* @param	object	$Breadcrumb
	* @return	void
	*/
	function syndication_breadcrumb_compile_($Breadcrumb){
		$CI =& get_instance();
		if(!$CI->gears->comments) return;
		if($Breadcrumb->name == 'node_info'){
			$node =& $Breadcrumb->data;
			//$Panel->data->comments
			$tmp = $CI->site->subdomains;
			$CI->site->subdomains = FALSE;
			$link = $CI->node->create_link($node,'#comments');
			$link = str_replace($CI->site->url.'/',$CI->site->url.'/rss/',$link);
			$CI->site->subdomains = $tmp;			
			$Breadcrumb->add(' &nbsp;<a href="'.$link.'"><img src="/gears/syndication/img/icon/rss.png" alt="RSS"></a>',50);
		}
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------