<?php
/**
* Sitemap control panel
*
* 
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Sitemap
* @version		$Id$
*/
class _Admin extends Controller{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();
	}
	
	/**
	* Show form for sitemap generation.
	*/
	public function index(){
		d('sitemap');
		$sitemap_path = ROOTPATH.'/sitemap.xml';
		if($sitemap = file_exists($sitemap_path)){
			$sitemap_size = format_size(filesize($sitemap_path));
			$sitemap_date = filemtime($sitemap_path);
		}
		@ini_set('memory_limit','512M');
		$this->form->set('admin_sitemap')
		->fieldset('sitemap',t('gears.sitemap'))
		->description($sitemap ? t('map_found',$sitemap_size, date('H:i d.m.Y',$sitemap_date)).'<br>'.$this->builder->a('sitemap.xml','http://'.$this->site->url.'/sitemap.xml') : t('no_map'))
		->buttons($sitemap ? 'update_sitemap' : 'create_sitemap');
		if($result = $this->form->result()){
			$this->sitemap->generate();
			redirect('/admin/sitemap/');
		}
		$this->form->compile();
	}	
}