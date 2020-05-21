<?php
/**
 * Node icon hooks
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link			 	http://cogear.ru
 * @package		Node Icon
 * @version			$Id$
 */
function node_icon_form_result_($Form){
	$CI =& get_instance();
	if($Form->name == 'node_createdit'){
		$Form->image('icon',array(
		'upload_path' => _mkdir(ROOTPATH.'/uploads/nodes_icons/'),
		'resize' => $CI->gears->node_icon->icon_size,
		),5);
		$Form->set_values($Form->data);
	}
} 

function node_icon_node_show_($Node,$node,$type,$return){
	$CI =& get_instance();
	if(!empty($node->icon)){
		$node->body = $CI->builder->span($CI->builder->img($node->icon),'fright').$node->body;
	}
}