<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Autoload classes and models
*
* @return	
*/
function __autoload($name){
	$CI =& get_instance();
	$name = strtolower($name);
	if(isset($CI->load->autoload['models'][$name])){
		$CI->load->_ci_loaded_files[] = $CI->load->autoload['models'][$name];
		require_once($CI->load->autoload['models'][$name]);
	}
	elseif(isset($CI->load->autoload['classes'][$name])){
		$CI->load->_ci_loaded_files[] = $CI->load->autoload['classes'][$name];
		require_once($CI->load->autoload['classes'][$name]);
	}
	else return FALSE;
}