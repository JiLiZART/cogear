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
 * User groups CP controller
 *
 * @package		CoGear
 * @subpackage	User groups
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller {
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		$this->table = 'user_groups';
		$this->load->model('form form');
	    d('user_groups');
		$this->form->set($this->table);
	    $this->form->input('name',array('label'=>t('group_name',$this->table),'size'=>25,'validation'=>'required','js_validation'=>'required'));
	    $this->form->image('icon',array('upload_path'=>mkdir_if_not_exists(ROOTPATH.'/uploads/user_groups/'),'thumbs'=>$this->gears->user->avatar->size,'width'=>'200','height'=>'200'));
	}
	// ------------------------------------------------------------------------

	/**
	*  Show user groups
	*
	* @return	void
	*/
	function index(){
	  $this->form->clear();
	  $header = array(
      'id'=>array('ID','text','5%'),
      'name'=>array(t('!user_groups group_name'),'link','45%'),
      'icon'=>array(t('!user avatar'),'image','45%'),
      'delete' => array(t('!edit delete'),'checkbox','5%')
      );
      $result = $this->db->order_by('id','asc')->get($this->table)->result_array();
	  foreach($result as $key=>$value){
		  if(isset($value['icon'])){
		   $result[$key]['icon'] = make_icons($value['icon'],array("24x24"));
		  }
      }
      $info = array(
      'link' => array('edit'),
      'link_add' => array('name'),
      'primary' => 'id',
      'multiple' => TRUE,
      'undel' => array(0,1,100),
      'no_class' => TRUE,
      'noname' => TRUE
      );
      $this->form->grid('user_groups',$header,$result,$info);
      $this->form->buttons();      
      $this->form->compile();
	  $this->builder->a($this->builder->span(t('!edit create')),'/admin/user_groups/create/','button',TRUE);
	}
	// ------------------------------------------------------------------------

	/**
	* Create user group
	*
	* @return	void
	*/
	function create(){
		$this->form->input('id', array('label'=>t('ID'),'validation'=>'required|numeric','js_validation'=>'digit'),1);
		if($result = $this->form->result()){
			$result['name'] = url_name($result['name']);
			if($this->form->save($this->table,$result)){
			 msg('!form saved','success');
			 redirect('/admin/user_groups/');
			}
			else {
			 msg('!form saved_failure','failure');
			 $this->form->compile();
			}
		}
		else {
			$this->form->buttons('create');
			$this->form->compile();
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Edit user group
	*
	* @param	int
	* @return	void
	*/
	function edit($name = FALSE){
		if(!$name) show_404();
		if($result = $this->form->result()){
				if($this->form->update($this->table,$result,array('name'=>$name))){
				 msg('!form saved','success');
				 redirect('/admin/user_groups/');
				}
				else {
				 msg('!form saved_failure','failure');
				 $this->form->compile();
				}
		}
		else {
			if($elem = $this->db->get_where($this->table,array('name'=>$name))){
				$this->form->buttons('save');
				$this->form->set_values($elem->row());
				$this->form->compile();
				
			}
			else {
				show_404();
			}
		}	
	}
	// ------------------------------------------------------------------------

	/**
	* Delete user group
	*
	* @return	void
	*/
	function delete(){
		 $delete = $this->input->post("delete");
		 if(is_array($delete) && count($delete) > 0 && $this->db->where_in('name',$delete)->delete($this->table)){
			  msg(t('!form deleted_success'));
			  redirect('/admin/user_groups/');
		 }
		 else {
			 show_404();		 
		 }
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------