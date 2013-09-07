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
 * URI class
 *
 * Extends CodeIgniter basic URI class to be able subdomain catching
 *
 * @package		CoGear
 * @subpackage	URI
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class CG_URI extends CI_URI {

	var $url = FALSE;
	var $subdir = FALSE;
	var $subdomain = FALSE;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */		
	function CG_URI()
	{
		parent::CI_URI();
		$this->loader =& load_class('Loader');
		$this->init();
	}
	
	/**
	* Initialize URI
	*
	* @return	void
	*/
	function init(){
		// If engine is in subdir -- check for it
		$this->url = str_replace(array('http://','www.'),'',$this->loader->gears['global']['url']);
		$data = parse_url('http://'.$this->url);
		$this->url = $data['host'];
		if($this->subdir = isset($data['path']) ? implode('/',preg_split('#/#',$data['path'],3,PREG_SPLIT_NO_EMPTY)) : FALSE){
			defined('SUBDIR') OR define('SUBDIR','/'.$this->subdir);
		}
		if(filter_var($this->url,FILTER_VALIDATE_IP)){
			return;
		}
		// Explode it into pieces
		$site_pieces = explode('.',$this->url);
		// If site is on 3rd level subdomain -- return
		if(count($site_pieces) > 2 OR $this->subdir){
			 $this->loader->global['subdomains'] = FALSE;
			 return FALSE; 
		 }
		// Explode real server name into pieces
	    $domain_pieces = explode(".",$_SERVER['SERVER_NAME']);
	    // If there is a 3rd level subdomain -- manage it
        if(count($domain_pieces) > 2){
            $subdomain = $domain_pieces[count($domain_pieces)-3];
            $this->subdomain =  $subdomain == 'www' ? FALSE : $subdomain;
            if($this->subdomain && !in_array('ajax',$this->segments)) $this->segments[] = $this->subdomain;
        }	 
	}
	
	/**
	 * Filter segments for malicious characters
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _filter_uri($str)
	{

		if ($str != '' && $this->config->item('permitted_uri_chars') != '' && $this->config->item('enable_query_strings') == FALSE)
		{
			if ( ! preg_match("|^[".preg_quote($this->config->item('permitted_uri_chars'))."]+$|iu", $str))
			{
				header('HTTP/1.1 400 Bad Request');
				show_error('The URI you submitted has disallowed characters.');
			}
		}


		// Convert programatic characters to entities
		$bad	= array('$', 		'(', 		')',	 	'%28', 		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');
		return str_replace($bad, $good, $str);
	}
	
	
	/**
	 * Get the URI String
	 *
	 * @access	private
	 * @return	string
	 */
	function _fetch_uri_string()
	{
		if (strtoupper($this->config->item('uri_protocol')) == 'AUTO')
		{
			// If the URL has a question mark then it's simplest to just
			// build the URI string from the zero index of the $_GET array.
			// This avoids having to deal with $_SERVER variables, which
			// can be unreliable in some environments

/*
			if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
			{
				$this->uri_string = key($_GET);
				return;
			}
*/


			// Is there a PATH_INFO variable?
			// Note: some servers seem to have trouble with getenv() so we'll test it two ways
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				$this->uri_string = $path;
				return;
			}

			// No PATH_INFO?... What about QUERY_STRING?
			$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
			if (trim($path, '/') != '')
			{
				$this->uri_string = $path;
				return;
			}

			// No QUERY_STRING?... Maybe the ORIG_PATH_INFO variable exists?
			$path = str_replace($_SERVER['SCRIPT_NAME'], '', (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO'));
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				// remove path and script information so we have good URI data
				$this->uri_string = $path;
				return;
			}

			// We've exhausted all our options...
			$this->uri_string = '';
		}
		else
		{
			$uri = strtoupper($this->config->item('uri_protocol'));

			if ($uri == 'REQUEST_URI')
			{
				$this->uri_string = $this->_parse_request_uri();
				return;
			}

			$this->uri_string = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
		}

		// If the URI contains only a slash we'll kill it
		if ($this->uri_string == '/')
		{
			$this->uri_string = '';
		}
	}
}
