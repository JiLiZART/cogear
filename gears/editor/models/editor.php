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
* @link			http://cogear.ru
* @since			Version 1.0
*/

// ------------------------------------------------------------------------

/**
* Editor model
*
* @package		CoGear
* @subpackage	Editor		
* @category		Gears models
* @author			CodeMotion, Dmitriy Belyaev
* @link				http://cogear.ru/user_guide/
*/
class Editor extends Model{
	private $code = "";
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		return parent::Model();
	}
	
	/**
	* Initializing editor
	*
	* @param	string	$code		Set code
	* @return	object	$this
	*/
	function init($code){
		$this->code = $code;
		return $this;
	}
	
	
	/**
	* Compile data to javascript code
	*
	* @param	boolean	$wysiwyg	
	* @return	void
	*/
	function _compile($wysiwyg = FALSE){
			//js('/gears/editor/js/inline/editor.html',FALSE,TRUE);
			$code = 'window.addEvent("domready",function(){';
			$code .= "\n editor = new gEditor();\n";
			$code .= $this->code;
			$code .= "\n});";
			js($code,TRUE,TRUE);
	}
} 