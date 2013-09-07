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
 * Meta CP controller
 *
 * @package		CoGear
 * @subpackage	Meta
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		d('meta');
	}
	// ------------------------------------------------------------------------

	/**
	* Edit meta config
	*
	* @return	void
	*/
	function index(){
		$this->form->set('meta_settings')
		->input('keywords',array('validation'=>'required','js_validation'=>'required','label'=>t('keywords'),'description'=>FALSE))
		->input('description',array('validation'=>'required','js_validation'=>'required','label'=>t('!edit description'),'description'=>FALSE))
		->buttons('save')
		->set_values($this->gears->meta->info);
		if($result = $this->form->result()){
			$data['info']['keywords'] = $result['keywords'];
			$data['info']['description'] = $result['description'];
			$this->info->set(GEARS.'meta/meta')->change($data)->compile();
			msg('form.saved');
			redirect('/admin/meta');
		}
		else {
			$this->form->set_values($result)->compile();
		}
	}
	// ------------------------------------------------------------------------

}
// ------------------------------------------------------------------------