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
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author			ExpressionEngine Dev Team
 * @category		Libraries
 * @link				http://codeigniter.com/user_guide/general/routing.html
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
 * Router class
 *
 * Extending CI_Router class for further functionality.
 *
 * @package		CoGear
 * @subpackage	Router
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */

// ------------------------------------------------------------------------
class CG_Router extends CI_Router {
   /**
	* Constructor
	*/
	function CG_Router()
	{
		parent::CI_Router();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_routing()
	{
		// Are query strings enabled in the config file?
		// If so, we're done since segment based URIs are not used with query strings.
/*
		if ($this->config->item('enable_query_strings') === TRUE AND isset($_GET[$this->config->item('controller_trigger')]))
		{
			$this->set_class(trim($this->uri->_filter_uri($_GET[$this->config->item('controller_trigger')])));

			if (isset($_GET[$this->config->item('function_trigger')]))
			{
				$this->set_method(trim($this->uri->_filter_uri($_GET[$this->config->item('function_trigger')])));
			}
			
			return;
		}
*/
		// Load the routes.php file.
		@include(APPPATH.'config/routes'.EXT);
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);
		
		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);	
		
		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();
		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
/*
|===============================================================
| CoGear - based on CodeIgniter (by CodeMotion, http://codemotion.ru)
|---------------------------------------------------------------
| http://cogear.ru
|---------------------------------------------------------------
| Copyright (c) 2009 CodeMotion, Dmitriy Belyaev
|---------------------------------------------------------------
| Following changes are made by Dmitriy Belyaev
|---------------------------------------------------------------
| Purpose:
| Increase method functionality to work best with subdomains
|===============================================================
*/		
	//if ($this->uri->uri_string == '')
		if ($this->uri->uri_string == '' && !$this->uri->subdomain)
		{
			if ($this->default_controller === FALSE)
			{
				show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
			}

// Comment default code
/*
		if (strpos($this->default_controller, '/') !== FALSE)
			{
				$x = explode('/', $this->default_controller);

				$this->set_class(end($x));
				$this->set_method('index');
				$this->_set_request($x);
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
				$this->_set_request(array($this->default_controller, 'index'));
			}
*/

// Write our own
		    // Turn the default route into an array.  We explode it in the event that
			// the controller is located in a subfolder
			// Set the class and method
			$this->set_class($this->default_controller);
			$this->set_method('index');
			$this->_parse_routes();		
/*
|===============================================================
| CoGear - changes end
|===============================================================
*/				

			
			// re-index the routed segments array so it starts with 1 rather than 0
			$this->uri->_reindex_segments();
			
			log_message('debug', "No URI present. Default controller set.");
			return;
		}
		unset($this->routes['default_controller']);
		
		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();
		
		// Compile the segments into an array
		$this->uri->_explode_segments();
		
		// Parse any custom routing that may exist
		$this->_parse_routes();		
		
		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
	}

	// --------------------------------------------------------------------

/*
|===============================================================
| CoGear - based on CodeIgniter (by CodeMotion, http://codemotion.ru)
|---------------------------------------------------------------
| http://cogear.ru
|---------------------------------------------------------------
| Copyright (c) 2009 CodeMotion, Dmitriy Belyaev
|---------------------------------------------------------------
| The following code is protected under copyright law
|===============================================================
| Load debug functionality
|===============================================================
| CoGear - code start
|===============================================================
*/
	/**
	 * Validate request. Find controllers in gears folders and subfolders. Make all other params as method call args.
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */	
	function _validate_request($segments)
	{
	 if(count($segments) > 0){
			// Look for directory
            if (file_exists('./gears/'.$this->fetch_directory().$segments[0].'/'))
            {
                // Recursive search for directory until catch the file
                $this->set_directory($this->fetch_directory().$segments[0]);
                $segments = array_slice($segments, 1);
                return $this->_validate_request($segments);
            }
            // If there a segment name controller file
            elseif(file_exists('./gears/'.$this->fetch_directory().$segments[0].EXT)){
                return $segments;
            }
            // If there default controller in current dir
            elseif(file_exists('./gears/'.$this->fetch_directory().$this->default_controller.EXT)){
        	    $segments = array_merge(array($this->default_controller),$segments);
                return $segments;
            }
        }
        // If there is no segments controller will be default
        else{
            // $this->fetch_directory() = '' but in some case it may do some good
            if(file_exists('./gears/'.$this->fetch_directory().$this->default_controller.EXT)){
                $segments[0] = $this->default_controller;
                return $segments;
            }
        }
        // If there is no gear, may it be errors gear
        $this->set_directory('errors');
        return array($this->default_controller);
    }
	// --------------------------------------------------------------------

