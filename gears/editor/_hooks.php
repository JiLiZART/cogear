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
 * Editor hooks
 *
 * @package		CoGear
 * @subpackage	Editor
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Parser enhance
	*
	* @param	object
	* @return void
	*/
	function editor_parser_construct_(&$Parser){
		$CI =& get_instance();
		$Parser->process['textarea'][10] = 'empty_tags|p';
		$Parser->process['textarea'][] = 'parse_block';
		$Parser->process['textarea'][] = 'parse_hidden';
		$Parser->process['textarea'][] = 'linkbreaks';
		$Parser->process['comment'][] = 'linkbreaks';
	}
	// ------------------------------------------------------------------------
	
	function parse_block($value){
		return preg_replace_callback('#\[block=(.*)\]\s*(.*)\s*\[/block\]#ismU','preg_replace_block',$value);
	}
	function preg_replace_block($matches){
		return  "<div class='block'><div class='header' >{$matches[1]}</div><div class='body'>{$matches[2]}</div></div>";
	}
	function parse_hidden($value){
		return preg_replace_callback('#\[hidden=(.*)\]\s*(.*)\s*\[/hidden]#ismU','preg_replace_hidden',$value);
	}
	function preg_replace_hidden($matches){
		return  "<div class='mini spoiler'><div class='header' >{$matches[1]}</div><div class='body'>{$matches[2]}</div></div>";
	}
	
	function linkbreaks($value){
		$value = preg_replace('#<br/?>#','',$value);
		$value = trim($value);

		$value = nl2br($value);
		$value = preg_replace(array(
		'#<br\s?/>#',
		'#>\n*<br>#imsU',
		'#(<br>\n*){2,}#imsU'
		),array(
		'<br>',
		'>',
		''
		),$value);
		$value = preg_replace_callback('#<code(.*)>(.*)</code>#ismU','clear_code_tag',$value);
		return $value;
	}
	
	function clear_code_tag($matches){
		return '<code'.$matches[1].'>'.str_replace(array('<br>','<br/>','&lt;br&gt;','&lt;br/&gt;'),'',$matches[2]).'</code>';
	}

	/**
	* Remove tag with no content inside
	*
	* @param	string
	* @param	string
	* @return string
	*/
	function empty_tags($value,$tag){
		$value = preg_replace('#\s*<'.$tag.'(.[^>]*)?>\s+</'.$tag.'>\s*#imsU','',$value);
		return $value;
	}
	// ------------------------------------------------------------------------

	/**
	* Add editor js for needed forms
	*
	* @param	object
	* @return void
	*/
	function editor_form_compile_after_($Form){
		$CI =& get_instance();
		$code = '';
		// Some strange notice
		// $CI->gears->editor->forms is a valid string, but using it in strpos here cause notice @Array to string conversion@
		// Mystery
		$forms = $CI->gears->editor->forms;
		if(!is_array($forms)) $forms = _explode(',',$forms);
		if(in_array($Form->name,$forms)){
			$code .= "\n if(\$defined(\$('{$Form->name}'))) editor.add('{$Form->name}');";
		}

		$editors = isset($Form->editors) ? $Form->editors : FALSE;
		if(is_array($editors)){
			foreach($editors as $editor){
				$code .= "\n if(\$defined(\$('{$editor}'))) editor.add('{$editor}');";
			}
		}
		if($code != ''){
			$CI->editor->init($code)->compile();
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Editor element for form
	*
	* @param	object	$Form
	* @param	string	$name
	* @param	array		$config
	* @return	object	$Form
	*/
	function form_editor_($Form,$name,$config){
		$Form->textarea($name,$config);
		if(!isset($Form->editors)) $Form->editors = array();
		if(in_array($name,$Form->editors)) return $Form;
		$Form->editors[] = $name;
		return $Form;
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------