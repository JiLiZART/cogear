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
 * Index page hooks
 *
 * @package		CoGear
 * @subpackage	Index
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Remove counter value from storage with each node create/edit action
	*
	* @param	object	$Form
	* @param	mixed	$result
	* @return	void
	*/
	function index_form_save_($Form,$result){
		if($Form->name == 'node_createdit' && $result){
			remove('index/*');
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Add checkbox to node title breadcrumb
	*
	* @param	object	Breadcrumb
	* @return	void
	*/
	function index_breadcrumb_compile_($Breadcrumb){
		if($Breadcrumb->name == 'node_title' && acl('index promote')){
			$node =& $Breadcrumb->data;
			// If node is already on index page Ñ checbox will be checked
			$value = empty($node->promoted) ? '' : ' checked="checked"';
			$Breadcrumb->add('<input type="checkbox" class="index-promote" id="index-promote-'.$node->id.'"'.$value.'>');
		}
	}
	
	
	/**
	* Add node to index after topic is created
	*
	* @param	object	Form
	* @param	boolean	result
	* @return	void
	*/
	function index_form_save_after_($Form,$result,$table,$data){
		if($Form->name == 'node_createdit' && $result && acl('index promote') && !empty($data['promoted'])){
			$CI =& get_instance();
			$CI->indexer->promote($Form->insert_id,TRUE);
		}
	}
	
	/**
	 * Add promote_to_index checkbox for admin
	 *
	 * @param	object	Form
	 */
	 function index_form_result_($Form){
		 if($Form->name == 'node_createdit' && acl('index promote')){
			 $Form->checkbox('promoted',array('label'=>t('index label'),'value'=>'true'));
		 }
	 }
// ------------------------------------------------------------------------