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
 * Pages CP controller
 *
 * @package		CoGear
 * @subpackage	Pages
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
	}
	// ------------------------------------------------------------------------

	/**
	* List pages
	*
	* @return	void
	*/
	function index(){
		d('pages');
		$pages = $this->db->order_by('position','asc')->get('pages')->result_array();
		$header = array(
		'position' => array('','dragndrop','5%'),
		'name' => array(fc_t('page'),'link','50%'),
		'url_name' => array(fc_t('!edit url_name'),'text','30%'),
		'link' => array('','icon','30%','/gears/nodes/img/icon/unpublished.png'),
		'delete' => array(fc_t('!edit delete'),'checkbox','5%'),
		);
		$info = array(
		'link' => array('/admin/pages/createdit','/pages'),
		'link_add' => array('id','url_name'),
		'primary' => 'id',
		'noname' => TRUE,
		'dragndrop' => 'position'
		);
		if($this->input->post('delete')) remove('page*');
		$this->form->set('pages_list')->grid('pages',$header,$pages,$info)->compile();
		$this->builder->a($this->builder->span(t('!edit create')),l('/admin/pages/createdit/'),'button',TRUE);
	}
	// ------------------------------------------------------------------------

	/**
	* Create and edit pages
	*
	* @param	int
	* @return	void
	*/
	function createdit($id = FALSE){
		$config = array('validation'=>'required','js_validation'=>'required');
		$this->form->set('pages_createdit')
		->input('name',$config)
		->input('url_name')
		->editor('body',$config)
		->textarea('description')
		->input('keywords')
		->buttons('preview','save');
		if($id && $page = $this->db->get_where('pages',array('id'=>$id))->row()){
			$this->form->title(t('!edit edit'),-100,FALSE,TRUE);
			$this->form->set_values($page);
			$body  =$this->form->find('body');
		}
		else $this->form->title(t('!edit create'),-100,FALSE,TRUE);
		if($result = $this->form->result()){
			if(!$id) $result['aid'] = $this->user->get('id');
			remove('page*');
			$result['last_update'] = date('Y-m-d H:i:s');
			if(isset($page) && $this->form->update('pages',$result,array('id'=>$page->id))){
				redirect('/admin/pages/');
			}
			else if(!$id && $this->form->save('pages',$result)){
				redirect('/admin/pages/');
			}
		}
		$this->form->compile();
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------