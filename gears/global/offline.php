<?php
/**
* Site offline controller
*
* Simply shows site offline page.
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Global
* @version		$Id$
*/
class Offline extends Controller{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();	
		if($this->user->get('id') != 1){
			$this->no_sidebar = TRUE;
			$this->content['cpanel'] = '	';
		}
	}
	
	/**
	* Show page
	*/
	public function index(){
		$this->builder->h1($this->site->offline_title,TRUE);
		info($this->site->offline_message);
	}
}