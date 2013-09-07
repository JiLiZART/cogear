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
 * Parser model
 *
 * @package		CoGear
 * @subpackage	Parser
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Parser extends Model{
	public $name = FALSE;
	public $data = FALSE;
	public $enabled = TRUE;
	public $prepare = array(
		'input'=> array('strip_tags'),
		'textarea' => array(),
		'comment' => array()
	);
	public $process = array(
	'input' => array('strip_tags'),
	'textarea' => array(),
	'comment' => array()
	);
	
	/**
	* Constructor
	*
	* @return	void
	*/
	function Parser(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Initialize
	*
	* @param	string
	* @param	mixed
	* @return	object
	*/
	function set($name,$data = FALSE){
		$this->name = $name;
		return $this->data($data);
	}
	// ------------------------------------------------------------------------

	/**
	* Set model data
	*
	* @param	mixed
	* @return	object
	*/
	function data($data = FALSE){
		if($data) $this->data =& $data;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Prepare content for database
	*
	* @param	string
	* @param	mixed
	* @return	string
	*/
	function _prepare($value,$element){
		return $this->parse($value,$element,'prepare');
	}
	// ------------------------------------------------------------------------

	/**
	* Parse content
	*
	* @param	string
	* @param	mixed
	* @param	string
	* @return	string
	*/
	function _parse($value,$element,$action = 'process'){
		if(!$this->enabled) return $value;
		$type = $this->prepare_type(is_array($element) ? $element['type'] : $element);
		$config = $this->$action;
		if(!isset($config[$type])) return $value;
		foreach($config[$type] as $function){
			$CI =& get_instance();
			if(is_array($function) && method_exists($CI->$function[0],(strpos($function[1],'|') !== FALSE ? substr($function[1],0,strpos($function[1],'|')) : $function[1]))){
				if(strpos($function[1],'|')){
					$args = explode('|',$function[1]);
					$function[1] = array_shift($args);
					array_unshift($args,$value);
				}
				else {
					$args = array($value);
				}
				$value = call_user_func_array(array($CI->$function[0],$function[1]),$args);
			}
			else{
				if(strpos($function,'|')){
					$args = explode('|',$function);
					$function = array_shift($args);
					array_unshift($args,$value);				
				}
				else {
					$args = array($value);
				}
				//array_push($args,$this->data);
				if(function_exists($function)){
					$value = call_user_func_array($function,$args);
				}
				elseif(method_exists($this,$function)){
					$value = call_user_func_array(array($this,$function),$args);
				}
			}
		}
		return $value;
	}
	// ------------------------------------------------------------------------

	/**
	* Prepare parser element type from input element
	*
	* @param	string
	* @return	string
	*/
	function _prepare_type($type){
		if($type == 'password'){
			$type = 'input';
		}
		elseif(!in_array($type,array('input','textarea','comment'))){
			$type = 'others';
		}
		return $type;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------