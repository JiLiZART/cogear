<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package   CodeIgniter
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 * @link        http://codeigniter.com
 * @since     Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package   CodeIgniter
 * @subpackage  Libraries
 * @category    Libraries
 * @author      ExpressionEngine Dev Team
 * @link        http://codeigniter.com/user_guide/general/controllers.html
 */

/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package   CoGear
 * @author      CodeMotion, Dmitriy Belyaev
 * @copyright   Copyright (c) 2009, CodeMotion
 * @license     http://cogear.ru/license.html
 * @link        http://cogear.ru
 * @since     Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Controller
 *
 * Increase default Controller functionality, making changes even in some default methods
 *
 * @package   CoGear
 * @subpackage  Controller
 * @category    Libraries
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
class Controller extends CI_Base {

  // Will user to store document title with delay - if you want to be out of array order
  public $title_delayed = array();
  // Will sign if you want to replace last title segment with your own
  public $title_delayed_remove_last = FALSE;
  // Var which can store some data for final template compilation
  public $content = array(
  'meta' => array(
    'info'=>'',
    'title'=>array(),
    'keywords'=>'',
    'description'=>''
    ),
  );
  public $hooks = array();
  public $override_output = FALSE;
  
  /**
   * Constructor
   *
   * @return  void
   */
  function Controller()
  { 
    parent::CI_Base();
    
    $this->_ci_initialize();
    // Let's get work
    // Name is the name of current gear
    $this->name = trim($this->router->fetch_directory(),'/');
    // Gear is a config of current gear - for quick access
    $this->gear = array2object($this->load->get_gear($this->name));
    // If there is no gear - there is nothing to show.
    if(!$this->gear) return show_404();
    // Extract gears info from loader into object form to easily operate them
    $this->gears = array2object($this->load->get_gears());
    // $this->site will be site settings (link to global gear)
    $this->site = TRUE;
    $this->site =& $this->gears->global;
    // Load required libraries and helpers
    $this->load->library('session');
    if($this->site->database){
	 if(!strpos($this->site->database,'dbprefix')){
		 $this->site->database.='?dbprefix=';
	 }
     $this->load->database($this->site->database.'&db_debug=TRUE',FALSE,TRUE);
    }
    $this->load->helper('cookie');
    if(!isset($this->site->cookie_domain,$this->site->cookie_path)){
      $this->site->cookie_domain = $this->site->url == 'localhost' ? FALSE : parse_url('http://'.$this->site->url,PHP_URL_HOST);
      if($this->site->subdomains){
        $this->site->cookie_domain = '.'.$this->site->cookie_domain;
      }
      if(defined('SUBDIR')){
        $this->site->cookie_path = SUBDIR;
      }
      else $this->site->cookie_path = '/';
    }
    if(!$this->site->database && $this->name != 'install'){
      redirect('/install/');
    }
    elseif($this->site->offline && !strpos($this->name,'captcha')){
      title($this->site->offline_title,TRUE,TRUE);
      if($this->user->get('id') != 1 && $this->router->fetch_class() != 'offline' && $this->name != 'user') redirect('/offline/');
    }
    elseif(!$this->site->offline && $this->router->fetch_class() == 'offline'){
      redirect('/');
    }
    
    // IMPORTANT: do not delete this!
    // Auto load gears models and libraries
    // Make it after Controller init, because it must be assined to Controller
    $this->load->gears_autoload();
    // Setup cache from config param
    $this->cache->enable = $this->site->cache;
    // Set come important Quicky params
    // Executing constuct hook
    log_message('debug', "Controller Class Initialized");
    $this->_hook(get_class($this),'construct');
    $this->output->set_header('Content-type: text/html; charset=utf-8');
    $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
    $this->output->set_header("Expires: " . date('r'));
    $this->output->set_header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
  }
  
  /**
  * Get param that not defined
  *
  * @param  strint    Param name
  * @return object
  */
  function __get($name){
    $name = strtolower($name);
    if(isset($this->load->autoload['models'][$name])){
      $file = $this->load->autoload['models'][$name];
      $gear = basename(dirname(dirname($file)));
      $model = strtolower(str_replace(EXT,'',basename($file)));
      $this->load->model($gear.' '.$model,$name);
      return $this->$name;
    }
    elseif(isset($this->load->autoload['classes'][$name])){
      $file = $this->load->autoload['classes'][$name];
      $gear = basename(dirname(dirname($file)));
      $class = strtolower(str_replace('.class'.EXT,'',basename($file)));
      $this->load->library($gear.' '.$class,NULL,$name);
      return $this->$name;
    }
    else return FALSE;
  }
    
