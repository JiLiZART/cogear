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
 *  Page model
 *
 * @package		CoGear
 * @subpackage	Pages
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Page extends Model{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Model();
	}
	// ------------------------------------------------------------------------
	
	/**
	*  Show page
	*
	* @param	mixed	$param
	* @param	mixed	$header Show header.
	* @return	
	*/	
	function show($param,$header = TRUE){
		if(is_numeric($param)){
			$this->db->where(array('id'=>$param));
		}
		elseif(is_string($param)) {
			$this->db->where(array('url_name'=>$param));
		}
		$page = $this->db->get('pages')->row();
		if(!$page) return _404();
			if($header) {
				$prefix = $this->builder->h1($page->name.(acl('pages admin') ? ' <a href="/admin/pages/createdit/'.$page->id.'/"><img src="/gears/global/img/icon/edit.png" title="'.t('!edit edit').'" alt="'.t('!edit edit').'"></a>' : FALSE));
				title($page->name);
			}
			else $prefix = '';
			
	    if($this->user->is_logged()){
		    foreach($this->user->get() as $key=>$item){
			    if(is_string($item)) $page->body = str_replace('%'.$key.'%',$item,$page->body);
		    }
	    }
		$body = $this->builder->div($this->parser->parse($page->body,'textarea'),'text');
		// Add page meta
		if(!empty($page->keywords)) meta($page->keywords,'keywords',TRUE);
		if(!empty($page->description)) meta($page->description,'description',TRUE);

		if(in_array('ajax',$this->uri->segments)){
			echo $this->builder->div($body,'page');
			exit();
		}
		else $this->builder->div($prefix.$body,'page',TRUE);
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------