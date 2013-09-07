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
 * Upload model
 *
 * @package		CoGear
 * @subpackage	Upload
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Upload extends Model {
	/**
	* Constructor
	*
	* @return	void
	*/
	function Upload(){
		parent::Model();
	}
	// ------------------------------------------------------------------------


	/**
	* Upload file
	*
	* @param	array
	* @param	boolean
	* @return	boolean
	*/
	function file(&$elem,$url = FALSE){
		$CI =& get_instance();
		if(!isset($elem['name']) OR (isset($elem["path"]) && trim($elem["path"]) != '')) return TRUE;
		$CI->load->library('upload',$elem,'uploader');
		$CI->uploader->initialize($elem);
		if(!$url){
			if((!isset($elem['validation']) OR !strpos($elem['validation'],'required') OR isset($elem['has_file'])) && (!isset($_FILES[$elem['name']]) OR trim($_FILES[$elem['name']]['name'],'/') == '')){
				return TRUE;
			}
		}
		else if(!$this->input->post($elem['name'])){
			return TRUE;
		}
		if($url){
			if(is_string($url)){
				$result = $CI->uploader->do_url($url,TRUE);
			}
			else {
				$result = $CI->uploader->do_url($elem);
			}
		}
		else {
			$result = $CI->uploader->do_upload($elem['name']);
		}
		if($result){
		 $elem = array_merge($CI->uploader->data(),$elem);
		 $elem["path"] = str_replace(ROOTPATH,'',$elem['full_path']);
		 // First check if elem is image
		 // Elem can also have mime-type "application/octet-stream" when it's loaded via flash
		 // So we should check config param value as well
		 if(isset($elem['is_image'])){
			 list($real_width,$real_height) = getimagesize($elem['full_path']);
			 $elem['image_width'] = $real_width;
			 $elem['image_height'] = $real_height;
		 }
		 // Resize element if there is config params
		 $this->resize($elem);
		 // Create thumbs if elem is larger than should be
		 // Create thumbs if there are config param deals with it
		 if(isset($elem['thumbs'])){
			 $elem['thumbs'] = (array) $elem['thumbs'];
		  if(isset($elem['thumbs']['after'])){
			 list($width,$height) = explode('x',$elem['thumbs']['after']);
			 if($elem['image_width'] > $width && $elem['image_height'] > $height){
				 $this->make_thumb($elem,$elem['thumbs']['after']);
				 if(isset($elem['thumbs']['resize'])){
					 $elem['resize'] = $elem['thumbs']['resize'];
					 $this->resize($elem);
				 }
			 }
		  }
		  else $this->make_thumbs($elem);
		 }
		 // Crop image
		 $this->crop($elem);
		 // Create watermark
		 $this->watermark($elem);
		 return TRUE;
		}
		else {
		 $elem['error'] = str_replace('.','.<br>',$CI->uploader->display_errors('',''));
		 return FALSE;
		}
	}
	// ------------------------------------------------------------------------


	/**
	* Resize image
	*
	* @param	array
	* @return	boolean
	*/
	function resize(&$image,$size = FALSE){
		if(empty($image['resize'])){
			return FALSE;
		}
		$config = array();
		$config['source_image'] = '.'.$image['path'];
		$config['maintain_ratio'] = TRUE;
		$size = explode('x',$size ? $size : $image['resize']);
		$config['width'] = $size[0];
		$size = array_pad($size,2,$size[0]);
		$config['height'] = $size[1];
		if(isset($image['resize_aspect'])) $config['master_dim'] = $image['resize_aspect'];
		$size = getimagesize('.'.$image['path']);
		if($size[0] <= $config['width'] && $size[1] <= $config['width']){
			 return FALSE;
		}

		if(isset($image['output_format'])){
			$image['output_format'] = trim($image['output_format'],'.');
			switch($image['output_format']){
				case 'gif':
					$config['mime_type'] = 'image/gif';
					$config["new_format"] = 1;
				break;
				case 'png':
					$config['mime_type'] = 'image/png';
					$config["new_format"] = 3;
				break;
				case 'jpeg':
					$config['mime_type'] = 'image/jpeg';
					$config["new_format"] = 2;
				break;
				default:
				$config['mime_type'] = 'image/jpg';
				$config["new_format"] = 2;
				break;
			}
			$config['new_image'] = '.'.ltrim(dirname($config['source_image']),'.').'/'.pathinfo($config['source_image'],PATHINFO_FILENAME).'.'.$image['output_format'];
			$image['path'] = ltrim($config['new_image'],'.');
		}
		 $this->load->library('image_lib');
		 $this->image_lib->initialize($config);
		 if ( ! $this->image_lib->resize())
		 {
			$this->image_lib->clear();
			$image['error'] .= "\n".$this->image_lib->display_errors('','');
			return FALSE;
		 }
		 else {
			if(isset($image['output_format'])) @unlink($config['source_image']);
			$this->image_lib->clear();
			return TRUE;
		 }
	}
	// ------------------------------------------------------------------------


	/**
	* Make thumbnails
	*
	* @param	array
	* @return	array
	*/
	function make_thumbs(&$image,$size = FALSE){
	 if(!$image['thumbs']) return FALSE;
	 if(is_object($image['thumbs']) OR is_array($image['thumbs'])){
	  foreach($image['thumbs'] as $thumb){
		  $this->make_thumb($image,$thumb);
	  }
	 }
	 else {
		 $this->make_thumb($image,$size);
	 }
	 return $image;
	}
	// ------------------------------------------------------------------------


	/**
	* Make thumbnail
	*
	* @param	array
	* @param	string
	* @return	boolean
	*/
	function make_thumb(&$image,$size){
	 $size = explode("x",$size);
	 if(!isset($size[1])){
	  $y = $size[0];
	 }
	 else {
	  $y = $size[1];
	 }
	 $x = $size[0];
	 $target_dir = rtrim(dirname($image['path']),'/').'/'.$x.'x'.$y.'/';
	 if(!file_exists($target_dir)){
		 mkdir_if_not_exists($target_dir, 0777);
	 }
	 $config = array();
	 $config["new_image"] = '.'.$target_dir.basename($image['path']);
	 $config['source_image'] = '.'.$image['path'];
	 $config['maintain_ratio'] = isset($image['thumbs_ratio']) ? $image['thumbs_ratio'] : TRUE;
	 $config["master_dim"] = isset($image['thumbs_aspect']) ? $image['thumbs_aspect'] : 'auto';
	 $config["quality"] = "100%";
	 if(isset($image['thumbs_crop'])){
		$x+=10;
		$y+=10;
	 }
	 $config["width"] = $x;
	 $config["height"] = $y;
	 $config['overwrite'] = TRUE;
	 if(isset($image['thumbs_config'])){
		 $config = array_merge($config,$image['thumbs_config']);
	 }
	 $this->load->library('image_lib');
	 $this->image_lib->initialize($config);
	 if (!$this->image_lib->resize()){
		$this->image_lib->clear();
		$image['error'] = "\n".$this->image_lib->display_errors('','');
		return FALSE;
	 }
	 else {
		$thumb['path'] = $config["new_image"];
		$this->image_lib->clear();
		if(isset($image['thumbs_crop'])){
		 $thumb['size'] = implode('x',$size);
		 $thumb['crop'] = $image['thumbs_crop'];
		 $this->crop($thumb,$size);
		}
		$this->image_lib->clear();
		if(isset($image['watermark'])){
		 $thumb['watermark'] = isset($image['thumbs_watermark']) ? $image['thumbs_watermark'] : $image['watermark'];
		 //$this->watermark($thumb);
		}
		$image['thumbnails'][implode('x',$size)] = trim($thumb['path'],'.');
		return TRUE;
	 }
	}
	// ------------------------------------------------------------------------

	/**
	* Crop images
	*
	* @param	array
	* @return	boolean
	*/
	function crop(&$image){
		if(empty($image['crop']) OR !is_string($image['crop'])) return FALSE;
		$config['source_image'] = '.'.ltrim($image['path'],'.');
		$size = getimagesize($config['source_image']);

		if(is_array($image['crop']) OR is_object($image['crop'])){
		 $image['crop'] = (array)$image['crop'];
		 if(isset($image['size']) && (!in_array($image['size'],$image['crop']) && !in_array($image['size'],array_keys($image['crop'])))){
		  return TRUE;
		 }
		 $image['crop'] = isset($image['crop'][$image['size']])  ? $image['crop'][$image['size']] : reset($image['crop']);
		}
		$crop = explode('x',$image['crop']);
		if(count($crop) == 1) $crop[1] = $crop[0];
		$config['width'] = $crop[0];
		$config['height'] = $crop[1];

		$config['x_axis'] = $size[0]/2 - $config['width']/2;
		$config['y_axis'] = $size[1]/2 - $config['height']/2;
		$config['maintain_ratio'] = FALSE;
		$config["master_dim"] = 'auto';
		$this->load->library('image_lib');
		$this->image_lib->initialize($config);
		if(!$this->image_lib->crop()){
			$this->image_lib->clear();
			$error = "\n".$this->image_lib->display_errors('','');
			isset($image['error']) ? $image['error'] .=$error : $image['error'] = $error;
			return FALSE;
		 }
		 else {
			$this->image_lib->clear();
			return TRUE;
		 }
	}
	// ------------------------------------------------------------------------

	/**
	* Watermarking
	*
	* @param	array
	* @param	array
	* @return	boolean
	*/
	function watermark(&$image,$params = FALSE){
		if(!isset($image['watermark']) OR !$image['watermark']) return FALSE;
		$this->do_watermark('.'.ltrim($image['path'],'.'),is_bool($image['watermark']) ? GEARS.'upload/img/watermark.png' : GEARS.$image['watermark']);
		return TRUE;
	}
	// ------------------------------------------------------------------------


	/**
	*  Create watermark
	*
	* @param	string $sourcefile Filename of the picture to be watermarked.
	* @param	string $watermarkfile Filename of the 24-bit PNG watermark file.
	* @return	void
	*/
	function do_watermark($sourcefile, $watermarkfile) {
	   $size['source'] = getimagesize($sourcefile);
	   $size['watermark'] = getimagesize($watermarkfile);
	   if($size['source'][0] < $size['watermark'][0]*2 OR $size['source'][1] < $size['watermark'][1]*2) return;
	   //Get the resource ids of the pictures
	   $watermarkfile_id = imagecreatefrompng($watermarkfile);

	   imageAlphaBlending($watermarkfile_id, TRUE);
	   imageSaveAlpha($watermarkfile_id, TRUE);

	   $fileType = strtolower(substr($sourcefile, strlen($sourcefile)-3));

	   switch($fileType) {
		   case('gif'):
			   $sourcefile_id = imagecreatefromgif($sourcefile);
			   $sourcefile_width=imageSX($sourcefile_id);
			  $sourcefile_height=imageSY($sourcefile_id);
  // create an empty truecolor container
		   $tempimage = imagecreatetruecolor($sourcefile_width, $sourcefile_height);

		   // copy the 8-bit gif into the truecolor image
		   imagecopy($tempimage, $sourcefile_id, 0, 0, 0, 0,
							   $sourcefile_width, $sourcefile_height);

		   // copy the source_id int
		   $sourcefile_id = $tempimage;
		   //imageAlphaBlending($sourcefile_id, TRUE);
		   //imageSaveAlpha($sourcefile_id, TRUE);
		   break;

		   case('png'):
			   $sourcefile_id = imagecreatefrompng($sourcefile);
			   imageAlphaBlending($sourcefile_id, TRUE);
			   imageSaveAlpha($sourcefile_id, TRUE);
			   break;

		   default:
			   $sourcefile_id = imagecreatefromjpeg($sourcefile);
	   }

	   //Get the sizes of both pix
	   $sourcefile_width=imageSX($sourcefile_id);
	  $sourcefile_height=imageSY($sourcefile_id);
	  $watermarkfile_width=imageSX($watermarkfile_id);
	  $watermarkfile_height=imageSY($watermarkfile_id);

	   $dest_x = $sourcefile_width - $watermarkfile_width;
	   $dest_y = $sourcefile_height - $watermarkfile_height;

	   imagecopy($sourcefile_id, $watermarkfile_id, $dest_x, $dest_y, 0, 0,
						   $watermarkfile_width, $watermarkfile_height);

	   //Create a jpeg out of the modified picture
	   switch($fileType) {

		   // remember we don't need gif any more, so we use only png or jpeg.
		   // See the upsaple code immediately above to see how we handle gifs
		   case('png'):
			   imagepng ($sourcefile_id, $sourcefile);
			   break;
		  case('gif'):
			   imagegif ($sourcefile_id, $sourcefile);
			   break;
		   default:
			   imagejpeg ($sourcefile_id, $sourcefile);
	   }
	   imagedestroy($sourcefile_id);
	   imagedestroy($watermarkfile_id);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------