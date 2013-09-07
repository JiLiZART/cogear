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
 * Panel model
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Panel extends Model{
	public $elements = array();
	public $links_base = FALSE;
	public $template = FALSE;
	public $set_title = TRUE;
	public $name = '';
	public $data = FALSE;
	public $active = FALSE;
	public $id;
	
	/**
	* Constructor
	*
	* @param	string
	* @param	mixed
	* @param	boolean
	* @param	stirng
	* @return	void
	*/
	function Panel($name = 'panel',$data = FALSE,$set_title = TRUE,$template = '!global panel'){
		parent::Model();
		$this->set($name,$data,$set_title,$template);
	}
	// ------------------------------------------------------------------------

	/**
	* Initialize
	*
	* @param	string
	* @param	mixed
	* @param	boolean
	* @param	stirng
	* @return	void
	*/
	function set($name = 'panel',$data = FALSE,$set_title = TRUE,$template = '!global panel'){
		$this->name = $name;
		if($template) $this->template = $template;
		$this->set_title = $set_title;
		$this->data = $data;
		$this->links_base = FALSE;
		$this->elements = array();
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set links base
	*
	* @param	string
	* @return	object
	*/
	function base($base){
		$this->links_base = $base;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Add new element
	*
	* @param	array
	* @param	int
	* @param	boolean
	* @return	object
	*/
	function _add($data, $position = FALSE, $replace = FALSE){
		 if(!isset($data['type'])) $data['type'] = 'icon';
		 if($position !== FALSE) {
			 if(isset($this->elements[$position]) && !$replace){
				array_insert($this->elements,$data,$position);
			 }
			 else {
				 $this->elements[$position] = $data;
			 }
		 }
		 else $this->elements[] = $data;
		 return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set data for model
	*
	* @param	mixed
	* @return	object
	*/
	function data($data){
		$this->data =& $data;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set class='active' to element
	*
	* @param	string
	* @param	boolean
	* @return	object
	*/
	function _set_active($name,$add = FALSE){
		$CI =& get_instance();
		foreach($this->elements as &$element){
			if($element['name'] == $name){
				if($this->set_title){
					 if(!isset($element['title'])) title($element['text'],FALSE,TRUE);
					 elseif($element['title']) title($element['title'],FALSE,TRUE);
					 $this->set_title = FALSE;
				 }
				$element['class'] = isset($element['class']) ? $element['class'].' active' : 'active';
			}
			else if(isset($element['class']) && !$add){
				if($element['class'] == 'active'){
					unset($element['class']);
				}
				else $element['class'] = preg_replace('/\s?active\s?/i','',$element['class']);
			}
		}
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set links for elements
	*
	* @return	object
	*/
	function _set_links(){
		if($this->links_base) foreach($this->elements as &$element){
			if(!isset($element['link'])){
				$element['link'] = $this->links_base;
				if(empty($element['index'])) $element['link'] .= $element['name'];
				$element['link'] = l($element['link']);
			}
		}	
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Compile panel
	*
	* @param	boolean
	* @return	mixed
	*/
	function _compile($return = FALSE){
		$this->set_links();
		ksort($this->elements);
		if(!strpos($this->template,' ')) $this->template = 'global '.$this->template;
		return $this->_template($this->template,get_object_vars($this),$return);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------