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
 * Users controller
 *
 * @package		CoGear
 * @subpackage	Users
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller {
	/**
	*	Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
	    d('users');
  		$this->pager_use_get = TRUE;
	}
	// ------------------------------------------------------------------------
    
    /**
    *	Show users
    *
    * @param	int	$page
    * @return	void
    */
    function	index($page = 0){
		$title = fc_t('list');
		$this->db->where('is_validated IS NOT NULL');
		$this->builder->h1($title.' '.$this->builder->sup($this->builder->small($this->db->count_all_results('users',FALSE))),TRUE);
		title($title,TRUE,TRUE);
		$query = $this->input->get('query');
		$this->form->set('users-search')
		->input('query',array('description'=>'','label'=>t('!gears search'),'value'=>$query))->buttons();
		$this->form->method = 'GET';
		//if(!is_numeric($page)) $result['query'] = $page;
		if($query){
			$this->db->like('users.name',$query)->or_like('users.url_name',url_name($query));
		}
			$config['per_page'] = isset($this->gear->list->per_page) ? $this->gear->list->per_page : 50;
			$page = $this->pager((int)$page, $this->db->count_all_results('users',FALSE),$config);
			$this->db->limit($page['limit'],$page['start']);
		$this->db->select('(SELECT COUNT(id) FROM nodes WHERE aid = users.id) as nodes_num,
		(SELECT COUNT(id) FROM comments WHERE aid = users.id) as comm_num, users.*');
		$users = $this->db->order_by('id','asc')->get('users')->result_array();
		foreach($users as &$user){
			$user['avatar'] = reset(make_icons($user['avatar']));
		}
		$header = array(
				'avatar'=>array('','image','5%','class'=>'avatar'),
				'name'=>array(fc_t('!user name'),'link','30%',FALSE,'left','before'=>'<h1>','after'=>'</h1>'),
				'nodes_num'=>array(fc_t('!gears nodes'),'link','10%'),
				'comm_num'=>array(fc_t('!gears comments'),'link','10%'),
				'reg_date'=>array(fc_t('!user reg_date'),'date','30%'),
				);
				$info = array(
				'link'=>array('/user','/blogs','/pulse'),
				'link_add'=>array('url_name'),
				'noname'=>'true',
				);
		$this->form->grid('users-list',$header,$users,$info)->compile();    
    }
    // ------------------------------------------------------------------------

	/**
	*	Show last registred users
	*
	* @param	int	$page
	* @return	void
	*/
	function fresh($page = 0){
		$title = fc_t('fresh');
		$this->builder->h1($title.' '.$this->builder->sup(
		$this->builder->small($this->db->where('reg_date > "'.date('Y-m-d H:I:s',time()-60*60*24).'"')->count_all_results('users')).'/'.
		$this->builder->small($this->db->where('reg_date > "'.date('Y-m-d H:I:s',time()-60*60*24*7).'"')->count_all_results('users')).'/'.
		$this->builder->small($this->db->where('reg_date > "'.date('Y-m-d H:I:s',time()-60*60*24*30).'"')->count_all_results('users'))
		),TRUE);
		title($title,TRUE,TRUE);
		$config['per_page'] = isset($this->gear->fresh->per_page) ? $this->gear->fresh->per_page : 50;
		$page = $this->pager((int)$page, $this->db->count_all_results('users',FALSE),$config);
		$this->db->limit($page['limit'],$page['start']);
		$this->db->select('(SELECT COUNT(id) FROM nodes WHERE aid = users.id) as nodes_num,
		(SELECT COUNT(id) FROM comments WHERE aid = users.id) as comm_num, users.*');
		$users = $this->db->order_by('reg_date','desc')->get('users')->result_array();
		foreach($users as &$user){
			$user['avatar'] = reset(make_icons($user['avatar']));
		}
		$header = array(
				'avatar'=>array('','image','5%','class'=>'avatar'),
				'name'=>array(fc_t('!user name'),'link','30%',FALSE,'left','before'=>'<h1>','after'=>'</h1>'),
				'nodes_num'=>array(fc_t('!gears nodes'),'link','10%'),
				'comm_num'=>array(fc_t('!gears comments'),'link','10%'),
				'reg_date'=>array(fc_t('!user reg_date'),'date','30%'),
				);
				$info = array(
				'link'=>array('/user','/blogs','/pulse'),
				'link_add'=>array('url_name'),
				'noname'=>'true',
				);
		$this->form->grid('fresh-users',$header,$users,$info)->compile();
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------
