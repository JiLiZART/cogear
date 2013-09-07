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
 * Sidebar CP controller
 *
 * @package		CoGear
 * @subpackage	Sidebar
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
	function _Admin(){
		parent::Controller();
		d('sidebar');
	}
	// ------------------------------------------------------------------------

	/**
	* Edit sidebar prefs
	*
	* @param	mixed	$name
	* @return	void
	*/
	function index($name = FALSE){
		$this->sidebar->get_widgets(FALSE);
		if($name){
			if(isset($this->sidebar->widgets[$name]) && $this->sidebar->load_widget($this->sidebar->widgets[$name]) && function_exists($name.'_widget_config')){
				call_user_func_array($name.'_widget_config',array($this));
			}
			else $this->sidebar->edit_config($name);
		}
		else {		
		$this->form->catch_dragndrop('widgets',array(),'name');
		if($this->input->post('name')){
			 $this->cache->clear('sidebar/cur_widgets');
		}
		if($this->sidebar->cur_widgets) 
		foreach($this->sidebar->cur_widgets as $key=>&$widget){
			$widget['id'] = $widget['name'];
			unset($this->sidebar->widgets[$widget['name']]);
			$widget['name'] = $this->builder->a('<img src="/gears/global/img/icon/edit.png" alt="edit" >',l('/admin/sidebar/'.$widget['name'])).' '.t('!widgets '.$widget['name']);
		}
		if($this->sidebar->widgets)
		foreach($this->sidebar->widgets as $key=>&$widget){
			$widget['id'] = $widget['name'];
			$widget['name'] = $this->builder->a('<img src="/gears/global/img/icon/edit.png" alt="edit" >',l('/admin/sidebar/'.$widget['name'])).' '.t('!widgets '.$widget['name']);
		}
		$this->form->set('widgets')
		->title('widgets')
		->drag_list('active',array('items'=>array(),'target'=>TRUE,'class'=>'widget sortables','title'=>t('%active_many').' '.t('widgets'),'additems'=>$this->sidebar->cur_widgets))
		->drag_list('name',array('items'=>$this->sidebar->widgets,'class'=>'widget sortables','title'=>t('%inactive_many').' '.t('widgets')))
		->div_clear()
		->buttons()
		->compile();
		}
	}
	// ------------------------------------------------------------------------

}
// ------------------------------------------------------------------------