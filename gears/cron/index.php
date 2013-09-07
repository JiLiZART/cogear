<?php
/**
 * Cron controller
 *
 * 
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Cron
 * @version			$Id$
 */
class Index extends Controller{
	/**
	 * Constructor
	 */
	public function __construct(){
		parent::Controller();
	} 
	
	/**
	 * Index 
	 */
	public function index(){
		$this->cron->check();
		die();
	} 
}