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

/**
 * Assets model
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Assets {
	public $glue = TRUE;
	private $js;
	private $css;
	// We need this to store gears order
	private $gears;
	private $last_mod;
	private $hash;
	private $cache;
	public $dir = './uploads/assets/';
	private $global_template = '';

	/**
	* Constructor
	*
	* @return void
	*/
	function __construct(){
		$CI =& get_instance();
		$this->subdir = '/'.$CI->uri->subdir;
		$this->cache = $CI->site->cache;
		$this->global_template = $CI->site->template;
		if(!file_exists($this->dir)){
			 mkdir($this->dir,0777,TRUE);
			 $this->reset();
		 }
	}

	/**
	* Add asset
	*
	* @param	mixed	Files or path to file.
	* @return	object
	*/
	function add($path){
		if($this->cache) return;
		if(is_array($path)){
			usort($path,array($this,'gsort'));
			foreach($path as $piece){
				$this->add($piece);
			}
		}
		else {
			$type = strtolower(pathinfo($path,PATHINFO_EXTENSION));
			$path = ROOTPATH.trim(str_replace(ROOTPATH,'',$path),'.');
			  if(strpos($path,'gears') !== FALSE){
					$alt_path = str_replace('gears','templates/'.$this->global_template.'/gears',$path);
				if(file_exists($alt_path)){
					$path = $alt_path;
				}
			 }
			if(preg_match('/\[(.*)\]/U',$path,$matches)){
			 $group = $matches[1];
			  }
			else $group = $type == 'js' ? 'scripts' : 'styles';
			if(file_exists($path)){
				$last_mod = filemtime($path);
				if($last_mod > $this->last_mod){
					$this->last_mod = $last_mod;
				}
				if(is_null($this->$type)) $this->$type = new stdClass;
				foreach(explode('|',$group) as $cgroup){
					if(!isset($this->$type->$cgroup)){
						$this->$type->$cgroup = new ArrayObject;
					}
					$this->$type->$cgroup->append($path);
				}
			}
			else $this->reset();
			return $this;
		}
	}

	/**
	* Sort via gears order
	*
	* @param	string
	* @param	string
	* @return	boolean
	*/
	function gsort($a,$b){
		$CI =& get_instance();
		if(is_null($this->gears)) $this->gears = array_keys(get_object_vars($CI->gears));
		$aa = basename(dirname(dirname($a)));
		$bb = basename(dirname(dirname($b)));
		if($aa == $bb) {
			return strnatcasecmp($a,$b);
		}
		return array_search($aa,$this->gears) > array_search($bb,$this->gears);
	}

	/**
	* Add files of choosen type from dir
	*
	* @param	string	$dir		Directory path
	* @return	object
	*/
	function add_dir($dir){
		$path = ROOTPATH.trim(str_replace(ROOTPATH,'',$dir),'.');
		$type = basename($path);
		if($files = glob($path.'*.'.$type)){
			natcasesort($files);
			foreach($files as $file){
				$this->add($file);
			}
		}
		return $this;
	}

	/**
	* Prepare assets code array for file put
	*
	* @param	array		$output		Array of readed file contents
	* @return	string
	*/
	private function prepare($output = array()){
		$output = implode("\n",$output);
		//$output = preg_replace('#([\t|\r|\n]{3,})#im','',$output);
		$CI =& get_instance();
		$output = str_replace('../','http://'.$CI->site->url.'/templates/'.$this->global_template.'/',$output);
		$output = str_replace('{$tpl}','http://'.$CI->site->url.'/templates/'.$this->global_template.'/',$output);
		if($CI->uri->subdir){
		 foreach(array('ajax','gears') as $item){
			 $output = str_replace('/'.$item,'/'.$CI->uri->subdir.'/'.$item,$output);
		 }
		}
		return $output;
	}

	/**
	* Glue assets files together and put them in one file by type
	*
	* @return	object
	*/
	function compile(){
		$CI =& get_instance();
		$data = $CI->cache->get('assets/data',TRUE);
		if($this->cache) return $this->last_mod = empty($data['last_mod']) ? FALSE : $data['last_mod'];
		if(!$data['last_mod'] OR $data['last_mod'] < $this->last_mod OR $data['js'] != md5(serialize($this->js)) OR $data['css'] != md5(serialize($this->css))
		){
			foreach(array('js','css') as $type){
				$data[$type] = md5(serialize($this->$type));
				foreach($this->$type as $group=>$files){
					$output = array();
					foreach($files as $file){
						$output[] = file_get_contents($file);
					}
					$output = $this->prepare($output);
					file_put_contents($this->dir.$this->global_template.'_'.$group.'.'.$type,$output);
				}
			}
			$data['last_mod'] = $this->last_mod;
			$CI->cache->set('assets/data',$data);
		}
		return $this;
	}

	/**
	* Output assets.
	*
	* Depends on $this->glue param, which points to glue files together or not.
	*
	* @param	string	$type		Type of assets to output
	* @return	string
	*/
	function output($type = 'js'){
		$this->compile();
		$CI =& get_instance();
		$suffix = $this->last_mod;
		$path = str_replace(ROOTPATH,'',ltrim($this->dir,'.')).$this->global_template.'_';
		$output = "\n".($type == 'js' ? '<script type="text/javascript" src="'.$path.'scripts.js?'.$suffix.'"></script>' : '<link media="screen" type="text/css" href="'.$path.'styles.css?'.$suffix.'" rel="stylesheet" />')."\n";
		$info = (object)user_agent();
		$browser = $info->browser.str_replace('.0','',$info->version);
		if(file_exists($this->dir.$this->global_template.'_'.$info->browser.'.'.$type)){
			$output .= "\n".($type == 'js' ? '<script type="text/javascript" src="'.$path.$this->global_template.'_'.$info->browser.'.js?'.$suffix.'"></script>' : '<link media="screen" type="text/css" href="'.$path.$this->global_template.'_'.$info->browser.'.css?'.$suffix.'" rel="stylesheet" />')."\n";
		}
		if(file_exists($this->dir.$this->global_template.'_'.$browser.'.'.$type)){
			$output .= "\n".($type == 'js' ? '<script type="text/javascript" src="'.$path.$this->global_template.'_'.$browser.'.js?'.$suffix.'"></script>' : '<link media="screen" type="text/css" href="'.$path.$this->global_template.'_'.$browser.'.css?'.$suffix.'" rel="stylesheet" />')."\n";
		}
		return $output;
	}

	/**
	* Clear compiled assets
	*
	* @return	object
	*/
	function flush(){
		rmdir_recurse($this->dir);
		$CI =& get_instance();
		return $this;
	}

	/**
	* Reset last_mod time to update assets
	*/
	function reset(){
		$CI =& get_instance();
		$this->last_mod = time() + 3600;
		$CI->cache->set('assets/last_mod',$this->last_mod);
	}
	/**
	* Reset class data
	*
	* @return	object
	*/
	function clear(){
		$this->js = array();
		$this->css = array();
		return $this;
	}
}