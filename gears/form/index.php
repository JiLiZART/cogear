<?php
/**
 * Form test controller
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		
 * @subpackage	
 * @version			$Id$
 */
class Index extends Controller
{
	/**
	 * Constructor
	 */
	 public function __construct(){
		 parent::__construct();
	 }
	 
	 /**
	  * Index function
	  */
	 public function index(){
		 $this->form->set('test-form')
		 ->radio('some')
		 ->compile();
	 } 
	
} 