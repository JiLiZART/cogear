<?php
/**
* Twitter control panel
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Twitter
* @version		$Id$
*/
class _Admin extends Controller{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();
		d('twitter');
	}
	
	/**
	* Settings.
	*
	* @return	void
	*/
	public function index(){
		$this->form->set('admin_twitter')->label_after(TRUE)
		->title(t('%settings'))
		->checkbox('node_info')
		->checkbox('cross_posting')
		->checkbox('template_tweet')
		->input('login')
		->password('password')
		->buttons('save');
		if($result = $this->form->result(TRUE)){
			$this->info->set(GEARS.'twitter/twitter')->change($result)->compile();
			if(empty($result['login'])) $result['login'] = '';
			if(empty($result['password'])) $result['password'] = '';
 			msg('form.saved');
			redirect('/admin/twitter');
		}
		else {
			$this->form->set_values($this->gears->twitter);
		}
		$this->form->compile();
	}
}