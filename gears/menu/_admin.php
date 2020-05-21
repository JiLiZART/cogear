<?php
/**
 * Menu control panel 
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Menu
 * @version			$Id$
 */
class _Admin extends Controller {
	/**
	 * Constructor
	 */
	public function __construct(){
		parent::Controller();
		d('menu');
	}  
	
	/**
	 * Index
	 */
	public function index(){
		$header = array(
		'position' => array('','dragndrop'),
		'name' => array(t('edit name'),'link', '10%', FALSE, 'left'),
		'edit' => array(t('items'),'icon','20%','/gears/global/img/icon/edit.png'),
		'status' => array(t('menu_createdit status'),'text','20%'),
		'output' => array(t('menu_createdit output'),'text'),
		'delete' => array(t('edit delete'),'checkbox')
		);
		
		$data = $this->db->order_by('position')->get('menu')->result_array();
		foreach($data as &$item){
			$item['status'] = $this->builder->a(
			$this->builder->img('/gears/menu/img/icon/'.(empty($item['is_active']) ? 'passive' : 'active' ).'.png'),
			'/admin/menu/change_status/'.$item['id']);
		}
		$info = array(
			'link'=>array('/admin/menu/createdit','/admin/menu/items'),
			'link_add'=>array('id','id'),
			'primary'=>'id',
			'dragndrop'=>'position',
			'noname'=>'TRUE',
		);
		if($this->input->post('result')){
				remove('menus');
		}
		$this->form->set('admin/menu')
		->grid('menu',$header,$data,$info)
		->compile();
		button(t('edit create'),'createdit');
	} 
	
	/**
	 * Menu manage
	 */
	public function createdit($id = FALSE){
		d('menu_createdit');
		if($id) $menu = $this->db->get_where('menu',array('id'=>$id))->row();
		$config = array('validation'=>'required','js_validation'=>'required');
		$options = array_map(create_function('$file','return basename($file);'),glob(GEARS.'menu/templates/*'));
		$this->form->set('admin/menu/createdit')
		->input('name',$config)
		->input('url_name',$config)
		->textarea('show_pattern')
		->input('resize')
		->checkbox('crop')
		->select('template',array(
		'options'=>array_combine($options,$options)
		))
		->select('access',array(
		'options' => array(
				'all' => t('all'),
				'user' => t('user'),
				'guest' => t('guest'),
			)
		))
		->input('output',array('validation'=>'required|numeric','js_validation'=>'required|digit'))
		->buttons(empty($menu) ? 'create' : 'save');

		if(!empty($menu)){
			$this->form->set_values($menu);
		}

		if($result = $this->form->result()){
			remove('menus');
			$result['url_name'] = url_name(empty($result['url_name']) ? $result['name'] : $result['url_name']);
			if(empty($menu)){
				$this->form->save('menu',$result);
			}
			else {
				$this->form->update('menu',$result,array('id'=>$menu->id));
			}
			redirect('/admin/menu/');
		}
		$this->form->compile();
	} 
	
	/**
	 * Menu items
	 * 
	 * @param	int		$mid		Menu id
	 * @param	string	$action	Action
	 * @param	int		$id		Menu item id
	 * @return	mixed
	 */
	public function items($mid = FALSE,$action = 'list',$id = FALSE){
		if(!$mid OR !$menu = $this->db->get_where('menu',array('id'=>$mid))->row()){
			redirect('/admin/menu/');
		}
		title(t('items').' &laquo;'.$menu->name.'&raquo;');
		d('menu_item_createdit');
		$config = array('validation'=>'required','js_validation'=>'required');
		switch($action){
			case 'create':
			case 'edit':
				$this->form->set('admin/menu/items/create')
				->input('name',$config)
				->input('url_name')
				->input('link',$config)
				->textarea('pattern')
				->image('image',array(
				'upload_path' => _mkdir(ROOTPATH.'/uploads/menu/'.$menu->url_name.'/'),
				'resize' => empty($menu->resize) ? FALSE : $menu->resize,				
				'crop' => empty($menu->resize) OR empty($menu->crop) ? FALSE : $menu->resize,				
				))
				->select('access',array(
				'options' => array(
						'all' => t('all'),
						'user' => t('user'),
						'guest' => t('guest'),
					)
				))
				->buttons($action == 'edit' ? 'update' : 'save');
				if($action == 'edit'){
					if(!$menu_item = $this->db->get_where('menu_items',array('id'=>$id))->row()){
						redirect('/admin/menu/items/'.$mid.'/');
					}	
					else {
						$this->form->set_values($menu_item);
					}
				}
				if($result = $this->form->result()){
					remove('menus');
				    $result['url_name'] = url_name(empty($result['url_name']) ? $result['name'] : $result['url_name']);
					if(empty($menu_item)){
						$result['mid'] = $menu->id;
						$this->form->save('menu_items',$result);
					}
					else {
						$this->form->update('menu_items',$result,array('id'=>$menu_item->id));
					}
					redirect('/admin/menu/items/'.$mid.'/');
				}
				$this->form->compile();		
			break;
			default:
				$header = array(
					'position' => array('','dragndrop'),
					'name' => array(t('edit name'),'link', '10%', FALSE, 'left'),
					'edit' => array('','icon','20%','/gears/global/img/icon/edit.png'),
					'delete' => array(t('edit delete'),'checkbox')
				);
				$data = $this->db->order_by('position')->get_where('menu_items',array('mid'=>$menu->id))->result_array();
				foreach($data as &$item){
					$item['mid'] = $mid.'/edit/'.$item['id'];
				}
		
				$info = array(
					'link'=>array('/admin/menu/items/','/admin/menu/items'),
					'link_add'=>array('mid','mid'),
					'primary'=>'id',
					'dragndrop'=>'position',
					'noname'=>'TRUE',
				);
				if($this->input->post('result')){
						remove('menus');
				}
				$delete = $this->input->post("delete");
				if($delete && is_array($delete)){
					$this->db->where_in('mid',$delete)->delete('menu_items');
				}
				$this->form->set('admin/menu/items')
				->grid('menu_items',$header,$data,$info)
				->compile();
				button(t('edit create'),'/admin/menu/items/'.$mid.'/create/');
		}
	}
	
	/**
	 * Change menu status 
	 */
	public function change_status($id = FALSE){
		if($id && $menu = $this->db->get_where('menu',array('id'=>$id))->row()){
			$this->db->update('menu',array('is_active' => empty($menu->is_active) ? 'true' : 'NULL'),array('id'=>$menu->id));
			remove('menus');

		}
		redirect('/admin/menu/');
	} 
}