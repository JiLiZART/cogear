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
 * Captcha model
 *
 * @package		CoGear
 * @subpackage	Captcha
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Captcha extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function Captcha(){
		parent::Model();
		$this->load->plugin('captcha');
	}
	// ------------------------------------------------------------------------

	/**
	* Initializating captcha
	*
	* @param	boolean	$flush	To flush last captcha or not
	* @return	string
	*/
	function init($flush = FALSE){
		if(!$this->session->get('captcha') OR $flush OR !$this->input->post('captcha')){
			if(count($words = explode(',',$this->gears->captcha->words)) > 1){
				$word = $words[rand(0,count($words)-1)];
			}
			else {
				$word = substr(md5(time()+time()+date('Y-m-d H:i:s')+$this->session->get('session_id')),0,rand($this->gears->captcha->min ? $this->gears->captcha->min : 3,$this->gears->captcha->max ? $this->gears->captcha->max : 5));	
			}
			$word = trim($word);
			$tmp = scandir(GEARS.'captcha/fonts/');
			foreach($tmp as $key=>$font){
				if(pathinfo($font,PATHINFO_EXTENSION) == "ttf"){
					$fonts[] = $font;
				}
			}
			$config = array(
				'word'         => $word,
				'img_path'     => mkdir_if_not_exists(ROOTPATH.'/uploads/captcha/'),
				'img_url'     => 'http://'.$this->site->url.'/uploads/captcha/',
				'font_path'     => GEARS.'captcha/fonts/'.$fonts[rand(0,count($fonts)-1)],
				'img_width'     => 300,
				'img_height' => 30,
				'expiration' => 6000,
				'color' => array(rand(0,50),rand(0,200),rand(100,200))
			);
			$captcha = create_captcha($config);
			$this->session->set('captcha',array('word'=>$word,'image'=>$captcha['image']));
			return $captcha['image'];            
		}
		else {
			return $this->session->get('captcha')->image;
		}
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------