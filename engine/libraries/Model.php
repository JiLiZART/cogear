<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author			ExpressionEngine Dev Team
 * @copyright		Copyright (c) 2008, EllisLab, Inc.
 * @license			http://codeigniter.com/user_guide/license.html
 * @link				http://codeigniter.com
 * @since			Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category		Libraries
 * @author			ExpressionEngine Dev Team
 * @link				http://codeigniter.com/user_guide/libraries/config.html
 */
 
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
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package		CoGear
 * @subpackage	Model
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Model {

	var $_parent_name = '';
	public $gear = FALSE;
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	function Model()
	{
		/* Some code from original CodeIgnier method) with a bit of change*/
		// We don't want to assign the model object to itself when using the
		// assign_libraries function below so we'll grab the name of the model parent
		$this->_parent_name = ucfirst(get_class($this));
		$this->_assign_libraries(TRUE);
		log_message('debug', "Model Class Initialized");
		$this->_hook(strtolower(get_class($this)),"construct");
		$class = strtolower(get_class($this));
		if(isset($this->load->_ci_models_gears[$class])) $this->gear = $this->load->_ci_models_gears[$class];
	}
	
	/**
	* Magic get method
	*
	* @param	string	Method name.
	*/
	function __get($name){
		$CI =& get_instance();
		return $CI->__get($name);
	}
	
	/**
	* __call 
	*
	* Standart PHP function to override unexists class calling. If model doesn't have called method it will try to invoke hooks and it's method with '_'.$name OR totally hooks created function
	*
	* @param	string 	$name 	Function name
	* @param	array 	$args 	Function arguments
	* @return	mixed 
	*/
	function __call($name,$args){
		// If you want model method to be able to work with hooks it must start with underscore (_method)
		if(method_exists($this,'_'.$name)){
			// Get hooks args
			if($hook_args = $this->_hook(strtolower(get_class($this)),$name,FALSE,$args,FALSE)){
				$args = is_object($hook_args[0]) ? array_slice($hook_args,1) : $hook_args;			
			}
			// Call hooked method
			$result = call_user_func_array(array($this,"_".$name),$args);
			// If there is result - add it into the begining of args array
			if(isset($result)) array_unshift($args,$result);
			// call after method hooks
			$response = $this->_hook(strtolower(get_class($this)),$name,'after',$args);
			if(!empty($response)){
				if(is_array($response) && count($response) != count($args)){
					$result = $response;
				}
			}

			return $result;  		
	    }
	    // If there is no class method, but there is method like 'model_method_' - it will be used as Model own method
	    elseif(function_exists(strtolower(get_class($this)).'_'.$name.'_')){
			if($hook_args = $this->_hook(strtolower(get_class($this)),$name,FALSE,$args,FALSE)){
				$args = is_object($hook_args[0]) ? array_slice($hook_args,1) : $hook_args;			
			}
			array_unshift($args,$this);
			$result = call_user_func_array(strtolower(get_class($this)).'_'.$name.'_',$args);
			if(isset($result)) array_unshift($args,$result);
			$this->_hook(strtolower(get_class($this)),$name,'after',$args);
			return $result;  		
	    }
	    else {
			show_error(t("Function {$name} (model <strong>".get_class($this)."</strong>) doesn't exist."));
			return FALSE;
	    }
	}
	

	
	/**
	* _template 
	*
	* Responsible to add template for current output
	*
	* @param	string 	$tpl_name 				Name of template to load. Use % prefix to load templates from current template, not from current gear
	* @param	mixed 	$position_or_data 	If it's integer => it's place template at numeric position order, otherwise (if array) it's used as data for template
	* @param	mixed 	$return 					Whether to return parsed template or not. If it's true, template won't be added to the output.
	* @param	boolean 	$replace 				Should the template replace existing one on position
	*/	
	function _template($tpl_name, $position_or_data = FALSE, $return = FALSE, $replace = FALSE){
		$CI =& get_instance();
		global $class;
		if(!is_array($tpl_name)){
			// If templates name begins with %, then it's belong to current global template
		    $parts = preg_split('~[^\w_\/\.-]+~', $tpl_name, 3, PREG_SPLIT_NO_EMPTY);
			if (empty($parts[0])) show_error("Couldn't load template by request '{$class}'.");
			if(strpos($tpl_name,'%') === 0){
				$path = ROOTPATH.'/templates/'.$this->site->template.'/'.end($parts);
			}
			elseif(count($parts) > 2) {
				$path = GEARS.implode('/',$parts);
			}
			elseif(count($parts) > 1){
				$path = GEARS.$parts[0].'/templates/'.$parts[1];
			}
			else {
				$path = GEARS.$this->gear.'/templates/'.$parts[0];
			}
			// If template extention is not defined, it will be added automatically
			if(!in_array(pathinfo($path, PATHINFO_EXTENSION),array('tpl','php'))) {
			 $path .= '.tpl';
			}
		}
		else {
			$path = $tpl_name;
		}
		$CI =& get_instance();
		// Parse template
		return $CI->template->parse($path, $position_or_data, $return, $replace);
	}
	// ----------------------------------------------------------------------------------------------------------------
	
    /**
	*_hook
	*
	* Call hook
	*
	* @param	string $class		 Class Name
	* @param	string $method 	Method Name
	* @param	string $suffix 		In case to execute some hooks after functions you may able to add suffix for hooks
	*/
	function _hook($class, $method, $suffix = FALSE, $args = array(), $return = TRUE){
		$CI =& get_instance();
		$class = strtolower($class);
        $method = strtolower($method);
        // Join them with .
         if($class == 'global'){
          $func_name = $method;
         }
         else {
          $func_name = $class.'_'.$method;
         }
        // if suffix is set - add it after name
        if($suffix){
         $func_name = $func_name.'_'.$suffix;
        }
		$args = is_array($args) ? $args : array_slice(func_get_args(),3);
        if(count($args) > 0){
         $args = array_merge(array('Model'=>&$this),$args);
        }
        else {
         $args = array('Model'=> &$this);
        }
        // if hook with this name is exist - include it
        $func_args = FALSE;
        $result = TRUE;
        $last_hook = FALSE;
        if(isset($CI->hooks[$func_name])){
  			foreach($CI->hooks[$func_name] as $function){
					 if($last_hook == $func_name && is_array($func_args)) {
						 $params = array_merge($func_args,$args);
					 }
					 else{
					  $params =& $args;
					  $func_args = FALSE;
					 }
					 $last_hook = $func_name;
					 $func_result = call_user_func_array($function,$params);
					 if(is_array($func_result) && $func_result !== $args) $func_args = $func_result;
					 else if($return && $func_result === FALSE){
						 $result = FALSE;
					 }
			 }      
        }
        else {
			foreach($CI->gears as $gear=>$value){
				 $prefix = $gear;
				 $path = $this->gear ? $prefix.'_'.$func_name.'_' : $gear.'_'.$func_name.'_';
				 if(function_exists($path)){
					 if($last_hook == $func_name && is_array($func_args)) {
						 $params = array_merge($func_args,$args);
					 }
					 else{
					  $params =& $args;
					  $func_args = FALSE;
					 }
					 $last_hook = $func_name;
					 $CI->hooks[$func_name][] = $path;
					 $func_result = call_user_func_array($path,$params);
					 if(is_array($func_result) && $func_result !== $args) $func_args = $func_result;
					 else if($return && $func_result === FALSE){
						 $result = FALSE;
					 }
				 }
			 }
         }
         return $return ? $result : $func_args;
    }
   // ----------------------------------------------------------------------------------------------------------------
   
     /**
	 * Assign Libraries - original Model class CodeIgniter function with changes - CI vars cannot override 'table' (use it for current db table) and 'name' - current model name
	 *
	 * Creates local references to all currently instantiated objects
	 * so that any syntax that can be legally used in a controller
	 * can be used within models.  
	 *
	 * @param	boolean 
	 * @access	private
	 */	
	function _assign_libraries($use_reference = TRUE)
	{
		$CI =& get_instance();
		foreach (array_keys(get_object_vars($CI)) as $key)
		{
			if ( ! isset($this->$key) AND $key != $this->_parent_name AND !in_array($key,array('name','table')))
			{			
				// In some cases using references can cause
				// problems so we'll conditionally use them
				if ($use_reference == TRUE)
				{
					$this->$key = NULL; // Needed to prevent reference errors with some configurations
					$this->$key =& $CI->$key;
				}
				else
				{
					$this->$key = $CI->$key;
				}
			}
		}
	}
    // ----------------------------------------------------------------------------------------------------------------

}
// ----------------------------------------------------------------------------------------------------------------
