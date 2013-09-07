<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author		CodeMotion, Dmitriy Belyaev
 * @copyright	Copyright (c) 2009, CodeMotion
 * @license		http://cogear.ru/license.html
 * @link		http://cogear.ru
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Common functions
 *
 * @package		CoGear
 * @subpackage	Global
 * @category	Gears libraries
 * @author		CodeMotion, Dmitriy Belyaev
 * @link		http://cogear.ru/user_guide/
 */
	// ------------------------------------------------------------------------

	/**
	* Set message
	*
	* @param string
	* @param string
	* @return void
	*/
	function msg($text,$success = '!global success'){
	 $CI =& get_instance();
	 if(is_bool($success)){
		 $success = $success ? '!global success' : '!global failure';
	 }
	 $CI->msg->set(t($text), t($success));
	}
	// ------------------------------------------------------------------------

	/**
	* Set title
	*
	* @param string
	* @param boolean
	* @param boolean
	* @return void
	*/
	function title($text = FALSE, $remove_last = FALSE,$delayed = FALSE){
	 $CI =& get_instance();
	 if(isset($CI->content['meta']) && in_array($text,$CI->content['meta']['title'])) return;
	 if(!isset($CI->content['meta'])) $delayed = TRUE;
	 if(!$delayed){
		 if($remove_last){
		  array_pop($CI->content['meta']['title']);
		 }
		 if($text){
		  $CI->content['meta']['title'][] = $text;
		 }
	 }
	 else {
		$CI->title_delayed[] = $text;		 
		$CI->title_delayed_remove_last = $remove_last;
	 }
	}
	// ------------------------------------------------------------------------

	
	/**
	* Transliterate russian chars to be url-compatible
	*
	* @param string
	* @param string
	* @return string
	*/
	function url_name($str, $separator = '-')
	{
		if(!$str) return false;
		$NpjLettersFrom = explode(",","а,б,в,г,д,е,з,и,к,л,м,н,о,п,р,с,т,у,ф,ц,ы");
	
		$NpjLettersTo   = explode(",","a,b,v,g,d,e,z,i,k,l,m,n,o,p,r,s,t,u,f,c,y");
		$NpjBiLetters = array(
		"й" => "jj", "ё" => "jo", "ж" => "zh", "х" => "kh", "ч" => "ch",
		"ш" => "sh", "щ" => "shh", "э" => "je", "ю" => "ju", "я" => "ja",
		"ъ" => "", "ь" => "",
		);
		$NpjCaps  = explode(",","А,Б,В,Г,Д,Е,Ё,Ж,З,И,Й,К,Л,М,Н,О,П,Р,С,Т,У,Ф,Х,Ц,Ч,Ш,Щ,Ь,Ъ,Ы,Э,Ю,Я");
		$NpjSmall = explode(",","а,б,в,г,д,е,ё,ж,з,и,й,к,л,м,н,о,п,р,с,т,у,ф,х,ц,ч,ш,щ,ь,ъ,ы,э,ю,я");
		$str = preg_replace( "/\s+/ms", $separator, $str );
		$str = str_replace($NpjCaps,$NpjSmall,$str);
		$str = str_replace($NpjLettersFrom,$NpjLettersTo,$str);
		$str = strtr( $str, $NpjBiLetters );
	
		$str = preg_replace("/[^a-z0-9\_\-.]+/mi", "", $str);
		$str=preg_replace('#[\-]+#i', $separator, $str);
		$str = strtolower ( $str );
	
		if (strlen($str) > 40) {
	
			$str = substr ($str, 0, 40);
	
			if (($temp_max = strrpos($str, '-')))  $str = substr ($str, 0, $temp_max);
	
		}
	
		return $str;
	
	}
	// ------------------------------------------------------------------------

	/**
	* Link to mkdir_if_not_extists
	*
	* @param
	* @param
	* @param
	* @return void
	*/
	function _mkdir(){
		$args = func_get_args();
		return  call_user_func_array('mkdir_if_not_exists',$args);
	}
	// ------------------------------------------------------------------------

	/**
	* Simple wrapper for mkdir with recursive flag
	*
	* @param
	* @param
	* @param
	* @return void
	*/
	function mkdir_if_not_exists($path,$mode = 0777){
		@mkdir($path,$mode,TRUE);
		return $path;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Send header of js content-type to browser and die. It will help Safari, if not UTF-8 encoding is used into json data
	*
	* @param string
	* @return void
	*/
	function dub($text)
	{
		header("Content-type: x-application/javascript");
		exit($text);
	}
	// ------------------------------------------------------------------------

	/**
	* Transform array into object recursively
	*
	* @param array
	* @return object
	*/
	function array2object($array) {
		if (is_array($array)) {
			$obj = new emptyClass();
			 
			foreach ($array as $key => $val){
				if(is_array($val)){
					$obj->$key = array2object($val);
				}
				else {
					$obj->$key = $val;
				}
			}
		}
		else { $obj = $array; }
	 
		return $obj;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Transform object into array recursively
	*
	* @param object
	* @return array
	*/
	function object2array($object) {
		if (is_object($object)) {
			$array = array();
			 
			foreach ($object as $key => $val){
				if(is_object($val)){
					$array[$key] = object2array($val);
				}
				else {
					$array[$key] = $val;
				}
			}
		}
		else { $array = $object; }
	 
		return $array;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Create icon path from image path
	*
	* @param string
	* @param string
	* @return string
	*/
	function make_icon($path,$size){
		$dir = dirname($path);
		$file = basename($path);
		return $dir.'/'.$size.'/'.$file;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Create icons paths from image path
	*
	* @param string
	* @param array
	* @param boolean
	* @return array
	*/
	function make_icons($path,$sizes = array(),$no_default = FALSE){
		$CI =& get_instance();
		$sizes = (array)$sizes;
		if($path && !is_string($path)){
			return $path;
		}
		if(count($sizes) < 1 OR !$sizes){
		  $sizes = $CI->gears->user->avatar->size;
		} 
		if((trim($path) == '' OR !$path)){
		  // OR !file_exists('.'.$path)
		  if($no_default === TRUE) return FALSE;
		  $path = is_string($no_default) ? $no_default : (isset($CI->gear->image->default) ? $CI->gear->image->default : $CI->gears->user->avatar->default);
		}
		$out = array();
		foreach($sizes as $size){
		  $out[$size] = make_icon($path,$size);
		}
		$out['original'] = $path;
		return $out;
	}
	// ------------------------------------------------------------------------

	
	
	/**
	* Create icons paths for array elements
	*
	* @param array
	* @param string
	* @return array
	*/
	function make_icons_from_array($array = array(),$field = 'icon', $default = TRUE){
		foreach($array as $key=>&$element){
			if(isset($element[$field])) {
				$element[$field] = make_icons($element[$field],FALSE,!$default);
			}
		}
		return $array;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Output ajax response
	*
	* If $arg[0] (success) is boolean - output will be json array like {success:$arg[0],msg:$arg[1]}
	* If $arg[1] is boolean - output will be plain $arg[0]
	*
	* @param mixed
	* @param mixed
	* @return void
	*/
	function ajax(){
	  $CI =& get_instance();
	  // You can set ajax traps - to stop terrorize poor ajax functions
	  if(isset($CI->site->ajax->trap)){
		  if($CI->session->get('ajax_wait') && (time() - $CI->session->get('ajax_wait') ) < $CI->site->ajax->wait)
		  {
			  $time_to_wait = ($CI->site->ajax->wait - (time() - $CI->session->get('ajax_wait')));
			  echo t('!ajax should_wait@'.$time_to_wait.'@'.declOfNum($time_to_wait,'!date second'));
			  exit();
		  }
		  else {
			  if($CI->session->get('ajax_wait')){
				  $CI->session->remove('ajax_wait');
				  $CI->session->remove('ajax_trap');
			  }
			  if($CI->session->get('ajax_trap') == $CI->site->ajax->trap){
					  $CI->session->set('ajax_wait',time());
			  }
			  else $CI->session->set('ajax_trap', $CI->session->get('ajax_trap') ? $CI->session->get('ajax_trap') + 1 : 1);
		  }
	  }
	  $args = func_get_args();
	  if(isset($args[0]) && is_array($args[0])){
		  $data = $args[0];
	  }
	  else {
		  $data['success'] = isset($args[0]) ? $args[0] : TRUE;
		  if(count($args) > 1) $data['msg'] = $args[1];
		  if(count($args) > 2) $data = array_merge($data,$args[2]);
	  }
	  if(isset($args[1]) && gettype($args[1]) == 'boolean' && $args[1]){
		  //header('Content-Type: text/plain');
		  die($args[0]);
	  }
	  else {
		  //header('Content-Type: application/json');
	      die(json_encode($data));
	  }
	}
	// ------------------------------------------------------------------------

	
	
	/**
	*  Create link
	*
	* @param string
	* @param string
	* @param boolean
	* @param boolean
	* @return string
	*/
	function l($link = FALSE,$suffix = '/',$force_subdomain = FALSE, $no_subdomain = FALSE){
		//$suffix = !$suffix ? '/' : $suffix;
		$CI =& get_instance();
		$stop =FALSE;
		if($link == '/user/login/'){
			$stop = TRUE;
		}
		preg_match("#(http|ftp|https)://(.[^/]*)#",$link,$matches);
		$site_url = trim($CI->site->url,'/ ');
		if($matches && $matches[2] != $site_url){
			return $link;	
		}
		if(trim($link) == ''){
			$link = $CI->uri->uri_string;
		}
		if(!$link) {
			return 'http://'.$CI->uri->url;
		}
		 if($CI->site->subdomains && (isset($link[0]) && $link[0] == '/' OR $force_subdomain) && !$no_subdomain){
			$pieces = explode('/',trim($link,' /'));
			$subdomain = array_shift($pieces);
			if($subdomain){
				if($force_subdomain){
					$url = implode('/',$pieces);
					return 'http://'.$subdomain.'.'.$site_url.'/'.$url.($url ? $suffix : '');
				}
				if(isset($CI->routes['%']) && $CI->routes['%'] == $subdomain && count($pieces) > 0) {
					$subdomain = array_shift($pieces);
					$url = implode('/',$pieces);
					return 'http://'.$subdomain.'.'.$site_url.'/'.$url.($url ? $suffix : '');
				}
				else if((!isset($CI->routes['%']) OR in_array($subdomain,$CI->subdomains)) && $CI->gears->$subdomain && $CI->gears->$subdomain->subdomains !== FALSE){
					$url = implode('/',$pieces);
					return 'http://'.$subdomain.'.'.$site_url.'/'.$url.($url ? $suffix : '');
				}
			}
		}
		if($link[0] == '/' OR $suffix){
			 return strpos($link,$site_url) ? $link : 'http://'.$site_url.'/'.trim($link,' /').($link != '/' ? $suffix : '');
		}
		elseif(strpos($link,$site_url) == FALSE) {
			return rtrim($CI->uri->uri_string,'/').'/'.rtrim($link,'/').'/';
		}
		else return $link;	
	}
	// ------------------------------------------------------------------------

	
	/**
	* Format date corresponding to site settings
	*
	* @param string
	* @return string
	*/
	function df($date,$format = FALSE){
		$CI =& get_instance();
		$date =  strftime($format ? $format : ($CI->site->date_format ? $CI->site->date_format : 'H:i Y-m-d'),strtotime($date));
		$replace = array(
		'Январь' => 'января',
		'Февраль' => 'февраля',
		'Март' => 'марта',
		'Апрель' => 'апреля',
		'Май' => 'мая',
		'Июнь' => 'июня',
		'Июль' => 'июля',
		'Август' => 'августа',
		'Сентябрь' => 'сентября',
		'Октябрь' => 'октября',
		'Ноябрь' => 'ноября',
		'Декабрь' => 'декабря'
		);
		$date = str_replace(array_keys($replace),array_values($replace),$date);
		return $date;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Set some array values as array keys.
	* After it can set array = one of it's values.
	*
	* @param array
	* @param string
	* @param boolean
	* @return void
	*/
	function array4key($array,$id,$val = FALSE){
		$tmp = array();
		foreach($array as $key=>$value){
		    $k = $id ? $value[$id] : $key;
			if($val === TRUE){
				$tmp[$k][] = $value;
			}
			else {
				if($val && isset($value[$val])){
					$tmp[$k] = $value[$val];
					 
				}
				elseif(!$val) {
					$tmp[$k] = $value;
				}
			}
		}
		return $array = $tmp;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Set some object values as object keys.
	* After it can set object = one of it's values.
	*
	* @param object
	* @param string
	* @param boolean
	* @return object
	*/
	function object4key($object,$id,$val = FALSE){
		$tmp = new emptyClass();
		foreach($object as $key=>$value){
			$name = $id ? $value->$id : $key;
			if($val === TRUE){
				$tmp->$name = $value;
			}
			else {
				$tmp->$name = $val ? $value->$val : $value;
			}
		}
		return $object = $tmp;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Set first chat uppercase in unicode string
	*
	* @param string
	* @return string
	*/
	function mb_ucfirst($string) {
		$string = mb_ereg_replace("^[\ ]+","", $string);
		$string = mb_strtoupper(mb_substr($string, 0, 1, "UTF-8"), "UTF-8").mb_substr($string, 1, mb_strlen($string), "UTF-8" );
		return $string;
	}
	// ------------------------------------------------------------------------
	
	/**
	* Insert element into array shifting it up
	*
	* @param array
	* @param mixed
	* @param int
	* @return void
	*/
	function array_put($array, $insert_array = array(), $position = 0){
		if($position < 0){
			$position = count($array)+$position;
		}
		$first = array_slice($array,0,$position);
		$second = array_slice($array,$position);
		$array = array_merge($first,(array)$insert_array,$second);
	}

	// ------------------------------------------------------------------------
	
	/**
	* Sort array elements by their positions values
	*
	* @param
	* @param
	* @param
	* @return void
	*/
	 function position_sort($a,$b){
		 if(is_array($a) && is_array($b)){
			 if(!isset($a['position'])){
			  return 1;
			 }
			 if(!isset($b['position'])){
			  return -1;
			 }
			 return ($a['position'] > $b['position']) ? 1 : -1;
		 }
		 elseif(is_object($a) && is_object($b)){
			 if(!isset($a->position)){
			  return 1;
			 }
			 if(!isset($b->position)){
			  return -1;
			 }
			 return ($a->position > $b->position) ? 1 : -1;
		 }
	 }
	// ------------------------------------------------------------------------

	 
	/**
	* Replace chars to other chars
	*
	* @param string
	* @param string
	* @param string
	* @return string
	*/
	 function strtrim($string,$chars,$replacement = ''){
		 return str_replace(str_split($chars),strlen($replacement) > 1 ? str_split($replacement) : '',$string);
	 }
	// ------------------------------------------------------------------------

	 
	/**
	* Add some value to every element of array
	*
	* @param array
	* @param string
	* @return array
	*/
	 function array_add2values($array,$add){
		 foreach($array as &$value){
			$value = $add.$value; 
		 }
		 return $array;
	 }
	// ------------------------------------------------------------------------

	 
	/**
	* Insert data into array at offset
	*
	* @param array
	* @param mixed
	* @param int
	* @return array
	*/
	 function array_insert(&$array, $value, $offset){
			 if(!isset($array[$offset])){
				 $array[$offset] = $value;
			 }
			 else {
				 $elements = $array;
				 foreach($elements as $key=>$element){
					 if(is_numeric($key) && $key >= $offset){
						 $array[($key+1)] = $element;
						 if($array[$key] == $element) unset($array[$key]);
					 }
				 }
				 $array[$offset] = $value;
			 }
			 ksort($array);
			 return $array;
	}
	// ------------------------------------------------------------------------

	 
	/**
	* Parse data with textile
	*
	* @param string
	* @return string
	*/
	 function textile($string){
		$CI =& get_instance();
		return $CI->textile->TextileThis($string);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Compile js insert code
	*
	* @param string js path or code
	* @param boolean
	* @param  return result or compile it in template
	* @return mixed
	*/
	function js($path,$inline = FALSE, $return = FALSE){
		$CI =& get_instance();
		static $scripts = array();
		if(in_array($path,$scripts)) return FALSE;
		else $scripts[] = $path;
		$config['type'] = 'text/javascript';
		if(strpos($path,'/') == -1){
			$path = '/gears/'.$CI->name.'/js/'.$path;
		}
		if(!$inline){
			if(!strpos($path,'.js')){
				$path .= '.js';
			}
			$config['src'] = $path;
		}
		$path = "\n".$path."\n";
		return $CI->builder->script($inline ? $path : '',$config,$return);
	}
	// ------------------------------------------------------------------------

	/**
	* Compile css insert code
	*
	* @param string js path or code
	* @param boolean
	* @param  return result or compile it in template
	* @return mixed
	*/
	function css($path,$inline = FALSE, $return = FALSE){
		$CI =& get_instance();
		if(strpos($path,'/') == -1){
			$path = '/gears/'.$CI->name.'/css/'.$path;
		}
		if(!$inline){
			if(!strpos($path,'.css')){
				$path .= '.css';
			}
		}
		if($CI->uri->subdir) $path = '/'.$CI->uri->subdir.'/'.$path;
		if($inline) $path = "\n".$path."\n";
		return $CI->builder->style($inline ? $path : '@import "'.$path.'";',$return);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Redirect browser
	*
	* @param string
	* @param boolean
	* @return void
	*/
	function redirect($uri = '', $suffix = '/')
	{
		header("Location: ".(empty($uri) && isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : l($uri,$suffix)), TRUE);
		exit;
	}
	// ------------------------------------------------------------------------

	
	/**
	* Remove directories recursive
	*
	* @param string
	* @return void
	*/
	function rmdir_recurse($path)
	{
		$path= rtrim($path, '/').'/';
		$handle = opendir($path);
		for (;false !== ($file = readdir($handle));)
			if($file != "." and $file != ".." )
			{
				$fullpath= $path.$file;
				if( is_dir($fullpath) )
				{
					rmdir_recurse($fullpath);
					rmdir($fullpath);
				}
				else
				  unlink($fullpath);
			}
		closedir($handle);
	}
	// ------------------------------------------------------------------------

	
	/**
	* Explode array and trim it's data
	*
	* @param string
	* @param array
	* @return array
	*/
	function _explode($delim,$array){
		$data = explode($delim,$array);
		foreach($data as &$value){
			$value = trim($value);
		}
		return $data;
	}
	// ------------------------------------------------------------------------
	
	/**
	*  Get site base url
	*
	* @return string
	*/
	function base_url()
	{
			$CI =& get_instance();
			return l('/');
	}
	// ------------------------------------------------------------------------
	
	/**
	* Format filesize
	*
	* @param	int	Size of file.
	* @return	string
	*/
	function format_size($size,$round = 2){
		$megabyte = 1024*1024;
		$gigabyte = $megabyte*1024;
		$terabyte = $gigabyte*1024;
		if($size < 1024){
			return round($size,$round).t('number.bytes');
		}
		elseif($size >= 1024 && $size < $megabyte){
			return round($size/1024,$round).t('number.kilobyte_abbr');
		}
		elseif($size >= $megabyte && $size < $gigabyte){
			return round($size/$megabyte,$round).t('number.megabyte_abbr');
		}
		elseif($size >= $gigabyte && $size < $terabyte){
			return round($size/$gigabyte,$round).t('number.gigabyte_abbr');
		}
		else {
			return round($size,$round).t('number.terabyte_abbr');
		}
	}

// ------------------------------------------------------------------------