<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author		CodeMotion, Dmitriy Belyaev
 * @copyright	Copyright (c) 2009, CodeMotion
 * @license		http://cogear.ru/license.html
 * @link		http://cogear.ru
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Buddy main controller.
 *
 * @package		CoGear
 * @subpackage	Buddy
 * @category	Gears controllers
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	*  Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		// Set language department
		d('buddy');
	}
	// ------------------------------------------------------------------------

	/**
	* Process buddy ajax request 
	*
	* @param string $action
	* @return json
	*/
	function index($action){
		$to = $this->input->post('id');
		$body = $this->input->post('msg');
		$from = $this->user->get('id');
		if(!$to OR !$body OR !$from) ajax(FALSE);
		$result = $this->buddy->$action($to,$from,$body);
		if($result === TRUE){
			ajax(TRUE,t($action.'_success'));
		}
		else {
			ajax(FALSE,$result === FALSE ? t('!errors error') : $result);
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Check for friendship via ajax request
	*
	* @return json
	*/
	function check(){
		$to = $this->input->post('id');
		$from = $this->user->get('id');
		$result = $this->buddy->check_db($to,$from);
		if($this->buddy->data){
			if($this->buddy->data->approved){
				ajax(TRUE);
			}
			else {
				ajax(FALSE,$result);
			}
		}
		else {
		  ajax(TRUE);
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Approve friendship from pm message
	*
	* @return void
	*/
	function approve(){
		$from = $this->input->post('from');
		$to = $this->user->get('id');
		$pm = $this->input->post('pm');
		$approved = $this->input->post('approved');
		if(!$from OR !$pm OR !$approved OR !$pm) _404();
		$this->user->refresh();
		$this->user->refresh($from);
		if($approved){
			if($this->buddy->approve($to,$from,$pm)){
				msg('!buddy new_friend');
			}
			else {
				msg('!errors error',FALSE);
			}
		}
		else {
			if($this->buddy->delete($to,$from,$pm)){
				msg('!buddy lost_friend');
			
			}
			else {
				msg('!errors error',FALSE);
			}
		}
		redirect("/{$this->gears->mail->url}/");
	}
	// ------------------------------------------------------------------------

	/**
	*  Search friends for autocomplete.
	*
	* @return json
	*/
	function search(){
		$buddies = $this->buddy->get();
		$value = $this->input->post('value');
		if(!$buddies OR !$value) exit();
		$output = array();
		foreach($buddies as $user){
		   if(strstr(strtolower($user['name']),strtolower($value))) array_push($output,$user['name']);
		}
		if($output) ajax(json_encode($output),TRUE);
		exit();
	}
	// ------------------------------------------------------------------------
	
	/**
	* Show form to send a message wit friendship
	*/
	function form(){
		$this->form->set('buddy')
		->title('!buddy '.$action.'_title',FALSE,FALSE,FALSE)
		->textarea('msg',array('js_validation'=>'required','label_hidden'=>TRUE,'description'=>' '))
		->buttons('submit')
		->set_values(array('body'=>t('!buddy '.$action.'_msg')));
		$this->builder->div($CI->form->compile(TRUE).'<br>','exact hidden','buddy-holder',TRUE);
		d();
	}
	

}
// ------------------------------------------------------------------------