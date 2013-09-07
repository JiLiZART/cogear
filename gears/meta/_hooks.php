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
 * Meta hooks
 *
 * @package		CoGear
 * @subpackage	Meta
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add fields to node_createdit form for meta data
	*
	* @param	object
	* @return	void
	*/
	function meta_form_result_($Form){
		if($Form->name == 'node_createdit' && acl('meta edit')){
			d('meta');
			$Form->input('keywords')->input('description');
			if(isset($Form->data->aid)) $Form->set_values($Form->data);
			d();
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Add meta data to CI content
	*
	* @param	object
	* @return	void
	*/
	function meta_header($CI){
		/**
		* Loading all meta information during controller initialize.
		*/
		$CI->content['meta'] = array(
		'title' => array(),
		'url' => trim($CI->site->url,'/'),
		'keywords' => $CI->gears->meta->info->keywords,
		'description' => $CI->gears->meta->info->description,
		'info' => ''
		);
		$CI->content['meta']['title'][] = $CI->site->name;
		if($CI->gear->title && !isset($CI->notitle) && !isset($CI->no_title)){
		 $CI->content['meta']['title'][] = has_t('!gears '.$CI->name) ? t('!gears '.$CI->name) : $CI->gear->title;
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Compile meta info to template variable 
	*
	* @param	object
	* @return	void
	*/
	function meta_footer($CI){
		$CI->builder->stop();
		$CI->content['meta']['info'] .= $CI->builder->meta('',array('name'=>'keywords','content'=>$CI->content['meta']['keywords']));
		$CI->content['meta']['info'] .= $CI->builder->meta('',array('name'=>'description','content'=>strip_tags(str_replace('"',"'",$CI->content['meta']['description']))));
		 if(isset($CI->title_delayed) && count($CI->title_delayed) > 0){
			 if($CI->title_delayed_remove_last){
				array_pop($CI->content['meta']['title']);	 
			 }
			 $CI->content['meta']['title'] = array_merge($CI->content['meta']['title'],$CI->title_delayed);
		 }
	}
	// ------------------------------------------------------------------------
	
	function meta_footer_after($CI){
			$CI->content['meta']['title'] = implode(' &laquo; ',array_reverse($CI->content['meta']['title']));
	}
	/**
	*  Show meta on node page
	*
	* @param	object
	* @param	object
	* @param	string
	* @return	void
	*/
	function meta_node_show_($Node,$node, $type){
		meta($node->name,'description');
		if(isset($node->tags)) meta(strip_tags($node->tags));
		if($type == 'short') return;
		if($node->keywords) meta($node->keywords);
		if($node->description) meta($node->description,'description');
	}
	// ------------------------------------------------------------------------

	/**
	* Add community data to meta
	*
	* @param	object
	* @return	void
	*/
	function meta_breadcrumb_compile_($Breadcrumb){
		if($Breadcrumb->name == 'community_header'){
			$community =& $Breadcrumb->data;
			if($community->description) meta($community->description,'description');
			meta($community->name);
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	*  Add rule to parser
	*
	* @param	object
	* @return	void
	*/
	function meta_parser_construct_($Parser){
		$CI =& get_instance();
		if(in_array('rss',$CI->uri->segments)){
			$Parser->process['textarea'][] = 'meta_add_images_alt';
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Parse data with preg_replace_callback
	*
	* @param	string
	* @return	string
	*/
	function meta_add_images_alt($value){
		return preg_replace_callback('#(<img)(.*)(/?>)#imsU','meta_add_images_alt_callback',$value);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Callback function for parse
	*
	* @param	array
	* @return	string
	*/
	function meta_add_images_alt_callback($matches){
		if(!strpos($matches[2],'alt')){
			$CI =& get_instance();
			$keywords = _explode(',',$CI->content['meta']['keywords']);
			shuffle($keywords);
			array_slice($keywords,0,count($keywords)/2);
			$alt = implode(', ',$keywords);
			return $matches[1].' alt="'.$alt.'" '.$matches[2].$matches[3];
		}
		else return $matches[1].$matches[2].$matches[3];
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------