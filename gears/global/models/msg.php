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
 *  Messages model
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Msg extends Model {
	private $messages = array();
	/**
	* Constructor
	*
	* @return	void
	*/
	function Msg(){
	 parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	*  Add new message 
	*
	* @param	string
	* @param	boolean
	* @return	object
	*/
	function set($msg = FALSE,$success = TRUE){
	 $this->messages = $this->session->get("messages",TRUE);
	 switch(gettype($msg)){
	  case 'array':
	   foreach($msg as $m){
		   $this->messages[] = array("text"=>addslashes(nl2br($m[0])),"success" => issset($m[1]) ? $m[1] : TRUE);
	   }
	  break;
	  case 'string':
	  default:
		  $this->messages[] = array("text"=>addslashes(nl2br($msg)),"success" => $success);
	 }
	 $this->session->set("messages",array_unique($this->messages));
	 return $this;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Get messages
	*
	* @return	array
	*/
	function get(){
		$messages = $this->session->get("messages",TRUE);
		if($messages) $this->session->delete("messages");
	    return $messages;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Show messages via js
	*
	* @return	void
	*/
	function render(){
		$messages = $this->get();
		if(!$messages) return FALSE;
		$code = "window.addEvent('domready',function(){\n";
		foreach($messages as $msg){
			if(!empty($msg['text'])) $code .= "msg(\"{$msg['text']}\",\"{$msg['success']}\");\n";
		}
		$code .= '});';
		js($code,TRUE,TRUE);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------
