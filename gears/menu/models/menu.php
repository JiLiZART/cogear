<?php
/**
 * Menu model
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Menu
 * @version			$Id$
 */
class Menu extends Model{
	/**
	 * @array
	 */
	private $menus = array();
	/**
	 * Constructor
	 */
	public function __construct(){
		parent::Model();
		$this->init();
	}

	/**
	 * Initialization
	 */
	private function init(){
		if(!$this->menus = retrieve('menus')){
			$this->menus = array4key(
			$this->db->order_by('position')->get_where('menu',array('is_active'=>'true'))->result_array()
			,'url_name');
			if($items = $this->db
			->select('menu_items.*, menu.url_name',FALSE)
			->join('menu','menu.id = menu_items.mid AND menu.is_active = "true"','inner')
			->order_by('position')->get('menu_items')->result_array()){
					foreach($items as $item){
						$this->menus[$item['url_name']]['items'][] = $item;
					}
			}
			store('menus',$this->menus,FALSE,'menu');
		}
	}

	/**
	 * Show all menus
	 */
	public function _show(){
		$output = array();
		if($this->menus){
			foreach($this->menus as $name=>$menu){
				$output[$name] = $this->show_menu($menu);
			}
		}
		$this->content['menu'] = $output;
	}

	/**
	 * Show menu
	 *
	 * @param	mixed	$menu	Can be a menu array or menu url_name
	 * @return	string
	 */
	public function show_menu($menu){
		if(!is_array($menu) && is_string($menu) && isset($this->menus[$menu])){
			$menu = $this->menus[$menu];
		}
		if(empty($menu)) return;

		if(!empty($menu['show_pattern'])){
			$patterns = preg_split("/\s+/", $menu['show_pattern'], -1, PREG_SPLIT_NO_EMPTY);
			foreach($patterns as $pattern){
				if(!$this->check_pattern($pattern)) return '';
			}
		}
		if(!$this->check_access($menu['access']) OR (!isset($menu['items'])) OR empty($menu['items'])) return '';
		foreach($menu['items'] as $key=>&$item){
			if(empty($item['pattern'])) continue;
			$patterns = preg_split("/\s+/", $item['pattern'], -1, PREG_SPLIT_NO_EMPTY);
			foreach($patterns as $pattern){
				if($this->check_pattern($pattern)){
					$item['active'] = TRUE;
				}
				if(!strpos($item['link'],'http://')) $item['link'] = '/'.ltrim($item['link'],'/');
			}
			if(!$this->check_access($item['access'])) unset($menu['items'][$key]);
		}
		$menu['output'] = intval($menu['output']);
		$output = $this->_template('menu '.$menu['template'],array('menu'=>$menu),$menu['output'] ? $menu['output'] : TRUE);
		return is_bool($output) ? '' : $output;
	}

	/**
	 * Check pattern
	 *
	 * Compare pattern with uri string
	 * @param	string	$pattern		Pattern regexp
	 * @return	boolean
	 */
	private function check_pattern($pattern){
		if(empty($pattern)){
			 return TRUE;
		}
		$pattern = str_replace(
		array('/:any',':any',':num',':alpha'),
		array('/?.*','.*','\d+','\w+'),
		$pattern);
		$uri = empty($this->uri->uri_string) ? '/' : ltrim($this->uri->uri_string,'/');
		if($pattern{0} == '!'){
			return !preg_match('#^'.substr($pattern,1).'$#iu',$uri);
		}
		return preg_match('#^'.$pattern.'$#iu',$uri);
	}

	/**
	 * Check access
	 *
	 * @param	string	$access	Access string
	 * @param	boolean
	 */
	private function check_access($access){
		if($access == 'user' && !$this->user->get('id')){
			return FALSE;
		}
		if($access == 'guest' && $this->user->get('id')){
			return FALSE;
		}
		return TRUE;
	}
 }