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
 *  Upload controller
 *
 * @package		CoGear
 * @subpackage	Upload
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	* Constructor
	*
	* @return void
	*/
	function __construct(){
		parent::Controller();
	}

	/**
	 * File upload
	 */
	public function index(){
		if(!$this->user->get('id')) return _403();
		$config = $this->gears->upload;
		include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'fileUpload'.EXT;
		// list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$allowedExtensions = explode('|',$config->allowed_types);
		// max file size in bytes
		$sizeLimit = $config->max_size * 1024 * 1024;

		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
		$dir = ROOTPATH.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.date('Y/m/d').DIRECTORY_SEPARATOR.$this->user->get('id');
		if(!is_dir($dir)){
			mkdir($dir,0777,TRUE);
		}
		$result = $uploader->handleUpload($dir,$config->overwrite);
		if(!empty($result['file'])){
		$size = getimagesize($result['file']);
		if(!empty($config->thumbs->after)){
			list($width,$height) = explode('x',$config->thumbs->after);
			if($width < $size[0] && $height < $size[1]){
				$img_config['source_image'] = $result['file'];
				$img_config['maintain_ratio'] = TRUE;
				$thumb = explode('x',$config->thumbs->size);
				$img_config['width'] = $thumb[0];
				$img_config['height'] = $thumb[1];
				$filename = pathinfo($result['file'],PATHINFO_FILENAME);
				$img_config['new_image'] = dirname($result['file']). '/' .str_replace($filename,$filename.'_thumb',basename($result['file']));
				 $this->load->library('image_lib');
				 $this->image_lib->initialize($img_config);
				 if ( ! $this->image_lib->resize())
				 {
					$this->image_lib->clear();
					$result['message'] .= "\n".$this->image_lib->display_errors('','');
					$result['success'] = FALSE;
				 }
				 else {
					$result['file'] = str_replace(ROOTPATH,'',$result['file']);
					$info = getimagesize($img_config['new_image']);
					$result['thumb'] = str_replace(ROOTPATH,'',$img_config['new_image']);
					$result['image'] = '<a href="'.$result['file'].'"><img src="'.$result['thumb'].'" width="'.$info[0].'" height="'.$info[1].'" alt=""></a>';
				 }
			 }
		}
		if(empty($result['image'])) {
			$result['file'] = str_replace(ROOTPATH,'',$result['file']);
			$result['image'] = "\n".'<img src="'.$result['file'].'" width="'.$size[0].'" height="'.$size[1].'" alt="'.basename($result['file']).'" align="">'."\n";
		}
		// dirty trick to fix URL path on Windows systems
		$result['image'] = str_replace('\\', '/', $result['image']);
		$result['image_left'] = str_replace('align=""','align="left"',$result['image']);
		$result['image_right'] = str_replace('align=""','align="right"',$result['image']);
		$result['image_left'] = '<p align="center">'.$result['image'].'</p>';

		// to pass data through iframe you will need to encode all html tags
		echo json_encode($result);
		}
		die();
	}
}
