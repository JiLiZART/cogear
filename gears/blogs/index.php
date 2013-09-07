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
 * Blogs main controller
 *
 * @package		CoGear
 * @subpackage	Blogs
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	*  Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
	}
	// ------------------------------------------------------------------------

	/**
	*  Show user blog nodes
	*
	* @param	string	$url_name
	* @param	int		$page
	* @return	void
	*/
	function index($url_name = FALSE, $page = 0){
		if(!is_numeric($url_name)){
			if($url_name && $user = $this->user->info($url_name)){
				$this->user->head($user,'blog');
				title($user->name);
				if(!empty($url_name) && $user->id == $this->user->get('id')){
					$this->nodes->published = FALSE;
				}
				$this->db->where(array('aid'=>$user->id));
			}
			elseif($this->gears->community) {
				$this->community->show($url_name,$action,$subaction);
				return;
			}
		}
		else {
			$page = $url_name;
		}
		$this->nodes->get($page);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------