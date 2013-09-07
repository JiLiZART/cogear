<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package   CoGear
 * @author      CodeMotion, Dmitriy Belyaev
 * @copyright   Copyright (c) 2009, CodeMotion
 * @license     http://cogear.ru/license.html
 * @link        http://cogear.ru
 * @since     Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Favorites controller
 *
 * @package   CoGear
 * @subpackage  Favorites
 * @category    Gears controllers
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
class Index extends Controller{
  /**
  * Constructor
  *
  * @return void
  */
  function __construct(){
	parent::Controller();
  }
  // ------------------------------------------------------------------------

  /**
  * Show user favorite nodes
  *
  * @param  string
  * @param  int
  * @return void
  */
  function index($url_name = FALSE, $page = 0){
	if($url_name && $user = $this->user->info($url_name)){
	  $this->user->head($user,'favorites');
	  $this->db->join('favorites',"favorites.nid = nodes.id AND {$this->db->dbprefix}favorites.uid = ".$user->id,'left');
	  $this->db->where(array('favorites.uid'=>$user->id));
	  $this->nodes->get($page,FALSE,TRUE);
	}
	else return _404();
  }
  // ------------------------------------------------------------------------

  /**
  * Add/remove node to user favorites via ajax
  *
  * @return json
  */
  function action(){
	d('favorites');
	if (acl('favorites manage'))
	{
		$nid = $this->input->post('nid');
		$action = $this->input->post('action');
		if(!$nid OR !$action) ajax(FALSE);
		$this->cache->tags('users/'.$this->user->get('id'))->clear();
		if($this->favorites->manage($nid)){
		  ajax(TRUE,t('add_success'));
		}
		else ajax(TRUE,t('remove_success'));
	}
	else ajax(TRUE,t('just_registered'));
  }
  // ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------