/*
|===============================================================
| CoGear - code end
|===============================================================
*/

	/**
	 * Set the Route
	 *
	 * This function takes an array of URI segments as
	 * input, and sets the current class/method
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function _set_request($segments = array())
	{
		$segments = $this->_validate_request($segments);
		
		if (count($segments) == 0)
		{
			return;
		}
						
		$this->set_class($segments[0]);
		
		if (isset($segments[1]))
		{
			// A scaffolding request. No funny business with the URL
			if ($this->routes['scaffolding_trigger'] == $segments[1] AND $segments[1] != '_ci_scaffolding')
			{
				$this->scaffolding_request = TRUE;
				unset($this->routes['scaffolding_trigger']);
			}
			else
			{
				// A standard method request
				$this->set_method($segments[1]);
			}
		}
		else
		{
			// This lets the "routed" segment array identify that the default
			// index method is being used.
			$segments[1] = 'index';
		}
		
		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->uri->segments
		$this->uri->rsegments = $segments;
	}
	// --------------------------------------------------------------------

/*
|===============================================================
| CoGear - based on CodeIgniter (by CodeMotion, http://codemotion.ru)
|---------------------------------------------------------------
| http://cogear.ru
|---------------------------------------------------------------
| Copyright (c) 2009 CodeMotion, Dmitriy Belyaev
|---------------------------------------------------------------
| Following changes are made by Dmitriy Belyaev
|---------------------------------------------------------------
| Purpose:
| Original CodeIgniter Router class method was extended to read routes from gears config files and work with subdomains
|===============================================================
*/

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @access	private
	 * @return	void
	 */
	function _parse_routes()
	{
		// Get Loader by link
		$this->loader =& load_class('Loader');
		// Subdomains - array for busy subdomains
		$subdomains = array();
		// If there are gears
		if(count($this->loader->gears) > 0){
		 // Seek through them
		 foreach($this->loader->gears as $name=>$gear){
		  // If there any gear routes - merge them with current routes	 
		  if(isset($gear['routes'])){
			$this->routes = array_merge($this->routes,$gear['routes']);		   
		  } 
		  // If there subdomain param
		  if(isset($gear['subdomain'])){
			    // If it's bool && TRUE - reserve gear name as subdomain
				if(is_bool($gear['subdomain']) && $gear['subdomain']) array_push($subdomains,$name);
				// If it's array - merge array values as reserved subdomains
				else if(is_array($gear['subdomain'])) $subdomains = array_merge($subdomains,$gear['subdomain']);
		  }
		 }
		 $this->subdomains = isset($subdomains) && count($subdomains) > 0 ? $subdomains : array();
		}
/*
		This comments and code are to old. I've sold the problem with cross-subdomain ajax via simple "ajax/(.*) = $1" rewrite rule.
		// If you are planing to use subdomains, don't forget that
		// YOU SHOULD NOT NAME your Controller functions as any of gears, because it will interrup next code
		// that allows to operate ajax request (that is cross-domain denied ) on any subdomain
		//if($this->uri->subdomain && isset($this->uri->segments[1]) && in_array($this->uri->segments[1],array_keys($this->loader->gears))){
			//array_shift($this->uri->segments);
		//}
*/
		// Do we even have any custom routing to deal with?
		// There is a default scaffolding trigger, so we'll look just for 1

		if (count($this->routes) == 1)
		{
			$this->_set_request($this->uri->segments);
			return;
		}

		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);
		// Loop through the route array looking for wild-cards
		//
		foreach ($this->routes as $key => $val)
		{						
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			$key = str_replace(':empty','\s?',$key);
			if($key[0] == '%' && $this->uri->subdomain && !in_array($this->uri->subdomain,$subdomains)){
				//$key = str_replace('%','([\w]+)',$key);
				if(!in_array('ajax',$this->uri->segments)) array_unshift($this->uri->segments,$val);
				
				$uri = $val.'/'.$uri;
				$key = "(.*)";
				$val = $uri;
				continue;
			}
			// Does the RegEx match?
			if(strpos($key,'^') === FALSE && strpos($key,'$') === FALSE){
				$start = '^';
				$end = '$';
			}
			else {
				$start = $end = '';
			}
			if (preg_match('#'.$start.$key.$end.'#', $uri, $matches))
			{		
				//if($start == '' && strpos($key,'^') === FALSE) $start = '.*';
				if($end == '' && !strpos($key,'$')) $end = '.*$';
				$val = preg_replace('#'.$start.$key.$end.'#', $val, $uri);
				$this->_set_request(explode('/', $val));		
				return;
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_set_request($this->uri->segments);
		$this->uri->_reindex_segments();
	}

/*
|===============================================================
| CoGear - changes end
|===============================================================
*/
}
	// --------------------------------------------------------------------
	// END Router Class

/* End of file Router.php */