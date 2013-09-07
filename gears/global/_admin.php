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
 * Site settings edit
 *
 * @package		CoGear
 * @subpackage	Global
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
		$this->load->model('form form');
	}
	// ------------------------------------------------------------------------

	/**
	* Edit site settings
	*
	* @return	void
	*/
	function index(){
		d('global_settings');
		foreach(new DirectoryIterator('./templates/') as $file){
			if($file->isDir() && !$file->isDot() && strpos($file->getFilename(),'.') !== 0){
				$templates[] = $file->getFilename();
			}
		}
		$config = array('validation'=>'required','js_validation'=>'required');
		$this->form->set('global_settings')
		->title('!gears global',FALSE,TRUE,TRUE)
		->input('url',array('validation'=>'required','js_validation'=>'required'))
		->input('name',$config)
		->select('template',array('options'=>array_combine($templates,$templates)))
		->checkbox('cache')
		->checkbox('debug')
		->checkbox('offline')
		->input('offline_title')
		->textarea('offline_message')
		->input('date_format',$config)
		->input('per_page',$config)
		->buttons('save')
		->set_values($this->site);
		if($result = $this->form->result(TRUE)){
			$result['url'] = str_replace(array('http://','www.'),'',$result['url']);
			if($result['cache'] != $this->site->cache){
				$this->cache->flush();
			}
			$this->info->set(GEARS.'global/global')->change($result)->compile();
			$this->cache->clear('gears');
			msg('form.saved');
			redirect('/admin/global/');
		}
		$this->form->compile();
	}
	// ------------------------------------------------------------------------
	/**
	 * Simply flush cache
	 */
	public function clear_cache(){
		$this->cache->flush();
		redirect();
	} 

}
// ------------------------------------------------------------------------