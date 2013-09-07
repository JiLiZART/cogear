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
 * Errors model
 *
 * @package		CoGear
 * @subpackage	Errors
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Errors extends Model{
	private $messages = array();
	/**
	* Constructor
	*
	* @return	void
	*/
	function Errors(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Show error
	*
	* @param	string
	* @param	string
	* @return	object
	*/
	function show($message,$header = FALSE){
		if(in_array($message,$this->messages)) return;
		if(!$header) $header = t('!errors error');
		title($header,TRUE);
		$this->messages[] = $message;
		$this->_template("errors error",array('header'=>$header,'message'=>$message),2,998);
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Show 404 error
	*
	* @param	string
	* @return	object
	*/
	function _404($message = FALSE){
		if (!$message) $message = t('!errors 404_descr');
		$alternate = $this->_hook('errors','404','',$message,TRUE);
		$this->output->set_header("HTTP/1.0 404 Not Found");
		if ($alternate) return $alternate;
		else return $this->show($message,t('!errors 404'));
	}
	// ------------------------------------------------------------------------

	/**
	* Show 403 error
	*
	* @param	string
	* @return	object
	*/
	function _403($message = FALSE){
		if(!$message) $message = t('!errors 403_descr');
		$this->output->set_header('HTTP/1.1 403 Forbidden');
		return $this->show($message,t('!errors 403'));
	}
	// ------------------------------------------------------------------------

	/**
	* Show info
	*
	* @param	string
	* @param	string
	* @param	int
	* @param	boolean
	* @return	mixed
	*/
	function info($message = FALSE,$header = FALSE,$position = 10, $replace = FALSE){
		if(in_array($message,$this->messages)) return;
		if(!$message) $message = t('!errors empty');
		$this->messages[] = $message;
		return $this->_template('errors info',array('msg'=>nl2br($message),'header'=>$header),$position,$replace);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------