  /**
  * _template
  *
  * Is responsible to add template for current output
  *
  * @param  string  $tpl_name   Name of template to load. Use % prefix to load templates from current template, not from current gear
  * @param  mixed $position   If it's integer => it's place template at numeric position order, otherwise (if array) it's used as data for template
  * @param  boolean $return     Whether to return parsed template or not. If it's true, template won't be added to the output.
  * @param  boolean $replace      Whether to replace template on position
  * @return mixed
  */  
  function _template($tpl_name, $position_or_data = 50, $return = FALSE, $replace = FALSE){
    // If you put some array instead of template name - it will be passed throug to template class  and be parsed
    if(is_array($tpl_name)){
        $path = $tpl_name;
    }
    else {
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
        $path = GEARS.$this->router->fetch_directory().'templates/'.$parts[0];
      }
    
      // If template extention is not defined, it will be added automatically
      if(!in_array(pathinfo($path, PATHINFO_EXTENSION),array('tpl','php'))) {
       $path .= '.tpl';
      }
    }
    if($tpl_name == 'form') debug($path);
    // Parse template
    return $this->template->parse($path, $position_or_data, $return, $replace);
  }
  // ----------------------------------------------------------------------------------------------------------------

    /**
    * __call
    *
  * Standart PHP function to override unexists class calling. If controller doesn't have called method it will try to invoke hooks and totally hooks created function
  * @param  string $name   Function name
  * @param  array  $args   Function arguments
  * @return mixed 
  */
  
  function __call($name,$args){
    // You can hook (create or even override) any Controller method
    // Create simple hook 
    // function [gear_name]_[gear_controller]_[gear_method]($CI){ }
    // this hook-created function will be also able to catch hooks from other gears
      if(function_exists(trim($this->router->fetch_directory(),'/').'_'.strtolower(get_class($this)).'_'.$name)){
      // Execute hooks and get the results
      if($hook_args = $this->_hook($this->name,$name,FALSE,$args)){
        // merging changes with existance args
        $args = array_merge($hook_args,$args);      
      }
      // Call created method
      $result = call_user_func_array(trim($this->router->fetch_directory(),'/').'_'.strtolower(get_class($this)).'_'.$name,array_merge(array("CI"=>&$this),$args));
      // If it has result it will be delivered to all after-hooks as first argument
      if(isset($result)) array_unshift($args,$result);
      // Call for after hooks
      $this->_hook($this->name,$name,'after',$args);
      return $result;     
      }
      // All the same as before, but more globally - for every controller in gear
    // function [gear_name]_[gear_method]($CI){ }
    elseif(function_exists(trim($this->router->fetch_directory(),'/').'_'.$name)){
      if($hook_args = $this->_hook($this->name,$name,FALSE,$args)){
        $args = array_merge($hook_args,$args);      
      }
      $result = call_user_func_array(trim($this->router->fetch_directory(),'/').'_'.$name,array_merge(array("CI"=>&$this),$args));
      if(isset($result)) array_unshift($args,$result);
      $this->_hook($this->name,$name,'after',$args);
      return $result;  
      }
      // Super-global method. Will be linked to any Controller of any gear
    elseif(function_exists('_'.$name)){
      if($hook_args = $this->_hook('',$name,FALSE,$args)){
        $args = array_merge($hook_args,$args);      
      }
      $result = call_user_func_array('_'.$name,array_merge(array("CI"=>&$this),$args));
      if(isset($result)) array_unshift($args,$result);
      $this->_hook('',$name,'after',$args);
      return $result;  
      }
      // If you asked for non-existance method
    else {
      show_error('Real function <strong>'.$name.'</strong> ('.get_class($this).') doesn\'t exist and any hooks hadn\'t been found.');
      return FALSE;
      }
  }
  // ----------------------------------------------------------------------------------------------------------------


    /**
    * _remap
    *
    * Takes control of controller workflow.
    *
    * @param  string  $method   Responsible for current method of class that is executed
    * @return void
    */
    function _remap($method){
       $this->method = $method;
   $this->_hook('*','header',FALSE,$this->uri->rsegments);
   // design header - stores in current template folder
   $this->_template('%header', 1);
   // Executing global hook for after creating header
   $this->_hook('*','header','after',FALSE,$this->uri->rsegments);
     // Execute before method hook
     $this->_hook($this->name,$method,FALSE,$this->uri->rsegments);
     // Call needed method
     if(!$this->override_output){
       call_user_func_array(array($this,$method),$this->uri->rsegments);
     }
   $this->_hook('*','after',FALSE,$this->uri->rsegments);
     // Execute after method hook
     $this->_hook($this->name,$method, 'after',$this->uri->rsegments);
     // Execute global hook after footer
   $this->_hook('*','footer',FALSE,$this->uri->rsegments);
   // Parsing global footer template
      $this->_template('%footer',1000);
   // Execute global hook after footer
     $this->_hook('*','footer', 'after',$this->uri->rsegments);
     // Compile all the data with current content array
   $this->template->compile($this->content);
    }
   // ----------------------------------------------------------------------------------------------------------------    
  
  /**
  *_hook
  *
  * This method is responsible for hooks over the Controller.
  *
  * @param  string  $class Class Name
  * @param  string  $method   Method Name
  * @param  string  $suffix   In case to execute some hooks after functions you may able to add suffix for hooks
  * @param  mixed $args   Arguments transmitted to hook
  * @return void
  */
    function _hook($class, $method, $suffix = FALSE, $args = FALSE){
    $install_hooks = array('meta','global');
    $gears = ($this->site->database) ? $this->gears : array_combine($install_hooks,$install_hooks);
    // Switch class name and method name to lowercase
        $class = strtolower($class);
        $method = strtolower($method);
        // if class is * then the hook is global 
         if($class == '*'){
          $func_name = $method;
         }
         // otherwise it will be connected with class_name
         else {
          $func_name = $class.'_'.$method;
         }
        // if suffix is set - add it after name
        if($suffix){
         $func_name = $func_name.'_'.$suffix;
        }
        // IMPORTANT
        // Number of this function arguments can be more than 4
        // If $args is array - it will be used as args
        // If $args is not array - all the arguments after the third will be used as hook args
        $args = is_array($args) ? $args : array_slice(func_get_args(),3);
        // If there there are more than 0 arguments
        if(count($args) > 0){
         // first will be link to CI
         $params = array('CI'=>&$this);
         foreach($args as $key=>$value) {
       $params[$key] = $value;
      }  
        }
        // Otherwise there is only one argument - CI link itself
        else {
         $params = array('CI'=>&$this);
        }
        if(isset($this->hooks[$func_name])){
      foreach($this->hooks[$func_name] as $function){
        if($result = call_user_func_array($function,$params)){
          if(is_array($result)){
            if($result[0] instanceof Controller || $result[0] instanceof Model){
              array_shift($result);
            }
            $params = array_merge($result,$params);
          }
        }
      }
      if(!empty($result)) return $result;
        }
        else {
        // if hook with this name is exist - include it
           foreach($gears as $gear=>$value){
         if(function_exists($gear.'_'.$func_name)){
           $this->hooks[$func_name][] = $gear.'_'.$func_name;
           if($result = call_user_func_array($gear.'_'.$func_name, $params)){
              if(is_array($result)){
                if($result[0] instanceof Controller || $result[0] instanceof Model){
                  array_shift($result);
                }
                $params = array_merge($result,$params);
              }
           }
         }
           }
      if(!empty($result)) return $result;
         }

    } 
  // ----------------------------------------------------------------------------------------------------------------    
    
  /**
   * Initialize
   *
   * Assigns all the bases classes loaded by the front controller to
   * variables in this class.  Also calls the autoload routine.
   *
   * @access  private
   * @return  void
   */
  function _ci_initialize()
  {
    // Assign all the class objects that were instantiated by the
    // front controller to local class variables so that CI can be
    // run as one big super object.
    $classes = array(
              'config'  => 'Config',
              'input'   => 'Input',
              'benchmark' => 'Benchmark',
              'uri'   => 'URI',
              'output'  => 'Output',
              'router'  => 'Router',
              'info' => 'Info',
              'cache' => 'Cache',
              'load' => 'Loader'
              );
    foreach ($classes as $var => $class)
    {
      $this->$var = NULL;
      $this->$var =& load_class($class);
    }
  }
  // --------------------------------------------------------------------
}
// ------------------------------------------------------------------------
