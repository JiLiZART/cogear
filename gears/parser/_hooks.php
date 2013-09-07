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
 * Parser hooks
 *
 * @package		CoGear
 * @subpackage	Parser
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Parse all elements after form posting
	*
	* @param	object
	* @return	voie
	*/
	function parser_form_prepare_elements_(&$Form){
		$CI =& get_instance();
		$CI->parser->name =& $Form->name;
		foreach($Form->elements as &$element){
			if(!isset($_POST[$element['name']])) continue;
			$_POST[$element['name']] = $CI->parser->prepare($_POST[$element['name']],$element);
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Parse node
	*
	* @param	object
	* @param	object
	* @param	string
	* @return	array
	*/
	function parser_node_show_($Node,$node,$type){
		$CI =& get_instance();
		if(!$CI->parser) return FALSE;
		$CI->parser->data =& $node;
		if(isset($node->no_parse) && $node->no_parse) return;
		foreach($node as $name=>$item){
			if(!is_object($item) OR !is_array($item)){
				if($name == 'body' OR strpos($name,'text')){
					$node->$name = $CI->parser->parse($item,'textarea');
				}
			}
		}
		return func_get_args();
	}
	// ------------------------------------------------------------------------

	
	/**
	* Parse personal messages
	*
	* @param	object
	* @param	object
	* @return	array
	*/
	function parser_pm_view_get($Pm,$message){
		$CI =& get_instance();
		$CI->parser->data =& $message;
		$message->body = $CI->parser->parse($message->body,'textarea');
		return func_get_args();
	}
	
	// ------------------------------------------------------------------------

	/**
	* Parse comments
	*
	* @param	object
	* @param	object
	* @return	array
	*/
	function parser_comments_process_after_($Comments,$comment){
		$CI =& get_instance();
		switch(gettype($comment)){
			case 'object':
				$comment->body = $CI->parser->parse($comment->body,'comment');
			break;
			case 'array':
				$comment['body'] = $CI->parser->parse($comment['body'],'comment');
			break;
		}
		return func_get_args();
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------