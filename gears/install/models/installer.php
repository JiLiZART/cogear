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
 * Installer model. All _install models should be inherited from this class
 *
 * @package		CoGear
 * @subpackage	* @category		Gears 
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Installer extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function Installer(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Import gear pack from archive
	*
	* @param	string
	* @param	string
	* @return	void
	*/
	function import($file,$type = 'gear'){
		$this->load->library('install unzip');
		$file = './'.ltrim($file,'./');
		$config['fileName'] = $file;
		$filename = pathinfo($file,PATHINFO_FILENAME);
		$path = dirname($file);
		@chmod($config['fileName'],0777);

		$config['targetDir'] = $path;
		
		switch($type){
			case 'gear':
			if(strpos($filename,'-')){
				$tmp = explode('-',$filename);
				$filename = array_shift($tmp);
				$info = implode('-',$tmp);
				$config['targetDir'] = $path.'/'.$filename;
			}
			break;
		}
		rm($path.'/'.$filename,TRUE);
		$this->unzip->initialize($config);
		$this->unzip->unzipAll();
		switch($type){
		  case 'gear':
			if(file_exists(GEARS.$filename.'/'.$filename.'.info')){
			  copy(GEARS.$filename.'/'.$filename.'.info',GEARS.$filename.'/'.$filename.'.info.backup');
			}
			if(file_exists($path.'/'.$filename.'/'.$filename)) $path = $path.'/'.$filename;
			if(file_exists($path.'/'.$filename.'/'.$filename.'.info')){
				$this->info->set($path.'/'.$filename.'/'.$filename)->change(array('enabled'=>FALSE))->compile();
			}
			elseif(isset($info)) {
				$this->info->set(GEARS.$filename.'/'.$filename)->change(array('version'=>$info))->compile();
			}
			if(file_exists($path.'/'.$filename)){
				@chmod($path.'/'.$filename,0777);
				cp($path.'/'.$filename,'./gears/'.$filename,0777);
			 }
		  break;
		  case 'update':
			  $this->info->set($path.'/'.$filename.'/update')->add(array('installed'=>TRUE))->compile();
			  if(file_exists($path.'/'.$filename.'/files/')) _copy($path.'/'.$filename.'/files/',ROOTPATH,0777);
		  break;
		}
		@unlink($file);
		return TRUE;	
	}
	// ------------------------------------------------------------------------

	
	/**
	* Export current gear
	*
	* @param	string
	* @param	boolean
	* @return	mixed
	*/
	function export($name, $return = FALSE, $path = FALSE){
			$this->load->library('zip');
			$this->zip->add_dir('/');
			if($path){
				$path = str_replace(ROOTPATH,'',$path);
				$path = '.'.ltrim($path,'.');
				$path = rtrim($path,'/').'/';
			}
			else $path = './gears/'.$name.'/';
			$this->zip->read_dir($path,FALSE);
			return $return ? $this->zip->get_zip() : $this->zip->download($name.'.zip');
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------