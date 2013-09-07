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

/**
 * Breadcrumb class
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Breadcrumb extends Model{
	public $name;
	public $data;
	public $elements = array();
	public $separator = ' ';
	public $class = FALSE;
	public $wrapper = FALSE;
	public $cursor = FALSE;

	/**
	* Constructor
	*
	* @return	void
	*/
	function Breadcrumb(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Initialize method
	*
	* @param	string
	* @param	string
	* @param	boolean
	* @param	string
	* @return	object
	*/
	function set($name,$separator = ' ', $class = FALSE){
		$this->name = $name;
		$this->separator = $separator;
		$this->elements = array();
		$this->class = $class;
		$this->wrapper = FALSE;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Determine to wrap output with div or no.
	*
	* @param	mixed
	* @return	object
	*/
	function wrapper($id = TRUE){
		$this->wrapper = $id;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set model data
	*
	* @param	object
	* @return	object
	*/
	function data($data){
		$this->data =& $data;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set cursor for add new elements
	*
	* @param	mixed
	* @return	void
	*/
	function cursor($position = FALSE){
		$this->cursor = $position;
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Add new element
	*
	* @param	name
	* @param	int
	* @param	boolean
	* @return	object
	*/
	function add($string,$position = FALSE,$replace = FALSE){
		$string = (string)$string;
		$str = str_split($string);
		if($this->cursor !== FALSE) {
			$position = $this->cursor++;
		}
		if($position !== FALSE) {
			 if(isset($this->elements[$position]) && !$replace){
				 array_insert($this->elements,$string,$position);
			 }
			 else $this->elements[$position] = $string;
		}
		else {
			$this->elements[] = $string;
		}
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Compile breadcrumb
	*
	* @param	mixed	$return
	* @param	mixed	$replace
	* @return	void
	*/
	function _compile($return = FALSE,$replace = FALSE){
		$CI =& get_instance();
		ksort($this->elements);
		$output = implode($this->separator,$this->elements);
		if($this->wrapper) $output = $CI->builder->div(is_bool($this->wrapper) ? $output : $this->wrapper,$this->class,$this->name);
		$output = str_replace(array('<p>','</p>'),'',$output);
		return $return === FALSE ? $output : $CI->_template(array($output),FALSE,is_bool($return) ? FALSE : $return,$replace);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------