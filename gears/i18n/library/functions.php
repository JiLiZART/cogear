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
 * Common functions for i18n
 *
 * @package		CoGear
 * @subpackage	i18n
 * @category		Gears libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/*
	* Translate with escape for Quicky template class
	*
	* @param	string
	* @return	string
	*/
	function etranslate($m){
	 return translate($m,TRUE);
	} 
	// ------------------------------------------------------------------------

	/*
	* Translate with for Quicky template class
	* @param	string
	* @param	boolean
	* @return	string
	*/
	function translate($name, $escape = FALSE) {
	  if(is_array($name)) $name = end($name);
	  $CI =& get_instance();
	  if($escape) $name = addslashes($name);
	  return $CI->i18n->translate($name,FALSE,TRUE);
	}
	// ------------------------------------------------------------------------

	
	/*
	* Translate text
	*
	* All additional args can be used as sprintf args to parse values
	*
	* @param	string
	* @param	mixed
	.....................
	* @return	string
	*/
	function t($name,$args = FALSE){
        if(!preg_match('#^[\\!%0-9a-zA-Z\s\._-]+$#isu',$name)) return $name;
	  $CI =& get_instance();
	  if(count(func_get_args()) > 1){
	   $args = array_slice(func_get_args(),1);
	  }
	  return $CI->i18n->translate($name,$args,FALSE);
	}
	// ------------------------------------------------------------------------
	
	/**
	* Alternative translation
	*
	* @param	string
	* @param	string	Defalt value
	* @param	string	Alt section
	* @return	string
	*/
	function at($name,$default = '',$section = 'edit'){
		$translate = t($name);
		if(strstr($name,$translate) && $alt =  t($section.' '.$name)){
			if($alt != $section.' '.$name){
				return strstr($name,$alt) ? $default : $alt;
			}
			elseif(strpos('[',$name) !== -1) {
				$name = preg_replace(array('#\w+\[#','#\]#'),array('',''),$name);
				return t($name) == $name ? '' : t($name);
			}
		}  
		return strstr($name,$translate) ? '' : $translate;
	}

	/*
	* Capitalize first chat in unicode string and translate
	*
	* @param	string
	* @param	array
	* @return	string
	*/
	function fc_t($name, $args = FALSE){
		return mb_ucfirst(t($name, $args));
	}
	// ------------------------------------------------------------------------

	/*
	* Check string for translation existance
	*
	* @param	string
	* @return	mixed
	*/
	function has_t($name){
		$CI =& get_instance();
		$result = t($name);
		if($result != $name){
			return $result;
		}
		else {
			return FALSE;
		}	
	}
	// ------------------------------------------------------------------------

	/*
	* Set section (department) for translation
	*
	* @param	string
	* @return	void
	*/
	function d($name = FALSE){
	 $CI =& get_instance();
	 if(gettype($name) == 'string'){
	   if($CI->i18n->section == 'global') $CI->i18n->default_section = $name;	 
	   else $CI->i18n->last_section = $CI->i18n->section;	 
	   $CI->i18n->section($name);
	 }
	 elseif(gettype($name) == 'boolean') {
	   if($name && isset($CI->i18n->default_section)) $CI->i18n->section($CI->i18n->default_section);
	   elseif(isset($CI->i18n->last_section)) $CI->i18n->section($CI->i18n->last_section);
	 }
	}
	// ------------------------------------------------------------------------

	
	/**
	 * Plural forms for words
	 *
	 * @param	int $number number
	 * @param	string $titles Array of words to make plural forms joined with |
	 * @return	string
	 **/
	function declOfNum($number, $titles = FALSE)
	{
		if($number < 0) $number = -$number;

		$cases = array (2, 0, 1, 1, 1, 2);
		
		if(!$titles){
			$titles = array(
				t($text),
				t($text.'_couple'),
				t($text.'_many')
			);
		}
		else {
			if(is_string($titles)) $titles = explode('|',$titles);
			if(count($titles) < 3){
				$titles = array_pad($titles,3,end($titles));
			}
		}
		$offset = ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)];
		return isset($titles[$offset]) ? $titles[$offset] : array_shift($offset);
	}
// ------------------------------------------------------------------------