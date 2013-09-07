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
 * i18n model
 *
 * @package		CoGear
 * @subpackage	i18n
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class i18n extends Model{
	private $lang = 'ru';
	public $context = array();
	public $section = 'global';

	/**
	* Constructor
	*
	* @return	void
	*/
	function i18n(){
		parent::Model();
		$CI =& get_instance();
		$this->initialize();
	}
	// ------------------------------------------------------------------------

	/**
	* Initialization
	*
	* @param
	* @return	void
	*/
	function initialize(){
		$CI =& get_instance();
		if($CI->site->lang) $this->lang = $CI->site->lang;
		setlocale(LC_TIME, isset($CI->site->locale) ? $CI->site->locale: "ru_RU.UTF-8");
		// Now let's get context
		$mod = $this->cache->get('languages/last_mod',TRUE);
		$recompile = FALSE;
		if(!$CI->site->cache){
			$files = glob(GEARS.'*/lang/'.$this->lang.'.lng');
			$last_mod = 0;
			foreach($files as $file){
				$mtime = filemtime($file);
				if($mtime > $last_mod) $last_mod = $mtime;
			}
			if($last_mod > $mod){
				$recompile = TRUE;
				$this->cache->set('languages/last_mod',$last_mod);
			}
		}
		if($recompile OR !$this->context = $this->cache->get('languages/'.$this->lang,TRUE)){
			$files = glob(GEARS.'*/lang/'.$this->lang.'.lng');
			foreach($files as $file){
				$this->context = is_array($this->context) ? array_merge_recursive($this->context,$CI->info->read($file)) : $CI->info->read($file);
			}
			$this->cache->set('languages/'.$this->lang,$this->context);
		}
		$js = ROOTPATH.'/uploads/i18n/'.$CI->site->lang.'.js';
		if(!file_exists(dirname($js))) mkdir(dirname($js),0777,TRUE);
		//$js_lang = $this->throw_unused_lang($this->context);
		$js_lang = 'lang = '.json_encode($this->context);
		if(!file_exists($js) OR $recompile OR filesize($js)!=strlen($js_lang)) file_put_contents($js, $js_lang);
		$CI->assets->add($js);
		$this->context = new ArrayObject($this->context);
	}
	// ------------------------------------------------------------------------

	/**
	* Keep i JS language file just strings we really use!
	*
	*/
	function throw_unused_lang($language)
	{
		$CI =& get_instance();
		$load_anyway = array('form');
		$global_template    = $CI->site->template;
		$dir                = $this->assets->dir;
		$used_strings       = array();
		$js_files = glob($dir.'*.js');
		// get compiled JS-files
		if (is_array($js_files)) foreach ($js_files AS $js_file)
		{
			// get currently used lang-strings
			$js_file = file_get_contents($js_file);
			$lang_str_end = strpos($js_file, '}}')+2;
			$js_file = substr($js_file, $lang_str_end, strlen($js_file));
			preg_match_all('/lang\.([a-zA-Z_\-\.]+)/ism', $js_file, $matches);
			$matches = $matches[1];
			foreach ($matches AS $match)
			{
				$match = explode('.', $match);
				if (count($match) != 2) continue;
				$used_strings[$match[0]][] = $match[1];
			}
		}
		$ready_lang = array();
		// will keep only variables we use
		foreach ($used_strings AS $gear_name=>$gear_lang_array)
		{
			foreach ($gear_lang_array AS $lang_str_name)
			{

				if (isset($language[$gear_name][$lang_str_name])) $ready_lang[$gear_name][$lang_str_name] = $language[$gear_name][$lang_str_name];
			}
		}
		foreach ($load_anyway AS $gear_name)  $ready_lang[$gear_name] = $language[$gear_name];
		return $ready_lang;
	}

	// ------------------------------------------------------------------------

	/**
	* Simple remove language cache.
	*
	*/
	function clear(){
		$this->cache->clear('languages/'.$this->lang);
	}

	/**
	* Translate text
	*
	* @param	string
	* @param	array
	* @return	string
	*/
	function translate($name,$args = FALSE){
	  // if name is array - first element will be section, second - text
	  if(is_array($name)){
		list($section,$name) = $name;
	  }
	  if($name[0] == '%'){
		  $name = substr($name,1);
		  $section = 'global';
	  }
	  $pieces = preg_split('#[\s><.!]#',$name,2,PREG_SPLIT_NO_EMPTY);
	  if(count($pieces) == 2) list($section,$name) = $pieces;
	  else $name = reset($pieces);
	  // If no section is set - use model current section
	  if(!isset($section)){
		  $section = $this->section;
	  }
	  else $section = str_replace('!','',$section);
	  if(strpos($name,'@') !== FALSE){
		  if($args = explode('@',$name)){
			  $name = array_shift($args);
		  }
	  }
	  // If text is set in current context
	  if(isset($this->context[$section]) && isset($this->context[$section][$name])){
		  // If there are args
		  if(is_array($args) && count($args) > 0 && $args[0] !== FALSE){
		   // Find all (one|some|many)	for creating correct plural forms
		   preg_match_all('#\((.+)\)#imU',$this->context[$section][$name],$matches);
		   $value = $this->context[$section][$name];
		   if(count($matches[0]) > 0) {
			   foreach($matches[0] as $key=>$val){
				   if(count(explode('|',$matches[1][$key])) == 3) $value = str_replace($val,declOfNum($args[$key],$matches[1][$key]),$value);
			   }
		   }
		   // making args for sprintf
		   $args = array_merge(array(	$value),$args);
		   // Process text with sprintf
		   return call_user_func_array('sprintf',$args);
		  }
		  // Simple return translated text
		  else {
		   return $this->context[$section][$name];
		  }
	  }
	  else {
		  if(is_array($args) && count($args) > 0){
		   $args = array_merge(array($name),$args);
		   return call_user_func_array('sprintf',$args);
		  }
		  else {
		   return $name;
		  }
	  }
	 }
	// ------------------------------------------------------------------------


	/**
	* Set language
	*
	* @param	string
	* @return	object
	*/
	 function lang($lang){
		 $this->lang = $lang;
		 return $this;
	 }
	// ------------------------------------------------------------------------


	/**
	* Set section
	*
	* @param	string
	* @return	object
	*/
	function section($name){
		$this->section = $name;
		return $this;
	}
	// ------------------------------------------------------------------------


	/**
	* Export data
	*
	* @param	array
	* @return	void
	*/
	 function export($data = array()){
		$CI =& get_instance();
		foreach($data as &$info){
			array4key($info,'name','value');
		}
		return $CI->info->write($data,TRUE);
	 }
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------