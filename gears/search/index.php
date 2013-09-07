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
 * Search controller
 *
 * @package		CoGear
 * @subpackage	Search
 * @category	Gears controllers
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/*
	* Contstructor
	*
	* @return void
	*/
	function __construct(){
		parent::Controller();
		$this->pager_use_get = TRUE;
	}
	// ------------------------------------------------------------------------

	/*
	* Show result
	*
	* @param string
	* @param int
	* @return void
	*/
	function index($page = 0){
		$this->form->set('search')->input('query')->buttons();
		$query = $this->session->get('search/query');
/*
		if($post = $this->input->post('query')){
			$query = $post;
			$this->session->set('search/query',$query);
		}
*/
		$query = $this->input->get('query');
		if(!$query) {
			$this->builder->h1(t('!gears search'),TRUE);
			info(FALSE,FALSE,3,200);
		}
		else {
			$query = urldecode($query);
			title($query);
			if(!strpos($query,' ')) $search = $query.'*';
			else $search = $query;
			$this->db->where('MATCH (nodes.name, nodes.body) AGAINST ("'.$this->db->escape($search).'" IN BOOLEAN MODE)',FALSE,FALSE);
			$this->nodes->get($page,5);
			$this->form->method = 'get';
			$this->form->set_values(array('query'=>$query));
			$this->builder->h1(t('!search search_for_query',$query).' '.$this->builder->sup($this->nodes->count),3);
		}
		$this->form->compile(4);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------