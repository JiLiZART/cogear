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
 * Sidebar model
 *
 * @package		CoGear
 * @subpackage	Sidebar
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Sidebar extends Model {
	/**
	* All widgets
	*
	* @var	array
	*/
	public $widgets = array();
	/**
	* Current widgets
	*
	* @var	array
	*/
	public $cur_widgets = array();
	// Cut from
	public $cut_from = FALSE;
	public $cut_to = FALSE;
	public $cache = TRUE;
	
	/**
	* Constructor
	*
	* @return	void
	*/
	function Sidebar(){
		parent::Model();
		$CI =& get_instance();
		$this->get_widgets();
		$this->cur_widgets = $CI->cache->get('sidebar/cur_widgets',TRUE);
		if(!$this->cur_widgets){
			$widgets = $this->db->order_by('position','asc')->get('widgets')->result();
			if($widgets){
				foreach($widgets as $widget){
					if(!isset($this->widgets[$widget->name])) continue;
					$this->cur_widgets[$widget->position] = array(
					'name'=> $widget->name,
					'title' => t('!widgets '.$widget->name),
					'gear' => $this->widgets[$widget->name]['gear']
					);
				}
			}
			$CI->cache->tags('widgets')->set('sidebar/cur_widgets',$this->cur_widgets,FALSE);
		}
	}


	
	/**
	* Get widgets
	*
	* @return	array
	*/
	function get_widgets($cache = TRUE){
		$CI =& get_instance();
		$this->widgets = $CI->cache->get('sidebar/widgets',TRUE);
		if(!$cache OR !$this->widgets){
			$files = glob(GEARS.'*/widgets/*'.EXT);
			foreach($files as $file){
				$gear = basename(dirname(dirname($file)));
				if(!$CI->gears->$gear){
					 continue;
				}
				$widget = basename(str_replace(EXT,'',$file));
				$this->widgets[$widget] = array('name'=>t($widget),'id'=>$widget,'gear'=>$gear);
			}
			$CI->cache->tags('widgets')->set('sidebar/widgets',$this->widgets,FALSE);
		}
		return $this->widgets;
	}


	
	/**
	* Add widget
	*
	* @param	string title
	* @param	strint	content
	* @param	mixed	int/string/boolean range_start/class/just_add
	* @param	mixed	int/boolean range_end/replace
	* @return	object
	*/
	function add(){
			$args = array_pad(func_get_args(),4,FALSE);
			$widget['title'] = $args[0];
			$widget['content'] = $args[1];
			$widget['simple'] = TRUE;
			// ====================================================
			switch(gettype($args[2])){
				case 'string':
				$widget['class'] = $args[2];			
				break;
				case 'integer':
					// ====================================================
					switch(gettype($args[3])){
						case 'integer':
						$this->cut_from = $args[2];
						$this->cut_to = $args[3];
						case 'boolean':
							if($args[3]) $this->widgets[$args[2]] = $widget;
							else {
									array_insert($this->widgets,$widget,$args[2]);
							}
						break;
					}
					// ====================================================
				break;
				case 'boolean':
				$this->widgets[] = $widget;
				break;
			}
			// ====================================================
			return $this;
	}


	/**
	* Load widget
	*
	* @param	object
	* @return	mixed
	*/
	function load_widget($widget){
		$CI =& get_instance();
		$widget['path'] = GEARS.$widget['gear'].'/widgets/'.$widget['name'].EXT;
		$widget['config_path'] = GEARS.$widget['gear'].'/widgets/'.$widget['name'].'.info'; 
		if(file_exists($widget['path'])){
			require_once($widget['path']);
			if(file_exists($widget['config_path'])){
				$widget['config'] = $CI->info->read($widget['config_path']);
			}
			if(function_exists($widget['name'].'_widget')) {
				$params = array('CI'=>&$CI);
				if(isset($widget['config'])) $params[] = array2object($widget['config']);
				$widget['content'] = call_user_func_array($widget['name'].'_widget',$params);
				if(!$widget['content']) {
					unset($widget);
				}
			}
			else return FALSE;
		}
		return isset($widget) ? array2object($widget) : FALSE;
	}



	/**
	* Edit widget config - default function. 
	*
	* @param	array
	* @return	void
	*/
	function edit_config($widget){
		$CI =& get_instance();
		if(is_string($widget)){
			if(!isset($this->widgets[$widget])) return;
			else $widget =& $this->widgets[$widget];
		}
		$config = @file_get_contents($widget['config_path']);
		if(!$config){
		 title(t('!widgets '.$widget['name']));
		 info(t('!sidebar no_config'));
		}
		else {
			$CI->form->set('widget_config')
			->title('!widgets '.$widget['name'],FALSE,FALSE,TRUE)
			->textarea('params',array('validation'=>'required','js_validation'=>'required','value'=>$config))
			->buttons('save');
			if($result = $CI->form->result()){
				if(@file_put_contents($widget['config_path'],$result['params'])){
					msg('!form saved');
				}
				else msg('!form saved_failure',FALSE);
			}
			$CI->form->set_values($result)->compile();
		}
	}


	
	/**
	* Compile sidebar
	*
	* @param	mixed	int/boolean
	* @return	mixed
	*/
	function _compile($return = FALSE){
		$CI =& get_instance();
		if(!$this->cur_widgets) return;
		foreach($this->cur_widgets as $key=>&$widget){
			if($this->cut_from && $this->cut_to && $key  > $this->cut_from && $key <= $this->cut_to){
				unset($this->cur_widgets[$key]);
			}
			else if(!isset($widget['simple'])) $this->load_widget(&$widget);
		}
		return $this->_template("sidebar sidebar",array('widgets'=>$this->cur_widgets),$return);
	}

}
// ------------------------------------------------------------------------