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
 * Builder class
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Builder extends Model{
	private $process = FALSE;
	public $content = array();

	/**
	* Constructor
	*
	* @return	void
	*/
	function Builder(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Start accumulating output
	*
	* @return	object
	*/
	function start(){
		$this->process = TRUE;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Stop accumulating output
	*
	* @return	object
	*/
	function stop(){
		$this->process = FALSE;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Dynamic function to build html elements
	*
	* If last arg is boolean - output will be send to template method
	*
	* @param	string
	* @param	array
	* @return	object
	*/
	function __call($tag,$args){
		array_unshift($args,$tag);
		if(!$this->process){
			if(count($args) > 2 && (is_bool(end($args)) OR is_numeric(end($args)))){
				$return = array_pop($args);
				$result = call_user_func_array(array($this,'build'),$args);
				return $return ? $this->_template(array($result),is_numeric($return) ? $return : FALSE) : $result;
			}
			else {
				return call_user_func_array(array($this,'build'),$args);
			}
		}
		else {
			$result = call_user_func_array(array($this,'build'),$args);
			if(is_bool(end($args)) && $args){
				return $result;
			} else {
				$this->content[] = $result;
				return  $this;
			}
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Build element
	*
	* @param	string
	* @param	string
	* @param	array
	* @return	object
	*/
	function build(){
		$args = func_get_args();
		$args = array_pad($args,2,FALSE);
		$tag = $args[0];
		$value = $args[1];

		if(isset($args[2])){
				$attributes = array();
				if(is_array($args[2])){
					$attributes = $args[2];
				}
				else {
					$tmp = array_slice($args,2);
					switch($tag){
						case 'a':
						$fields = array('href','class','id','title','target');
						break;
						case 'img':
						$fields = array('class','id','alt','width','height','border');
						break;
						case 'label':
						$fields = array('for');
						break;
						default:
						$fields = array('class','id');
					}
					foreach($tmp as $key=>$elem){
						if(strpos($elem,'=')){
						 $elem = explode('=',$elem);
						 $attributes[$elem[0]] = $elem[1];
						}
						elseif($elem && trim($elem) != '' && isset($fields[$key]) && !is_bool($elem)){
						 $attributes[$fields[$key]] = $elem;
						}
					}
				}
		}
		else {
			$attributes = FALSE;
		}
		if(is_array($value)){
			$result = array();
			foreach($value as $val){
				$result[] = $this->build($tag,$val,$attributes);
			}
			return $result;
		}
		else {
			$output = '<'.$tag.' ';
			if($tag == 'img' && !isset($attributes['src'])){
				 $attributes['src'] = strpos($value,$this->site->url) ? $value : 'http://'.$this->site->url.$value;
			}
			if($tag == 'input'){
				 switch($attributes['type']){
				  case 'checkbox':
				  if($value) $attributes['checked'] = 'checked';
				  break;
				  default:
				  $attributes['value'] = $value;
				 }
			}
			if($attributes){
				foreach($attributes as $attr=>$attr_value){
					if($attr_value) $output.= is_string($attr) ? $attr.'="'.$attr_value.'" ' : $attr_value;
				}
				$output = rtrim($output);
			}
			if(in_array($tag,array('br','hr','img','input','meta','link'))){
				/*$output .= '/>';*/$output .= ' />';
			}
			else if(is_bool($value)){
				if($value) $output .= '>';
				else $output = '</'.$tag.'>';
			}
			else {
				$output .=  '>'.$value.'</'.$tag.'>';
			}
			return $output;
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Compile output
	*
	* @param	boolean
	* @return	mixed
	*/
	function compile($return = FALSE){
		if($this->process) $this->process = FALSE;
		$content = implode('',$this->content);
		return is_bool($return) ? $content : $this->_template(array($content),$position);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------