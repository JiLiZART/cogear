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
 * Form model
 *
 * @package		CoGear
 * @subpackage	Form
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Form extends Model {
	// Form name
	public $name;
	// Form class
	private $class;
	// Form action
	public $action = FALSE;
	// Form enctype
	public $enctype = FALSE;
	// Form key, used to validate request and protect posting from bots
	public $key = FALSE;
	// Form method
	public $method = 'post';
	// Form elements
	public $elements = array();
	public $backlink = TRUE;
	// Whether fieldset has been opened or not
	private $fieldset = FALSE;
	// Whether div has been opened or not
	private $div = FALSE;
	// Whether to use ajax validation
	public $ajax = FALSE;
	// Use js validation or none
	private $js_validation = FALSE;
	// Has submit button or no
	private $has_submit =  FALSE;
	// Has been form checked or no
	private $checked = FALSE;
	// Dependencies
	private $depends = FALSE;
	// Sortables
	private $sortables = FALSE;
	// Label after
	private $label_after = FALSE;
	// Some of elements needed to be md5 encoded
	private $md5 = FALSE;
	// If some data is post via cookies (to prevent sniffers)
	private $via_cookie = FALSE;
	// Save function last insert id
	public $insert_id = FALSE;
	public $cursor = FALSE;
	// Locations for js_validation 
	public $errors_location = 1;
	public $errors_msg = 0;
	// If you need to pass some publiciables to hooks - use this param
	public $data = FALSE;
	// Current element link
	private $current = FALSE;
	// Reset empty variables value
	public $reset_empty = TRUE;
	/**
	* Constructor
	*
	* @return	void
	*/
	function Form(){
		 parent::Model();
	}
	// ------------------------------------------------------------------------
    
	/**
	* Define current form name and configuration parameters
	*
	*@param	string $name Form name
	*@param	array $config Parameters
	*@return void
	*/
    function set(){
     $args = func_get_args();
     $args = array_pad($args,2,FALSE);
     // Clear form in case of class being used before 
     $this->clear();
     $this->has_submit = FALSE;
     if($this->key) $this->keygen();
     // If isset $name, it becomes id and name
     if($args[0]) {
      $this->id = $this->name = array_shift($args);
     }
     // Otherwise it will be called by default - 'form'
     else {
      $this->id = $this->name = 'form';
     }
     if(is_string($args[0])){
      $this->class = $args[0];
     }
     // initialize $config
	 return $this;
    }
	// ------------------------------------------------------------------------

	/** 
	* Set action
	*@param	string	Action
	*@return object this
	*/
	function action($action){
		$this->action = l($action);
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Switch ajax use in form
	*
	* @param	boolean	$value	
	* @return	void
	*/
	function ajax($value = FALSE){
		if(!$value){
			$this->ajax = $this->ajax ? FALSE : TRUE; 
		}
		else $this->ajax = $value;
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Enable or disable key check
	* 
	* @param	boolean		$value
	* @return	void
	*/
	function key($value = FALSE){
		$this->key = $value;
		return $this;
	}
	// ------------------------------------------------------------------------	
	
	/**
	* Generate key
	*
	*@return void
	*/
	function keygen(){
	 // if secret key is needed (there is no 'avoid_key' config param) and hasn't been set yet 
     if($this->key){
         // get key from encription from join of session_id and client ip-adress
     	 $this->load->library('encrypt');
		 $this->key = $this->encrypt->encode($this->session->get('session_id').$this->session->get('ip_address'));
     }
     return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Set label after checboxes, etc
	*
	* @param	boolean	Label after.
	* @return	object
	*/
	public function label_after($value = FALSE){
		$this->label_after = $value;
		return $this;
	}
	
	/**
	* Clean up class params
	*
	*@return void
	*/
	function clear(){
	    // There is no name
		$this->name = FALSE;
		// Default enctype
		$this->enctype = FALSE;
		// Empty elements
		$this->elements = array();
		$this->ajax = FALSE;
		$this->class = FALSE;
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Move current element to position
	*
	* @param	int
	* @param	boolean
	* @return	object
	*/
	function move($id,$replace = FALSE){
		 if(!$this->current) return $this;
		 if(isset($this->elements[$id]) && !$replace){
			 array_insert($this->elements,$this->current,$id);
		 }
		 elseif($replace) $this->elements[$id] = $this->current;
		 return $this;
	}
	// ------------------------------------------------------------------------


	/**
	* Unexistable method catcher. Create neede method on-fly.
	*
	* @param	string
	* @param	array
	* @return	object
	*/
	function __call($func,$args){
		$CI =& get_instance();
		//if(method_exists($this,'_'.$func)) return parent::__call($func,$args);
		$args = array_pad($args,4,FALSE);
		// set params for current elem if func_name begins with set
		// Example: $this->form->input('text')->setClass('nice')->setTitle('input');
		if(substr($func,0,3) == 'set'){
			$param = strtolower(substr($func,3));
			if($this->current){
			 switch($param){
				 case 'position':
				  $this->move($args[0]);
				 break;
				 case 'replace':
				  $this->move($args[0],TRUE);
				 break;
				 default:
				 $this->current[$param] = $args[0];
			 }
			}
			return $this;
		}
		// Creating elements
		$config =  is_array($args[1]) ? $args[1] : array();
		switch($func){
			// Form title
			// @string title
			// @mixed position
			// @boolean remove_last_title - helps to erase last element in meta title
			// @boolean add_to_metatitle - add form title to meta title
			case 'title':
			 $config['type'] = 'title';
			 $args[0] = fc_t($args[0]);
			 if($args[3]) title($args[0],$args[2],$args[3]);
			 $position = $args[1];
			 $replace = $args[3];
			break;
			// Form description 
			// @string description
			// @array config
			// @mixed position
			case 'description':
			 $config['type'] = 'description';
			 $args[0] = fc_t($args[0]);
			break;
			// Input field
			case 'calendar':
				$config['calendar'] = TRUE;
			case 'input':
			case 'text':
				$config['type'] = 'text';
			break;
			// Datetime field
			// @string name
			// @array range 
			case 'datetime':
				if(is_array($args[1])) $config = array_merge($config,$args[1]);
				$config['type'] = 'datetime';
				if(isset($args[1]['range'])){
					$range = $args[1]['range'];
					$to = FALSE;
					$from = FALSE;
					if(!is_array($range)){
						if(is_numeric($range)){
							$from = 1970;
							$to = $range;
						}
						elseif(strpos($range,'-') !== FALSE){
							$tmp = explode('-',$range);
							$from = $tmp[0];
							$to = $tmp[1];
						}
						elseif(strpos($range,',') !== FALSE){
							$range = explode(',',$range);
						}
					}
					if(!is_array($range)){
						$range = array();
						for($i = $from; $i <= $to; $i++){
							$range[] = (int)$i;
						}
					}
					$years_range = array_combine($range,$range);
				}
				else {
					$years_range = array(
					date('Y')-1 => date('Y')-1,
					date('Y') => date('Y'),
					date('Y')+1 => date('Y')+1
					);
				}
				$config['options']['year'] = $years_range;
				for($i = 0; $i < 24; $i++){
				 $z = $i < 10 ? '0'.$i : $i;
				 $config['options']['hour'][$z] = $z;
				}
				for($i = 0; $i < 60; $i++){
				 $z = $i < 10 ? '0'.$i : $i;
				 $config['options']['minute'][$z] = $z;
				}
				for($i = 01; $i <= 31; $i++){
				 $z = $i < 10 ? '0'.$i : $i;
				 $config['options']['day'][$z] = $z;
				}
				for($i = 1; $i <= 12; $i++){
				 $z = $i < 10 ? '0'.$i : $i;
				 $config['options']['month'][$z] = strftime('%B',2419200*$i);
				}
				if(!isset($config['value'])){
					$config['value'] = array(
					'year'=>date('Y'),
					'month'=>date('m'),
					'day'=>date('d'),
					'hour'=>date('H'),
					'minute'=>date('i')
					);
				}
				else {
					$tmp = $config['value'];
					$config['value'] = array(
					'year'=>date('Y',strtotime($tmp)),
					'month'=>date('m',strtotime($tmp)),
					'day'=>date('d',strtotime($tmp)),
					'hour'=>date('H',strtotime($tmp)),
					'minute'=>date('i',strtotime($tmp))
					);
				}
			break;
			// Password field
			case 'password':
				$config['type'] = 'password';
			break;
			// File
			case 'file':
				 $config['type'] = 'file';
				 $this->enctype = 'multipart/form-data';
		    break;
			// File Url
			case 'file_url':
				 $config['type'] = 'text';
				 $config['is_url'] = TRUE;
				 $config['js_validation'] = 'url';
		    break;
		    case 'image_url':
				 $config['type'] = 'text';
				 $config['is_image'] = TRUE;
				 $config['is_url'] = TRUE;
				 $config['allowed_types'] = 'jpg|jpeg|gif|png|ico';
				 $config['js_validation'] = 'url,file[jpg,gif,png,ico]';
		    break;
			// Image
			case 'image':
				 $config['type'] = 'file';
				 $config['is_image'] = TRUE;
				 if(empty($config['allowed_types'])) $config['allowed_types'] = 'jpg|jpeg|gif|png';
				 if(empty($config['js_validation'])) $config['js_validation'] = 'file[jpg,gif,png]';
				 $this->enctype = 'multipart/form-data';
			break;
			// Hidden field			
			case 'hidden':				
				$config['type'] = 'hidden';
			break;
			// Textarea
			case 'textarea':
				$config['type'] = 'textarea';
				if(!isset($config['class'])) $config['class'] = 'grow';
			break;
			// Simple select
			case 'select':
				$config['type'] = 'select';
			break;
			// Image select
			case 'image_select':
				 $config['type'] = 'image_select';
				 $config['class'] = 'left-out';
			break;
			// Radio button
			case 'radio':
				 $config['type'] = 'radio';
			break;
			// Checkbox
			case 'checkbox':
				 $config['type'] = 'checkbox';
				 if(!isset($config['label_after'])){
					 $config['label_after'] = $this->label_after;
				 }
			break;
			// Fieldset & Div
			/**
			* @string name
			* @string label
			* @boolean is_opened
			*/
			case 'div':
			case 'fieldset':
				  if($args[0]){
				   if($this->$func && !$args[2]){
					   $args[0] = FALSE;
					   $args[2] = FALSE;
					   call_user_func_array(array($this,$func),$args);
				   }
				   $this->$func = $args[0];
				   $args[2] = TRUE;
				  }
				  else {
				   $this->$func = FALSE;
				  }
				  $config = array(
					  'type'=>$func,
					  'legend'=>$args[1],
					  'open'=>$args[2],
				  );
				  $position = FALSE;
				  $replace = FALSE;
			break;
			case 'drag_list':
				$config['type'] = 'drag_list';
				$config['sortables'] = TRUE;
				$config['template'] = GEARS."form/templates/drag.list.tpl";
			break;
			case 'div_clear':
				$args[0] = substr(md5(time()),0,4);
				$config = array('type'=>'clear');
 			break;
 			case 'br':
				$config['type'] = 'br';
 			break;
 			default:
 			return parent::__call($func,$args);				
		}
		if(is_array($args[1])) $config = array_merge($args[1],$config);
		
		if(count($args) > 3){
			if(!isset($position)) $position = $args[2];
			if(!isset($replace)) $replace = $args[3];
		}
		elseif(count($args) > 2){
			if(!isset($position)) $position = $args[2];
			if(!isset($replace)) $replace = FALSE;
		}
		else {
			$position = FALSE;
			$replace = FALSE;
		}
		$this->add($args[0],$config,$position,$replace);	
		return $this;
	}
	// ------------------------------------------------------------------------

	

    /**
	* Buttons
	*
	* Args can be either string names of buttons or an array of them. Last arg can be numeric - it will determine buttons position.
	*
	*@param	mixed
	*@param	mixed
	.....................
	*@return object
	*/
	function _buttons(){
		if($this->has_submit) return $this;
		$this->has_submit = TRUE;
		
		$args = func_get_args();
		if(!$args) return $this;
		
		if(is_numeric(end($args))){
			$i = array_pop($args);
		}
		else  $i = 300;
		
		if(isset($args[0]) && is_array($args[0])){
			$buttons = $args[0];
		}
		else if(!isset($args[1]) && is_string($args[0]) && strpos($args[0],',') !== FALSE){
			$buttons = _explode(',',$args[0]);
		}
		else $buttons = $args;
		if(count($buttons) < 1) return $this;
	    // Secret key of the form automatically placed before buttons
	    if($this->key) $this->add('key',array('type'=>'hidden','value'=>$this->key));
		// Form action hidden field is added
		$this->add('action',array('type'=>'hidden','value'=>'submit'),$i);
	    // Seek thru the buttons
		foreach($buttons as $name=>$button){
		 if(!$button) continue;
		 if(is_array($button)){
		  if(!is_string($name)) $name = isset($button['name']) ? $button['name'] : 'submit';
		  $config = $button;
		 }
		 else {
		  if(strpos($button,'|')){
		    $tmp = explode('|',$button);
		    $name = array_shift($tmp);
		    $config = array();
		    $config['onclick'] = "if(!confirm('".str_replace("\n","\\n",t(implode('|',$tmp)))."')) return false;"; 

		  }
		  else {
			  $name = $button;
			  $config = array();
			  if($name == 'delete'){
			   $config['onclick'] = "if(!confirm('".t('!form are_you_shure')."')) return false;";
			  }
		  }
		 }
		 if(!isset($config['value'])) $config['value'] = has_t($name) ? t($name) : (has_t('edit '.$name) ? t('edit '.$name) : $name);

		 // if type isn't set, it gets from name (easy - 'submit' => submit)
		 if(!isset($config['type'])) {
		  $config['type'] = in_array($name,explode(',',"submit,reset,button,image")) ? $name : 'submit';
		 }
		 if($config['type'] == 'submit'){
			 $config['accesskey'] = 's';
		 }
		 // all buttons changes 'action' field value
		 $chunk = "$('{$this->name}').getElement('input[name=action]').set('value',this.name);";
		 $config['onclick'] = isset($config['onclick']) ? $chunk.$config['onclick'] : $chunk;
		 // add button
		 $this->add($name,$config,++$i);
		}
		if(reset($buttons) && $this->backlink) $this->add('back',array('type'=>'back'),++$i);
		return $this;	 
	}
	// ------------------------------------------------------------------------
	
	/**
	* Add single button
	*
	* @param	string
	* @param	array
	* @param	int
	* @return	object
	*/
	function button($name,$config = array(),$position=320){
		if(!isset($config['type'])) {
		  $config['type'] = in_array($name,explode(',',"submit,reset,button,image")) ? $name : 'submit';
		 }
		if($name == 'delete'){
			$config['onclick'] = "if(!confirm('".t('!form are_you_shure')."')) return false";
		}
		$chunk = "$('action').set('value',this.name);";
		$config['onclick'] = isset($config['onclick']) ? $chunk.$config['onclick'] : $chunk;
		$config['value'] = isset($config['value']) ? $config['value'] : (has_t($name) ? t($name) :  t('edit '.$name));
		$this->add($name,$config,$position);
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Set cursor for add fields
	*
	* @param	int
	* @return	object
	*/
	function set_cursor($id = FALSE){
		$this->cursor = $id;
		return $this;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Synonim for set cursor
	*
	* @param	int
	* @return	object
	*/
	function cursor($id = FALSE){
		return $this->set_cursor($id);
	}
	
	
	/**
	* Set model data
	*
	* @param	mixed
	* @return	object
	*/
	function _data($data = FALSE){
		if($data) $this->data =& $data;
		return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Add element
	*
	* @param	string
	* @param	array
	* @param	int
	* @param	boolean
	* @return	object
	*/
	function add($name = FALSE,$config = array(),$position = FALSE,$replace = FALSE){
	 // set name
	 $config['name'] = $name;
	 // translate label
	 $config['label'] = isset($config['label']) ? $config['label'] : at($name);
	 // translate description
	  $config['description'] = isset($config['description']) ? $config['description'] : at($name.'_description');
	 // set required param to show * after label
	 if(isset($config['validation']) && strpos($config['validation'],'required') !== FALSE OR isset($config['js_validation']) && strpos($config['js_validation'],'required') !== FALSE) {
	   $config['required'] = TRUE;
	  }
	 // Set id to insert
	 $id = $position ? $position : (count($this->elements) > 0 && max(array_keys($this->elements)) < 290 ? (max(array_keys($this->elements))+1) : count($this->elements)+1);
	 // If form has cursor - set id from it
	 if($this->cursor){
		 $id = $this->cursor;
		 $this->cursor++;
	 }
	 
	 // If element exists - shift array
	 if(isset($this->elements[$id]) && !$replace){
		 array_insert($this->elements,array(),$id);
	 }
	 // Else - replace element
	 elseif($replace) unset($this->elements[$id]);
	 
	 // Seek through params and assign it to element
	 foreach($config as $key=>$value){
	  if(isset($this->elements[$id][$key])){
		  $this->elements[$id][$key] .= $value;
	  }
	  else {
		  $this->elements[$id][$key] = $value;
	  }
	 }
	 // Add js_validation class
	 if(isset($this->elements[$id]['js_validation'])){
	  $this->js_validation = TRUE;
	  $js_validation = explode("|",$this->elements[$id]['js_validation']);
	  $this->add_way($this->elements[$id],'js_validation',"validate['".implode("','",$js_validation)."']");
	 }
	 // Add md5 param
	 $this->add_way($this->elements[$id],'md5');
	 // Add via_cookie param
	 $this->add_way($this->elements[$id],'via_cookie');
	 // Add sortables param
	 $this->add_way($this->elements[$id],'sortables');
	 // Add depends param
	 if(isset($config['depends'])){
	  $this->add_way(&$this->elements[$id],'depends',"depends['{$this->elements[$id]['depends'][0]}','{$this->elements[$id]['depends'][1]}']");
	  js('/gears/form/js/inline/depends',FALSE,TRUE);
	 }
	 // set key for element
	 $this->elements[$id]['key'] = $id;
	 $el =& $this->elements[$id];
	 // link element as current
	 $this->current = $el;
	 return $this;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Adds class to element if it has some param and define form way param
	*
	*@param	array $elem
	*@param	string $name
	*@return void
	*/
	function add_way(&$elem,$name,$value = FALSE){
	  if(isset($elem[$name])){
		  if(!isset($this->$name) OR !$this->$name) $this->$name = TRUE;
		  if(isset($elem['class'])){
			$elem['class'] .= ' '.($value ? $value : $name);	  
		  }
		  else {
			$elem['class'] = $value ? $value : $name;
		  }
	  }
	}
	// ------------------------------------------------------------------------
	
	/**
	* Parse elements via template
	*
	*@return void
	*/
	function parse(){
		 foreach($this->elements as $key=>$element){
			 if($key < -99) unset($this->elements[$key]);
		 }
		 ksort($this->elements);
		 $config = array(
		 'name'=>&$this->name,
		 'elements'=>&$this->elements,
		 'method'=>&$this->method,
		 'action'=>&$this->action,
		 'ajax'=>&$this->ajax,
		 'enctype'=>&$this->enctype,
		 'class'=>&$this->class,
		 'via_cookie'=>&$this->via_cookie,
		 'depends'=>&$this->depends,
		 'js_validation'=>&$this->js_validation,
		 'errors_location'=>&$this->errors_location,
	     'errors_msg'=>&$this->errors_msg,
		 'md5'=>&$this->md5,
		 'sortables'=>&$this->sortables
		 );
		 return $this->_template('form',$config,TRUE);
	}
	// ------------------------------------------------------------------------
	
	/**
	* Checks form to be valid
	*
	*@param	string $name Form name
	*@return boolean
	*/
	function _check($name = FALSE){
     $this->checked = TRUE;
	 $this->load->library('form_validation');

     // Seek thru the elements to find out whether they have validation rules
     foreach($this->elements as $element){
		 if(isset($element['validation'])){
		  // Add validation rules
		  $this->form_validation->set_rules($element['name'],isset($element['label']) ? $element['label'] : $element['name'],$element['validation']);
		 }
     }
     // If form secret key exists and it's posted 
     $key_not_match = FALSE;
     if($this->key && $this->input->post('key')){
      // Compare it to the original
      if($this->encrypt->decode($this->input->post('key')) != $this->session->get('session_id').$this->session->get('ip_address')){
          // Assign new publiciable that shows us if keys doesn't match
		  $key_not_match = TRUE;
      }
     }
	// ------------------------------------------------------------------------
     $this->form_validation->set_error_delimiters('','');
     // If keys match unset previously set error element
     // Run form validation and check for the key
     //count($this->form_validation->_config_rules) > 0 && 
     if((count($_POST) > 0 && $this->form_validation->run() === FALSE && strlen(validation_errors()) != 0 OR $key_not_match) OR count($_POST) == 0){
     	 foreach($this->elements as &$element){
		    if(!in_array($element['type'],explode('|',"submit|reset|button|hidden"))){
				if($this->input->post($element['name']) && !isset($element['md5'])) $element['value'] = set_value($element['name']);
				$element['error'] = form_error($element['name']);
			}
		 }	
		 // If keys not match each other - show an error
		 if($key_not_match) {
			if($this->ajax){
				ajax(FALSE,t('!form key_miss'));
			} else {
				msg('!form key_miss',FALSE);
			}
		 }
		 if($this->ajax){
			 ajax(FALSE,validation_errors());
		 }
		 else {
			  return FALSE;
		 }
     }
     else {
		 if($this->upload()){
			  return TRUE;
		  }
		  else {
			  return FALSE;		  
		  }
      }
	}	
	// ------------------------------------------------------------------------
	
	/**
	* Upload form files
	*
	* @return	boolean
	*/
	function upload(){
	$CI =& get_instance();
    $CI->load->model("upload upload","form_upload");
    $result = array();
		foreach($this->elements as &$element){
		   if($element['type'] == "file") {
			   $result[] = $CI->form_upload->file($element);
/*
			   if($this->input->post($element['name']) !== FALSE){
			    $element['path'] = $this->input->post($element['name']);
			    }
*/
		   }
		   else if(isset($element['is_url']) && $element['is_url']) {
			   $result[] = $CI->form_upload->file($element,TRUE);
		   }
	   }
	   return !in_array(FALSE,$result);
	}
	// ------------------------------------------------------------------------

	
    /**
	* Produce request result
	*
	*@param	array $values In case to use own values in form (edit post - values from database, for example)
	*@return mixed
	*/
	function _result($not_for_db = FALSE,$values = array()){
	 if($values) $this->set_values($values);
	 $check = $this->check();
	 if($check){
		 $this->session->remove('form_fail');
		 return $this->prepare_elements(!$not_for_db);
	 }
	 else {
		 if(count($_POST) > 0) $this->set_values($this->prepare_elements(FALSE));
		 $this->session->set('form_fail',(int)$this->session->get('form_fail')+1);
		 return FALSE;
	 }
	}
	// ------------------------------------------------------------------------

	/**
	* Prepare elements after post catching
	*
	*@return mixed $output
	*/	
	function _prepare_elements($for_db = TRUE){
	$output = array();
	foreach($this->elements as $element){
		   $name = $element['name'];

		   // If elements has via cookies params
		   if($this->via_cookie && isset($element['via_cookie'])){
			   $CI =& get_instance();
			   // if in cookie
			   if($this->input->cookie($name)){
				   $output[$name] = $this->input->cookie($name);
				   delete_cookie($name, '.'.$CI->site->url, '/');
			   }
			   // if in POST
			   elseif($this->input->post($name)){
				   $output[$name] = $this->input->post($name);
			   }
		   }
		   // If element is file
		   else if($element['type'] == 'file'){
			   if($this->input->post($name) !== FALSE){
				   $output[$name] = $this->input->post($name);
			   }
			   elseif(isset($element['path'])) {
				   $output[$name] = $element['path'];
			   }
		   }
		   // if elements is not in some types
		   else if(!in_array($element['type'],array('fieldset','br','error','file','div','title','description'))){
			   // If element name looks like array, just catch all array
			   if(strpos($name,'[')) $name = substr($name,0,strpos($name,'['));
				   // If data is in POST
				   $value = $this->input->post($name);
				   if($value !== FALSE){
					   // fetch POST data to output
					   if($element['type'] == 'datetime' && !strpos($element['name'],'[') && $for_db){
						   $output[$name] = $value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minute']; 
					   }
					   // image select or select data is glued with , to fit 1 field in database
					   elseif(in_array($element['type'],array('select','image_select')) && isset($element['multiple']) && $element['multiple']){
						$output[$name] = implode(',',$value);
					   }
					   // If data is array - serialize it
					   elseif(is_array($value) && count($value) > 0 && $for_db){
						$output[$name] = @serialize($value);
					   }
					   elseif($element['type'] == 'checkbox'){
						if(is_array($value)){
							foreach($value as $key=>$data){
								$output[$name][$key] = TRUE;
							}			
						}
						else {
							if($for_db) $output[$name] = 'true';
							else $output[$name] = TRUE;
						}
					   }
					   else {
						$output[$name] = $value;
					   }
					   // If string is empty or no data - unset it
					   if((!empty($element['stop_reset']) OR !$this->reset_empty OR strpos($name,'id') !== FALSE) && $for_db && (count($output[$name]) < 1 OR trim($output[$name]) == '')){
						unset($output[$name]);
					   }
				   }
				   elseif($element['type'] == 'checkbox' && (!isset($output[$name]) OR !$output[$name])){
						if($for_db) $output[$name] = 'NULL';
						else $output[$name] = FALSE;
				   }
					// automake url_name from name
				   if($name == 'url_name' && $this->input->post('name')){
					   $output[$name] = isset($output[$name]) ? url_name($output[$name]) : url_name($this->input->post('name'));
				   }

			}
	  }
/*
	  debug($output);
	  die('asdasd');
*/
	  return $output;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Set values of elements
	*
	*@param	array $values Values in array(name=>value) format
	*@param	boolean
	*@return object
	*/
	function set_values($values = array(),$wrapper = FALSE){
	 if(!$values) return $this;
	 $values = (array)$values;
	 foreach($this->elements as $key=>&$element){
	  foreach($values as $name=>$value){
		  if(is_array($value) OR is_object($value)){
			 foreach($values as $key=>$item) $this->set_values($item,$key);
			 continue;
		  }
		   $result = $value;  
		   // Wrapper - for elements in same array
		   // body[text], body[info] ... etc.
		   if($wrapper) $name = $wrapper.'['.$name.']';
			  if(strpos($element['name'],$name.'[') !== FALSE){
				  $name = $element['name'];
				  $value = @unserialize($value);
				  preg_match('|\[(.[^\]]*)+\]|i',$name,$matches);
				  if(!$matches OR !$matches[1]) continue;
				  $result = isset($value[$matches[1]]) ? $value[$matches[1]] : FALSE;
			  }

		   if($element['name'] == $name && isset($result) && $result !== NULL && !isset($element['md5'])){
				    // Image processing
					if(isset($element['is_image'])){
					 $data = @getimagesize('.'.$value);
					 if(is_array($data)){
						 $info['src'] = $value;
						 $info['width'] = isset($element['width']) ? $element['width'] : $data[0];
						 $info['height'] = isset($element['height']) ? $element['height'] : $data[1];
						 $result = $info;
					 }
					 // Thumbs processing
					 if(isset($info) && isset($element['thumbs']) && count($element['thumbs']) > 0){
						 $dir = '.'.dirname($info['src']);
						 $file = basename($info['src']);
						 $element['thumbnails'] = array();
						 foreach($element['thumbs'] as $thumb){
						  if(@file_exists($dir.'/'.$thumb.'/'.$file) &&  $data = @getimagesize($dir.'/'.$thumb.'/'.$file)){
							  $element['thumbnails'][$thumb]['src'] = dirname($info['src']).'/'.$thumb.'/'.$file;
							  $element['thumbnails'][$thumb]['width'] = $data[0];
							  $element['thumbnails'][$thumb]['height'] = $data[1];
						  }
						 }
						 $element['thumbnails'] = array_reverse($element['thumbnails']);
					 }
					}
					// File processing
					if($element['type'] == 'file'){
						if(isset($element['class'])) $element['class'] = preg_replace("#\'?required\'?,?#",'',$element['class']);
						$element['has_file'] = TRUE;
					}
					// Selects processing
					if(in_array($element['type'],array('select','image_select')) && isset($element['multiple']) && $element['multiple']){
					 if(is_string($value) && strpos($value,',')){
					  $result = explode(',',$value);
					 }
					}
					// Datetime processing
				   if($element['type'] == 'datetime' && !is_array($result)){
   					   $data = explode(' ',$result);
					   $first = explode('-',$data[0]);
					   $second = explode(':',$data[1]);
					   $result = array(
					   'year' => $first[0],
					   'month' => $first[1],
					   'day' => $first[2],
					   'hour' => $second[0],
					   'minute' => $second[1]
					   );
				   }
					// All other cases
					if(!is_array($result) && !is_object($result) && trim($result) != '' OR is_array($result) ) $element['value'] = $result;
			   }
    	  }
	  }
	  return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Get form compiled and add it to template parser
	*
	*@param	boolean
	*@param	boolean
	*@return object
	*/
	function _compile($return = FALSE, $replace = FALSE){
	 // If thereis no buttons - add it automatically
	 if(!$this->has_submit){
	  $this->buttons(array('submit'=>array(),'reset'=>array()));
	 }
	 if($this->fieldset){
		 $this->fieldset();
	 }
	 if($this->div){
		 $this->div();
	 }
	 // Parse every element to content
	 $output = $this->parse();	 
	 // if it needed to be return
	 if($return === TRUE){
		 return $output;
	 }
	 // add this to template parser
	 else {
		  return $this->_template(array($output),FALSE,$return,$replace);
	 }
	 return $this;
	}	
	// ------------------------------------------------------------------------
	
	
	/**
	* Simple save data to database table
	*
	*@param	string $table Table name
	*@param	array $data Data array to save
	*@return boolean
	*/
	function _save($table,$data = FALSE){
	 $fields = $this->db->list_fields($table);
	 if(!$data) {
	  return FALSE;
	 } 
	 foreach($fields as $field){
	  if(isset($data[$field])){
	    if($data[$field] === 'NULL') $insert[$field] = NULL;
	    else $insert[$field] = $data[$field];
	  }
	  else if($field == 'position'){
		  $insert['position'] = reset($this->db->select_max('position')->get($table)->row_array()) + 1;
	  }
	 }
	 if($this->db->insert($table,$insert)){
		 $this->insert_id = $this->db->insert_id();
		 msg('!form saved');
		 return TRUE; 
	 }
	 else {
		 msg('!form saved_failure',FALSE);
		 return FALSE;
	 }
	}
	// ------------------------------------------------------------------------
	
	/**
	* Simple update data in database table using some params
	*
	*@param	string $table Table name
	*@param	array $data Data array to save
	*@param	array $where Where to update conditions
	*@return boolean
	*/
	function _update($table,$data = FALSE,$where=FALSE){
	 $fields = $this->db->list_fields($table);
	 if(!$data OR !$where) {
	  return FALSE;
	 } 
	 foreach($fields as $field){
		  if(isset($data[$field])){
			if($data[$field] === 'NULL') $insert[$field] = NULL;
			else $insert[$field] = $data[$field];
		  }
	 }
		 if(isset($insert) && $this->db->update($table,$insert,$where)){
			 msg('!form updated');
			 return TRUE; 
		 }
		 else {
			 msg('!form updated_failure',FALSE);
			 return FALSE;
		 }
	}
	// ------------------------------------------------------------------------
	
	/**
	* Simple delete data in database table using some paras
	*
	*@param	string
	*@param	array
	*@return boolean
	*/
	function _delete($table,$where=FALSE){
	 if(!$where) {
	  return FALSE;
	 } 
	 if($this->db->delete($table,$where)){
		 msg('!form deleted');
		 return TRUE; 
	 }
	 else {
		 msg('!form deleted_failure',FALSE);
		 return FALSE;
	 }
	}
	// ------------------------------------------------------------------------
	
	
	/**
	* Grid building
	*
	*@param	array $header Table header
	*@param	array $data Data to output
	*@return boolean
	*/
	function _grid($name,$header,$data,$info = FALSE){
	  foreach($data as $key=>$value){
		  if(isset($data['icon'])){
		   $data[$key]['icon'] = make_icons($data['icon'],array("24x24"));
		  }
		  if(isset($data['avatar'])){
		   $data[$key]['avatar'] = make_icons($data['avatar'],array("24x24"));
		  }
	  }
	 if($this->input->post('result') && isset($info['dragndrop'])){
		 $result = explode(',',$this->input->post('result'));
		 foreach($result as $position=>$id){
			 $this->db->update($name,array($info['dragndrop']=>$position+1),array($info['primary']=>$id));
		 }
		 ajax(TRUE);
	 }
	 $delete = $this->input->post("delete");
	 if(!isset($info['autodelete']) OR $info['autodelete'] !== FALSE){
		  if(is_array($delete) && count($delete) > 0 && $this->db->where_in($info['primary'],$delete)->delete($name)){
			  foreach($data as $key=>$value){
				  if(in_array($value['id'],$delete)){
					  unset($data[$key]);
				  }
			  }
			  if(isset($info['ajax_delete']) && $info['ajax_delete']){
				ajax(TRUE,t('!form deleted_success'));
			  }
			  else {
				  msg(t('!form deleted_success'));
			  }
		  }
     }
	 if(!isset($this->has_grid)){
		 $this->has_grid = FALSE;
	 }
	 else {
		 $this->has_grid = TRUE;
	 }
	 $publics = array('header'=>$header,'data'=>$data,'info'=>$info,'type'=>'grid','template'=>GEARS."form/templates/grid.tpl",'has_grid'=>$this->has_grid);
	 //$this->fieldset(url_name($name),isset($info['noname']) ? FALSE : $name);
	 $this->add($name,$publics);
	 //$this->fieldset();
	 $this->buttons();
	 return $this;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Catching form drag_n_drop
	*
	* @param	string
	* @param	array
	* @param	string
	* @return	void
	*/
	function _catch_dragndrop($table,$where = array(),$param){
		if(($data = $this->input->post($param)) !== FALSE){
			$this->db->delete($table,$where ? $where : "id <> 0");
			if(trim($data) != ''){
				$i = 1;
				$data = explode(',',$data);
				foreach($data as $value){
					$this->db->insert($table,array_merge($where,array($param=>$value,'position'=>$i++)));
				}
			}
			return TRUE;
		}
		return FALSE;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Find and return element
	*
	*@param	string $name 
	*@return mixed
	*/
	function find($name,$field='name'){
		foreach($this->elements as $key=>&$element){
			if($element[$field] == $name){
			 $element['key'] = $key;
			 return $element;
			}
		}
		return FALSE;
	}
	// ------------------------------------------------------------------------
	
	/**
	*  Link to find method
	*
	* @param	string
	* @return	mixed
	*/
	function find_by_name($name){
		return $this->find($name);
	}
	// ------------------------------------------------------------------------

	/**
	* Put changed element back
	*
	* @param	array
	* @return	void
	*/
	function elem($element){
		if(isset($element['key'])) $this->elements[$element['key']] = $element;
	}
	// ------------------------------------------------------------------------


}
// ------------------------------------------------------------------------