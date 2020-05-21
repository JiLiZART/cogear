<?php
/**
* Tags hooks
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Tags
* @version		$Id$
*/

/**
* Add tags input field to node createdit form.
*
* @param	object	$Form
* @return	void
*/
function tags_form_result_($Form){
	if($Form->name == 'node_createdit'){
		d('tags');
		if($tags = empty($Form->data->tags) ? FALSE : $Form->data->tags){
			foreach(explode(',',$tags) as $tag){
				if(trim($tag) == '') continue;
				$output[] = trim($tag);
			}
			$value = implode(', ',$output);
		}
		$Form->input('tags',array('autocomplete'=>array('url'=>'/ajax/tags/complete/','multiple'=>TRUE),'value'=>empty($value) ? FALSE : $value));
		d();
	}
}

/**
* Process save results
*
* @param	object	Form
* @param	mixed 	Result
* @param	string	Table name
* @param	array	Data to save
* @return	void
*/
function tags_form_save_after_($Form,$result,$table,$data){
	if($Form->name == 'node_createdit'){
	if(!empty($data['tags']) && $data['published'] != 'NULL'){
		$tags = array_unique(explode(',',$data['tags']));
		foreach($tags as $tag){
			$tag = trim($tag);
			if(empty($tag)) continue;
			if(!$row = $Form->db->where('name',$tag)->get('tags')->row()){
				$Form->db->insert('tags',array(
				'name'=>$tag,
				));
				$tid = $Form->db->insert_id();
			}
			else {
				$tid = $row->id;
			}
			$Form->db->insert('nodes_tags',array(
			'nid'=>$Form->insert_id,
			'tid'=>$tid
			));
		}
		$Form->cache->tags('tags')->clear();
	}
	}
}

/**
* Process update results
*
* @param	object	Form
* @param	mixed 	Result
* @param	string	Table name
* @param	array	Data to save
* @return	void
*/
function tags_form_update_after_($Form,$result,$table,$data,$where){
	if($Form->name == 'node_createdit'){
	/*
	* Important. If tags weren't changed -- nothing will be happend.
	*/

		if(!empty($data['tags']) && trim($Form->data->tags) == trim($data['tags']) && $data['published'] != 'NULL') return;
		elseif(empty($data['tags'])){
			$data['tags'] = '';
			$Form->db->update('nodes',array('tags'=>''),$where);
		}
		/*
		* Remove all links between node and tags.
		*/
		$Form->db->where('nid',$where['id'])->delete('nodes_tags');

		if(!empty($data['tags'])){
			/*
			* Get old tags
			*/
			$old_tags = array_unique(explode(',',$data['tags']));
			/*
			* Seek through tags and check them.
			* If tag doesn't belong to any node -- delete tag.
			*/
			foreach($old_tags as $tag){
				if($t = $Form->db->get_where('tags',array('name'=>trim($tag)))->row()){
					if(strpos($data['tags'],trim($tag)) !== FALSE){
						$reserved_tags[$t->name] =$t->id;
					}
					elseif(!$Form->db->get_where('nodes_tags',array('tid'=>$t->id))->row() OR $data['published'] != 'NULL'){
						$Form->db->where('id',$t->id)->delete('tags');
					}
					
				}
			}
		}
		if($data['published'] != 'NULL'){
			$tags = array_unique(explode(',',$data['tags']));
			foreach($tags as $tag){
				$tag = trim($tag);
				if(empty($tag)) continue;
				if($t = $Form->db->where('name',$tag)->get('tags')->row()){
					$tid = $t->id;
				}
				elseif(!empty($reserved_tags[$tag])){
					$tid = $reserved_tags[$tag];
				}
				else {
					$Form->db->insert('tags',array(	'name'=>$tag));
					$tid = $Form->db->insert_id();
				}
				$Form->db->insert('nodes_tags',array(
				'nid'=>$where['id'],
				'tid'=>$tid
				));
			}
		}
		$Form->cache->tags('tags')->clear();
	}
}

/**
* Process delete node
*
* @param	object	Form
* @param	mixed 	Result
* @param	string	Table name
* @param	array	Data to save
* @return	void
*/
function tags_form_delete_after_($Form,$result,$table,$where){
	if($Form->name == 'node_createdit'){
		if(!empty($Form->data->tags)){
		/*
		* Remove all links between node and tags.
		*/
		$Form->db->where('nid',$where['id'])->delete('nodes_tags');
			/*
			* Get tags
			*/
			$tags = array_unique(explode(',',$Form->data->tags));
			/*
			* Seek through tags and check them.
			* If tag doesn't belong to any node -- delete tag.
			*/
			foreach($tags as $tag){
				if($t = $Form->db->get_where('tags',array('name'=>trim($tag)))->row()){
					if(!$Form->db->get_where('nodes_tags',array('tid'=>$t->id))->row()){
						$Form->db->where('id',$t->id)->delete('tags');
					}
					
				}
			}
			$Form->cache->tags('tags')->clear();
		}
	}
}

/**
* Add tags to node output
*
* @param	object	Node
* @param	mixed	result
* @param	object	Node data object
* @param	string	Type
* @return	void
*/
function tags_node_show_($Node,$node,$type){
	if(empty($node->tags)) return;
	$tags = explode(',',$node->tags);
	foreach($tags as $tag){
		$tag = trim($tag);
		if(empty($tag)) continue;
		$output[] = '<a href="'.l('/tags/'.$tag).'">'.$tag.'</a>';
	}
	$node->extra = '<div class="tags">'.implode(', ',$output).'</div>';
}