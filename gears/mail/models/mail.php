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
 * Mail model - sends email via PHPMailer class
 *
 * @package		CoGear
 * @subpackage	Mail
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Mail extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function Mail(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Set addressee
	*
	* @param	mixed object/int user/uid
	* @return	object
	*/
	function to($to){
		if(is_object($to)){
				$to = trim($to->secemail) == '' ? $to->email : $to->secemail;
		}
		$this->phpmailer->AddAddress($to);
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set mail author
	*
	* @param	string
	* @param	string
	* @return	object
	*/
	function from($from=FALSE,$from_name = FALSE){
		$this->phpmailer->From = $from ? $from : $this->gears->mail->site_email;
		$this->phpmailer->FromName = $from_name ? $from_name : (isset($this->gears->mail->from_name) ? $this->gears->mail->from_name : $from);
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set mail subject
	*
	* @param	string
	* @return	object
	*/
	function subject($subject){
		$this->phpmailer->Subject = t($subject);
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set mail body
	*
	* @param	mixed string/array template/text
	* @param	array
	* @return	object
	*/
	function body($template,$args = FALSE){
		$this->phpmailer->IsHTML(true);
		if(!is_array($template)){
			if(strpos($template,' ') == FALSE) $template = 'mail email/'.$template;
			$this->phpmailer->Body =  $this->_template($template,$args,TRUE).$this->_template('mail email/signature',array(),TRUE);
		}
		else {
			$this->phpmailer->Body = reset($template).$this->_template('mail email/signature',array(),TRUE);
		}
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Send mail
	*
	* @return	void
	*/
	function go(){
		 if(isset($this->gears->mail->smtp->host,$this->gears->mail->smtp->user,$this->gears->mail->smtp->password)){
			$this->phpmailer->Host = $this->gears->mail->smtp->host;
			$this->phpmailer->Username = $this->gears->mail->smtp->user;
			$this->phpmailer->Password = $this->gears->mail->smtp->password;
			$this->phpmailer->SMTPAuth = TRUE;
			if($this->gears->mail->smtp->secure){
				$this->phpmailer->SMTPSecure = $this->gears->mail->smtp->secure;
			}
			$this->phpmailer->IsSMTP();
			//$this->phpmailer->SMTPDebug  = TRUE;
		}
		$this->phpmailer->Send();
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Clear PHPMailer addresses
	*
	* @return	object
	*/
	function clear(){
		$this->phpmailer->ClearAddresses();
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Send mail with args
	*
	* @param	mixed	$to
	* @param	string	$from
	* @param	string	$from_name
	* @param	string	$subject
	* @param	string	$template
	* @param	array		$args
	* @return	object
	*/
	function send($to,$from,$from_name,$subject,$template,$args = FALSE){
		$this->load->library('mail _phpmailer','phpmailer');
		$this->to($to)
		->from($from,$from_name)
		->subject($subject)
		->body($template,$args)
		->go()
		->clear();
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	*  Link to send method with default From values
	*
	* @param	mixed	$to
	* @param	string	$from
	* @param	string	$from_name
	* @param	string	$subject
	* @param	string	$template
	* @param	array		$args
	* @return	object
	*/
	function s($to,$subject,$template,$args){
	    $this->send($to,FALSE,FALSE,$subject,$template,$args);	
		return $this;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------