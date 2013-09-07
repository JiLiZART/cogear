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
 * Cut hooks
 *
 * @package		CoGear
 * @subpackage	Cut
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Extends editor for cut button and js
	*
	* @param	object
	* @return	void
	*/
	function cut_editor_compile_after_($Editor){
			js('/gears/cut/js/inline/cut.button',FALSE,TRUE);
	}	
	// ------------------------------------------------------------------------

	/**
	* Set tizer param for node
	*
	* @param	object
	* @param	object
	* @param	string
	* @return	array
	*/
	function cut_node_show_($Node,$node,$type){
		if($type == 'short') $node->tizer = TRUE;
		else $node->tizer = FALSE;
		return func_get_args();
	}
	// ------------------------------------------------------------------------

	/**
	* Extends parser processor to parse cut
	*
	* @param	object
	* @return	void
	*/
	function cut_parser_construct_($Parser){
		array_insert($Parser->process['textarea'],'parse_cut',0);
	}
	// ------------------------------------------------------------------------

	/**
	* Parse cut function
	*
	* @param	string
	* @param	object
	* @return	string
	*/
	function parse_cut($value){
		$CI =& get_instance();
		$value = preg_replace("#<div>\s+?([\[|\<]cut)#imsU",'\\1',$value);
		if(preg_match("#[\[|\<]cut=?([^=].*)?/?[\]|\>]#imU",$value,$matches)){
			if(isset($matches[1])){
				$cut_text = $matches[1];
			}
			else $cut_text = t('!cut more');
			$data =& $CI->parser->data;
			if(isset($data->tizer) && $data->tizer){
				$value = substr($value,0,strpos($value,$matches[0])).$CI->builder->div($CI->builder->a($cut_text,$CI->node->create_link($CI->parser->data,'#cut')),'cut clear');
			}
			else {
				$part_one = substr($value,0,strpos($value,$matches[0]));
				$part_two = substr($value,strpos($value,$matches[0])+strlen($matches[0]));
				$value = $part_one.$CI->builder->div(' ','cut clear','cut').$part_two;
			}
		}
		return $value;
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------