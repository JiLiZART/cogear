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
 
/**
 * CoGear
 *
 * CMS build on CodeIgniter
 *
 * @package		CoGear
 * @author			Dmitriy Belyaev
 * @copyright		Copyright (c) 2008, Dmitriy Belyaev.
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 0.1
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Extends CI Loader class
 *
 * @package		CoGear
 * @subpackage	Libraries
 * @author			Dmitriy Belyaev
 * @category		Libraries
 */
class CI_Loader {
   
    var $gears = array();
    public $autoload = array(
    'models' => array(),
    'classes' => array()
    );
    // Whether to clue included files into one big
    var $speedy = FALSE;

	// All these are set automatically. Don't mess with them.
	var $_ci_ob_level;
	var $_ci_view_path		= '';
	var $_ci_is_php5		= FALSE;
	var $_ci_is_instance 	= FALSE; // Whether we should use $this or $CI =& get_instance()
	var $_ci_cached_vars	= array();
	var $_ci_classes		= array();
	var $_ci_loaded_files	= array();
	var $_ci_models			= array();
	var $_ci_helpers		= array();
	var $_ci_plugins		= array();
	var $_ci_varmap			= array('unit_test' => 'unit', 'user_agent' => 'agent');
	var $_ci_models_gears = array();
	
	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 *
	 * @return	void
	 */
	function CI_Loader()
	{	
		$this->cache =& load_class('Cache');
		$this->info =& load_class('Info');
		$this->mem = memory_get_usage(TRUE)/1024/1024;
		$this->bench =& load_class('Benchmark');
		$this->bench->point('Loader initialize.');
	}
	
	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */	
	function library($library = '', $params = NULL, $object_name = NULL)
	{
		if ($library == '')
		{
			return FALSE;
		}

		if ( ! is_null($params) AND ! is_array($params))
		{
			$params = NULL;
		}
		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->_ci_load_class($class, $params, $object_name);
			}
		}
		else
		{
			$this->_ci_load_class($library, $params, $object_name);
		}
		$CI =& get_instance();
		$this->_ci_assign_to_models();
	}
	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */	
	function model($model, $name = '',$silent = FALSE)
	{		

		if (is_array($model))
		{
			foreach($model as $babe)
			{
				$this->model($babe);	
			}
			return;
		}

		if ($model == '')
		{
			return;
		}

		$CI =& get_instance();
		
		$parts = preg_split('~[^\w_\/]+~', $model, 3, PREG_SPLIT_NO_EMPTY);
		if (empty($parts[0])) show_error("Couldn't load model by request '{$model}'.");
		$gear = count($parts) > 1 ? $parts[0] : $CI->name;

		$dir = count($parts) > 2 ? $parts[1] : 'models';
		$model = end($parts);

	
		if (!$name OR $name == '')
		{
			$name = strtolower($model);
		}
	    $this->_ci_models_gears[$name] = $gear;

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}
		
		if (isset($CI->$name))
		{
			if($silent) return FALSE;
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}
	
		$model = strtolower($model);
	      $model = ucfirst($model);
	      if(!class_exists('Model')){
		      load_class('Model',FALSE);
	      }
            $CI->$name = new $model;
		$this->_ci_models[] = $name;	
	}
	// --------------------------------------------------------------------	
	
	/**
	 * Database Loader
	 *
	 * @param	string	the DB credentials
	 * @param	bool	whether to return the DB object
	 * @param	bool	whether to enable active record (this allows us to override the config setting)
	 * @return	object
	 */	
	function database($params = '', $return = FALSE, $active_record = FALSE)
	{
		// Grab the super object
		$CI =& get_instance();
		
		// Do we even need to load the database class?
		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == FALSE AND isset($CI->db) AND is_object($CI->db))
		{
			return FALSE;
		}	
	
		require_once(BASEPATH.'database/DB'.EXT);
		if ($return === TRUE)
		{
			return DB($params, $active_record);
		 }
		
		// Initialize the db variable.  Needed to prevent   
		// reference errors with some configurations
		$CI->db = '';
		
		// Load the DB class
		$CI->db = DB($params, $active_record);	
		// Assign the DB object to any existing models
		$this->_ci_assign_to_models();
	}
	// --------------------------------------------------------------------

	/**
	 * Load the Utilities Class
	 *
	 * @return	string		
	 */		
	function dbutil()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}
		
		$CI =& get_instance();

		// for backwards compatibility, load dbforge so we can extend dbutils off it
		// this use is deprecated and strongly discouraged
		$CI->load->dbforge();
	
		require_once(BASEPATH.'database/DB_utility'.EXT);
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility'.EXT);
		$class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

		$CI->dbutil = new $class();

		$CI->load->_ci_assign_to_models();
	}
	// --------------------------------------------------------------------

	/**
	 * Load the Database Forge Class
	 *
	 * @return	string		
	 */		
	function dbforge()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}
		
		$CI =& get_instance();
	
		require_once(BASEPATH.'database/DB_forge'.EXT);
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge'.EXT);
		$class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

		$CI->dbforge = new $class();
		
		$CI->load->_ci_assign_to_models();
	}
	// --------------------------------------------------------------------	

	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function file($path, $return = FALSE)
	{
		return $this->_ci_load(array('_ci_path' => $path, '_ci_return' => $return));
	}
	// --------------------------------------------------------------------
	
	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @param	array
	 * @return	void
	 */
	function vars($vars = array(), $val = '')
	{
		if ($val != '' AND is_string($vars))
		{
			$vars = array($vars => $val);
		}
	
		$vars = $this->_ci_object_to_array($vars);
	
		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_ci_cached_vars[$key] = $val;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 *
	 * @param	mixed
	 * @return	void
	 */
	function helper($helpers = array())
	{
		if ( ! is_array($helpers))
		{
			$helpers = array($helpers);
		}
	
		foreach ($helpers as $helper)
		{		
			$helper = strtolower(str_replace(EXT, '', str_replace('_helper', '', $helper)).'_helper');

			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}
			
			$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;

			// Is this a helper extension request?			
			if (file_exists($ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.EXT;
				
				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}
				
				include_once($ext_helper);
				include_once($base_helper);
			}
			elseif (file_exists(APPPATH.'helpers/'.$helper.EXT))
			{ 
				include_once(APPPATH.'helpers/'.$helper.EXT);
			}
			else
			{		
				if (file_exists(BASEPATH.'helpers/'.$helper.EXT))
				{
					include_once(BASEPATH.'helpers/'.$helper.EXT);
				}
				else
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}
			}

			$this->_ci_helpers[$helper] = TRUE;
			log_message('debug', 'Helper loaded: '.$helper);	
		}		
	}
	// --------------------------------------------------------------------
	
	/**
	 * Load Helpers
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function helpers($helpers = array())
	{
		$this->helper($helpers);
	}
	// --------------------------------------------------------------------
	
	/**
	 * Load Plugin
	 *
	 * This function loads the specified plugin.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugin($plugins = array())
	{
		if ( ! is_array($plugins))
		{
			$plugins = array($plugins);
		}
	
		foreach ($plugins as $plugin)
		{	
			$plugin = strtolower(str_replace(EXT, '', str_replace('_pi', '', $plugin)).'_pi');		

			if (isset($this->_ci_plugins[$plugin]))
			{
				continue;
			}

			if (file_exists(APPPATH.'plugins/'.$plugin.EXT))
			{
				include_once(APPPATH.'plugins/'.$plugin.EXT);	
			}
			else
			{
				if (file_exists(BASEPATH.'plugins/'.$plugin.EXT))
				{
					include_once(BASEPATH.'plugins/'.$plugin.EXT);	
				}
				else
				{
					show_error('Unable to load the requested file: plugins/'.$plugin.EXT);
				}
			}
			
			$this->_ci_plugins[$plugin] = TRUE;
			log_message('debug', 'Plugin loaded: '.$plugin);
		}		
	}
	// --------------------------------------------------------------------
	
	/**
	 * Load Plugins
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugins($plugins = array())
	{
		$this->plugin($plugins);
	}
	// --------------------------------------------------------------------
	
	/**
	 * Loads a config file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{			
		$CI =& get_instance();
		$CI->config->load($file, $use_sections, $fail_gracefully);
	}
	// --------------------------------------------------------------------
	
	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_load($_ci_data)
	{
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
		{
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		// Set the path to the requested file
		if ($_ci_path == '')
		{
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;
			$_ci_path = $this->_ci_view_path.$_ci_file;
		}
		else
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}
		
		if ( ! file_exists($_ci_path))
		{
			show_error('Unable to load the requested file: '.$_ci_file);
		}
	
		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		// Only needed when running PHP 5
		
		if ($this->_ci_is_instance())
		{
			$_ci_CI =& get_instance();
			foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var)
			{
				if ( ! isset($this->$_ci_key))
				{
					$this->$_ci_key =& $_ci_CI->$_ci_key;
				}
			}
		}

		/**
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */	
		if (is_array($_ci_vars))
		{
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);
				
		/**
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();
				
		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
		
		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path); // include() vs include_once() allows for multiple views with the same name
		}
		
		log_message('debug', 'File loaded: '.$_ci_path);
		
		// Return the file data if requested
		if ($_ci_return === TRUE)
		{		
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/**
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */	
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			// PHP 4 requires that we use a global
			global $OUT;
			$OUT->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string	an optional object name
	 * @return	null
	 */
	function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{	
		// Dmitry Belyaev
		if(strpos($class,'_') === 0) $class = substr($class,1);
		if(strstr($class,'.class')){ 
			$class = str_replace('.class','',$class);
		}
		// =============================================

		// Is there an associated config file for this class?
		if ($config === NULL)
		{
			// We test for both uppercase and lowercase, for servers that
			// are case-sensitive with regard to file names
			if (file_exists(APPPATH.'config/'.strtolower($class).EXT))
			{
				include_once(APPPATH.'config/'.strtolower($class).EXT);
			}			
			else
			{
				if (file_exists(APPPATH.'config/'.ucfirst(strtolower($class)).EXT))
				{
					include_once(APPPATH.'config/'.ucfirst(strtolower($class)).EXT);
				}			
			}
		}
		if ($prefix == '')
		{			
			if (class_exists('CI_'.$class)) 
			{
				$name = 'CI_'.$class;
			}
			elseif (class_exists(config_item('subclass_prefix').$class)) 
			{
				$name = config_item('subclass_prefix').$class;
			}
			else
			{
				$name = $class;
			}
		}
		else
		{
			$name = $prefix.$class;
		}
		// Is the class name valid?
		if ( ! class_exists($name))
		{
			log_message('error', "Non-existent class: ".$name);
			show_error("Non-existent class: ".$class);
		}
		
		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);
		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}

		// Save the class name and object name		
		$this->_ci_classes[$class] = $classvar;
		
		// Instantiate the class		
		$CI =& get_instance();

		if ($config !== NULL)
		{
			return $CI->$classvar = new $name($config);
		}
		else
		{		
			return $CI->$classvar = new $name;
		}	
	} 	
	
	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function _ci_object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}
	// --------------------------------------------------------------------

	/**
	 * Determines whether we should use the CI instance or $this
	 *
	 * @access	private
	 * @return	bool
	 */
	function _ci_is_instance()
	{
		if ($this->_ci_is_php5 == TRUE)
		{
			return TRUE;
		}
	
		global $CI;
		return (is_object($CI)) ? TRUE : FALSE;
	}
	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_autoloader()
	{	
		include_once(APPPATH.'config/autoload'.EXT);

		if ( ! isset($autoload))
		{
			return FALSE;
		}
		
		// Load any custom config file
		if (count($autoload['config']) > 0)
		{			
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}		

		// Autoload plugins, helpers and languages
		foreach (array('helper', 'plugin', 'language') as $type)
		{			
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}		
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']))
		{
			$autoload['libraries'] = $autoload['core'];
		}
		
		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load scaffolding
			if (in_array('scaffolding', $autoload['libraries']))
			{
				$this->scaffolding();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
			}
		
			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}		
		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}
	// --------------------------------------------------------------------	

    /**
    * Preload function
    *
    * Get gears list and their config
    *
    * @return	void
    */
    function preload(){
	    if(!file_exists(GEARS.'/global/global.info') && file_exists(GEARS.'/global/global.info.default')){
		    file_put_contents(GEARS.'/global/global.info',file_get_contents(GEARS.'/global/global.info.default'));
		    $url = parse_url($_SERVER['SERVER_NAME']);
		    $docroot = $_SERVER['DOCUMENT_ROOT'];
		    $realdir = dirname($_SERVER['SCRIPT_FILENAME']);
		    if($docroot != $realdir){
			    defined('SUBDIR') OR define('SUBDIR',str_replace($docroot,'',$realdir));
			    $url['path'] .= SUBDIR;
		    }
		    $this->info->set(GEARS.'/global/global.info')->change('url',$url['path'])->compile();
	    }
		$this->gears['global'] = $this->info->read(GEARS.'/global/global.info');
		$this->cache->enabled = $this->gears['global']['cache'];
		//$this->gears['global']['url'] = trim($this->gears['global']['url'],'/');
		$this->gears['global']['icon'] = '/gears/global/img/gear.png';
		//Gears config autoload
		$this->gears_load();
		if(!$libraries = $this->cache->get('libraries')){
			$libraries = array_filter(glob(GEARS.'*/library/*'.EXT),array($this,'filter_libraries'));
		}
		foreach($libraries as $file){
			include_once($file);
		}
      }
	// --------------------------------------------------------------------	
	
	/**
	* Check path to load by gear state
	*
	* @param	string
	* @param	string
	* @return	boolean
	*/
	public function filter($path,$exclude = FALSE){
		if(basename(dirname($path)) == 'models') return TRUE;
		$gear = basename(dirname(dirname($path)));
		if($exclude && strpos($path,$exclude)) return FALSE;
		return isset($this->gears[$gear]);
	}
	
	/**
	* Check libraries
	*
	* @param	string
	* @return	boolean
	*/
	private function filter_libraries($path){
		return $this->filter($path,'.class');
	}
	
	/**
	* Prepare class name by path
	*
	* @param	string
	* @return	string
	*/
	public function class_name($path){
		return strtolower(str_replace(array('.class',EXT),'',basename($path)));
	}
	
	
	/**
	 * Gears load
	 *
	 * Gears info load.
	 * 
	 * @return	void
	 */
	function gears_load(){
	    if($gears = $this->cache->get('gears')){
		$config = $this->gears['global'];
		$this->gears = array_merge($this->gears,$gears);
		$this->gears['global']['cache'] = $config['cache'];
		$this->gears['global']['debug'] = $config['debug'];
		$this->autoload = $this->cache->get('autoload');
	    }
	    else {
		$files = glob(GEARS.'*/*.info');
		 foreach($files as $file){
		   $gear = basename(dirname($file));
		   $config = $this->info->read($file);
		   if(isset($config['group']) && $config['group'] == 'core' OR (isset($config['enabled']) && $config['enabled'])){
			   $this->gears[$gear] = $config;
		   }
		 }
		   // If gear required other gears - it won't work without them
		  foreach($this->gears as $gear=>&$config){
			  if(!empty($config['required'])){
				  $required = is_array($config['required']) ? $config['required'] : explode(',',str_replace(' ','',$config['required']));
				  foreach($required as $require){
					  if(preg_match('/(\w+)\s?([\d\.]+)/',$require,$matches)){
						  list($all,$req_gear,$req_version) = $matches;
						  if(!isset($this->gears[$req_gear]) OR
						      isset($this->gears[$req_gear]['version']) && 
						      version_compare($this->gears[$req_gear]['version'],$req_version,'<')
						  ){
							  unset($this->gears[$gear]);
							  continue;
						  }
					  }
					  else if(!isset($this->gears[$require])){
						  unset($this->gears[$gear]);
						  continue;
					  }
				  }
			  }
			  if(!empty($config['incomp'])){
				  $incomp = is_array($config['incomp']) ? $config['incomp'] : explode(',',str_replace(' ','',$config['incomp']));
				  foreach($incomp as $incomp_gear){
					  if(!empty($this->gears[$incomp_gear]['position']) && $this->gears[$incomp_gear]['position'] > $config['position']){
						  unset($this->gears[$incomp_gear]);
					  }
				  }
			  }
	  		  // if routes were set - get it
			  if(isset($config['routes'])){
			   foreach($config['routes'] as $key=>$route){
				   $route = explode("=",$route);
				   if(count($route)  == 2) {
					   $config['routes'][trim($route[0])] = trim($route[1]);
				   }
				   unset($config['routes'][$key]);   
			   }
			  }
			  // If node params exists - it will be parsed and transformed into route
			  if(isset($config['node'])){
				  $node = $config['node'];
				  if(isset($node['url'])){
					$url = str_replace('%id%','[\d]+',$node['url']);
					$url = preg_replace('/%(.*)%/i','.+',$url);
					$brackets = count(explode(')',$url));
					if(isset($node['suffix'])){
						$url .= str_replace('.','\.',$node['suffix']);
					}
					$url .= "$";
					$config['routes'][$url] = 'nodes/show/';			  
					for($i = 1; $i < $brackets; $i++){
						$config['routes'][$url] .= '$'.($brackets-$i).'/';
					}
					$config['routes'][$url] = rtrim($config['routes'][$url],'/');
				  }
			  }
			  // Gear icons
			  if(file_exists(GEARS.$gear.'/img/'.$gear.'.png')){
				  $config['icon'] = '/gears/'.$gear.'/img/'.$gear.'.png';
			  }
			  else {
				  $config['icon'] = '/gears/global/img/gear.png';
			  }
		  }
		 //Sorting gears via position config param
		 uasort($this->gears,array($this,'gears_sort'));
  		 $this->autoload['models'] = array_filter(glob(GEARS.'*/models/*'.EXT),array($this,'filter'));		   
		 $this->autoload['models'] = array_combine(array_map(array($this,'class_name'),$this->autoload['models']),$this->autoload['models']);
		 $this->autoload['classes'] = array_filter(glob(GEARS.'*/library/*.class'.EXT),array($this,'filter'));		   
		 $this->autoload['classes'] = array_combine(array_map(array($this,'class_name'),$this->autoload['classes']),$this->autoload['classes']);
		 $this->cache->set('autoload',$this->autoload);
		 $this->cache->set('gears',$this->gears);	
		}
	}

	/**
	* Gears models and classes autoload
	*
	* @return	void
	*/	
    function gears_autoload(){
      foreach($this->gears as $gear_name=>$gear){
		  // Hooks load	 
		  $path = GEARS.$gear_name.'/_hooks'.EXT;
		  if(file_exists($path)){
			$this->_ci_loaded_files[] = $path;
			require_once $path;
		  }
		  if(isset($gear['models']) && is_array($gear['models'])){
			  foreach($gear['models'] as $model){
				  $model = trim($model);
				  if(strpos($model,' ')){
				   list($model,$name) = explode(' ',$model);
				   $this->model($model,$name);
				  }
				  else $this->model($model);
			  }
		  }
	  }
	 }
	// --------------------------------------------------------------------	

	/**
	 * Gears sort
	 *
	 * @access	private
	 * @return	void
	 */
	 function gears_sort($a,$b){
		 if(!isset($a['position'])){
		  return 1;
		 }
		 if(!isset($b['position'])){
		  return -1;
		 }
		 
         return ((int)$a['position'] > (int)$b['position']) ? 1 : -1;
	 }
	// --------------------------------------------------------------------	


	/**
	 * Get gears
	 *
	 * Return Gears array.
	 * 
	 * @return	void
	 */
	 function get_gears($group = FALSE){
		 if(!$group){
			 return $this->gears;
		 }
		 else {
			 $gears = array();
			 foreach($this->gears as $name=>$gear){
				 if(isset($gear['group']) && $gear['group'] == $group){
					 $gears[$name] = $gear;
				 }
			 }
			 return count($gears) > 0 ? $gears : FALSE;
		 }
	 }
	// --------------------------------------------------------------------
	
	/**
	 * Get groups
	 *
	 * Return Groups array.
	 *
	 * @return	void
	 */	
	 function get_groups(){
		 $groups = array();
		 foreach($this->gears as $gear){
			 if(isset($gear['group']) && !in_array($gear['group'],$groups)){
				 $groups[] = $gear['group'];
			 }
		 }
		 asort($groups);
		 return count($groups) > 0 ? $groups : FALSE;
	 }	 
	 // --------------------------------------------------------------------	
	
	/**
	 * Get gear
	 *
	 * Return Gear as array. 
	 *
	 * @access	private
	 * @return	void
	 */
	 function get_gear($name = FALSE){
			 return isset($this->gears[$name]) ? $this->gears[$name] : FALSE;
	 }
	// --------------------------------------------------------------------	

	/**
	 * Assign to Models
	 *
	 * Makes sure that anything loaded by the loader class (libraries, plugins, etc.)
	 * will be available to models, if any exist.
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function _ci_assign_to_models()
	{
		if (count($this->_ci_models) == 0)
		{
			return;
		}
	
		$CI =& get_instance();
			foreach ($this->_ci_models as $model)
			{			
				$CI->$model->_assign_libraries();
			}
	}  	
	// --------------------------------------------------------------------

	
	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @access	private
	 * @param	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{	
		// Get the class name, and while we're at it trim any slashes.  
		// The directory path can be included as part of the class name, 
		// but we don't want a leading slash
		$class = str_replace(EXT, '', trim($class, '/'));
		$parts = preg_split('~[^\w_\/\.]+~', $class, 3, PREG_SPLIT_NO_EMPTY);
		if (empty($parts[0])) show_error("Couldn't load library by request '{$class}'.");
		$gear = count($parts) > 1 ? $parts[0] : FALSE;
		$dir = count($parts) > 2 ? $parts[1] : 'library';
		$class = end($parts);
		$subdir = FALSE;
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);	
			
			// Reset the $class variable now that we know the actual filename
			$class = end($x);
			
			// Kill the filename from the array
			unset($x[count($x)-1]);
			
			// Glue the path back together, sans filename
			$subdir = implode($x, '/').'/';
		}
			/*
	// if class name has ! on it's start - it will wake up from gear
		$gear = strpos($class,'!') == 0 ? trim(substr($class,1,strpos($class,' '))) : FALSE;
		if($gear) $class = substr($class,strpos($class,' ')+1);
		// Was the path included with the class name?
		// We look for a slash to determine this
		if(strpos($class,' ')){
			$tmp = explode(' ',$class);
			$dir = array_shift($tmp);
			$class = implode(' ',$tmp);
		}
		else $dir = 'library';
		

*/

		if($gear && !$subdir)	return $this->_ci_init_class($class, '', $params, $object_name);			
		
		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			if($gear) {
			 $class_path = GEARS.$gear.'/'.$dir.'/'.$subdir.$class.EXT;
			 if(file_exists($class_path)){
						if (in_array($class, $this->_ci_loaded_files)){
							// Before we deem this to be a duplicate request, let's see
							// if a custom object name is being supplied.  If so, we'll
							// return a new instance of the object
							if ( ! is_null($object_name))
							{
								$CI =& get_instance();
								if ( ! isset($CI->$object_name))
								{
									return $this->_ci_init_class($class, '', $params, $object_name);			
								}
							}
							
							$is_duplicate = TRUE;
							log_message('debug', $class." class already loaded. Second attempt ignored.");
							return;
						}
						if(!class_exists($object_name ? $object_name : $class)){
							include_once($class_path);
						}
						$this->_ci_loaded_files[] = dirname($class_path).'/'.strtolower(basename($class_path));
						return $this->_ci_init_class($class, '', $params, $object_name);			
				}
			}
			else {
			$subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.EXT;
				// Is this a class extension request?			
				if (file_exists($subclass))
				{
					$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;
					
					if ( ! file_exists($baseclass))
					{
						log_message('error', "Unable to load the requested class: ".$class);
						show_error("Unable to load the requested class: ".$class);
					}
	
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($subclass, $this->_ci_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& get_instance();
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);			
							}
						}
						
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
					$this->_ci_loaded_files[] = $baseclass;
					$this->_ci_loaded_files[] = $subclass;
					if(!class_exists($object_name ? $object_name : config_item('subclass_prefix').$class)){
						include_once($baseclass);				
						include_once($subclass);
					}
					return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);			
				}

				// Lets search for the requested library file and load it.
				$is_duplicate = FALSE;		
				for ($i = 1; $i < 3; $i++)
				{
					$path = ($i % 2) ? APPPATH : BASEPATH;	
					$filepath = $path.'libraries/'.$subdir.$class.EXT;
					
					// Does the file exist?  No?  Bummer...
					if ( ! file_exists($filepath))
					{
						continue;
					}
					
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($filepath, $this->_ci_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& get_instance();
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, '', $params, $object_name);
							}
						}
					
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
					$this->_ci_loaded_files[] = $filepath;
					include_once($filepath);
					return $this->_ci_init_class($class, '', $params, $object_name);
				}
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}
		
		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}
}

/* End of file Loader.php */
/* Location: ./system/libraries/Loader.php */