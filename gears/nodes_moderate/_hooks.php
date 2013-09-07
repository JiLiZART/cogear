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
 * Nodes moderate hooks
 *
 * @package		CoGear
 * @subpackage	Nodes moderate	
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add textarea to pm comment to node author
	*
	* @param	object
	* @return	void
	*/
	function nodes_moderate_form_result_($Form){
		$CI =& get_instance();
		if($Form->name == 'node_createdit'){
			d('nodes_moderate');
			if(acl('nodes_moderate message_author') && isset($Form->data->aid) && $Form->data->aid != $CI->user->get('id')) $Form->editor('comment',array('stop_reset'=>TRUE));
			//if(acl('nodes_moderate change_datetime') && isset($Form->data->aid)) $Form->datetime('created_date',array('value'=>isset($Form->data->created_date) ? $Form->data->created_date : date('Y-m-d H:i:s')));
			d();
		}
	}
	// ------------------------------------------------------------------------

	/**
	* Send pm to author on node update
	*
	* @param	object
	* @param	string
	* @param	array
	* @param	array
	* @return	void
	*/
	function nodes_moderate_form_update_($Form,$table,$result,$where){
		$CI =& get_instance();
		if($Form->name == 'node_createdit' && acl('nodes_moderate') && $Form->data->aid != $CI->user->get('id') && isset($result['comment'])){
			$data = array(
			'to'=>$Form->data->aid,
			'subject' => t('!nodes_moderate pm_subject',$Form->data->name),
			'body' => $result['comment']."<br>".' 
			<a href="'.$CI->node->create_link($Form->data).'">'.$Form->data->name.'</a>',
			);
			$CI->pm->send($data);	
		}
	// ------------------------------------------------------------------------
	}
// ------------------------------------------------------------------------