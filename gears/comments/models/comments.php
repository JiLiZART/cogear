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
 * Comments model
 *
 * @package		CoGear
 * @subpackage	Comments
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Comments extends Model{
	public $table;
	public $links_table;
	public $link_field;
	public $views_table;
	public $data;
	public $name = FALSE;
	private $access = FALSE;

	/**
	* Constuctor
	*
	* @return	void
	*/
	function Comments(){
		parent::Model();
	}
	// ------------------------------------------------------------------------

	/**
	* Initialization
	*
	* @param	string
	* @param	mixed
	* @param	mixed
	* @return	object
	*/
	function set($table,$data = FALSE, $name = FALSE){
		$this->table = $table;

		$this->links_table = 'comments_'.$table;
		$this->views_table = $this->links_table.'_views';
		$this->link_field = substr($table,0,1).'id';
		if($data) $this->data =& $data;
		if($name) $this->name = $name;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Set data for model
	*
	* @param	mixed
	* @return	object
	*/
	function data($data){
		if(is_object($data)) $this->data =& $data;
		else $this->data = (object)$data;
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Check access for comments - only one time during all page life cycle
	*
	* @return	void
	*/
	function check_access(){
		if(!$this->access){
			$this->access = array(
			'add' => acl('comments add'),
			'edit' => acl('comments edit'),
			'edit_all' => acl('comments edit_all'),
			'delete' => acl('comments delete_all'),
			'delete_all' => acl('comments delete_all'),
			'destroy' => acl('comments destroy'),
			'view_ip' => acl('comments view_ip'),
			'view_ip_all' => acl('comments view_ip_all'),
			'delete_node_author' => acl('comments delete_node_author'),
			'edit_node_author' => acl('comments edit_node_author'),
			);
		}
	}
	// ------------------------------------------------------------------------


	/**
	* Show comments. Can be hooked. because of _ prefix.
	*
	* @param	array
	* @param	string
	* @param	boolean
	* @param	booelan
	* @return	mixed
	*/
	function _show($comments = FALSE, $return = FALSE, $type = 'tree',$wrapper = TRUE){
		if($this->data && $this->data->comments > 0){
			$this->builder->h1(fc_t('!gears comments').' ('.$this->builder->a($this->data->comments,array('id'=>'comments_counter','class'=>'comments_counter','href'=>'#comments')).') &darr;',TRUE);
		}
		$this->check_access();
		if(!$comments){
				//$comments = $this->cache->get('comments/'.$this->table.'/'.$this->data->id);
				//if(!$comments){
					$this->db->join($this->links_table,$this->links_table.'.cid = comments.id','inner');
					$this->db->where($this->links_table.'.'.$this->link_field,$this->data->id);
					$this->db->order_by($type == 'tree' ? 'path' : 'comments.id',$type == 'tree' ? 'asc' : 'desc');
					$this->query();
					$comments = array4key($this->db->get('comments')->result_array(),'id');
				//	$this->cache->set('comments/'.$this->table.'/'.$this->data->id,$comments);
				//}
		}
		if($type == 'tree'){
			$last_id = $this->comments_views($comments);
			foreach($comments as $key=>&$comment){
				if($last_id && $last_id < $key && $comment['aid'] != $this->user->get('id')){
					$comment['class'] = 'new';
				}
				$comment = $this->process($comment,$type);
			}
			if($this->user->get('id')){
			  js('/gears/comments/js/inline/updater.class',FALSE,TRUE);
			  if ($this->gears->comments->show_updater) js('/gears/comments/js/inline/comments',FALSE,TRUE);
			}
			if($this->access['edit'] OR $this->access['edit_all']){
				js('/gears/comments/js/inline/edit',FALSE,TRUE);
			}
			if($this->access['destroy']){
				js('/gears/comments/js/inline/destroy',FALSE,TRUE);
			}
		}
		if(is_object($comments)) $comments = object2array($comments);
		return $return === TRUE ? $comments : $this->_template('comments comments',array('comments'=>$comments,'wrapper'=>$wrapper,'type'=>$type),$return);
	}
	// ------------------------------------------------------------------------

	/**
	* Manage comments views
	*
	* @param	array	comments
	* @param	mixed	comments count
	* @return	int		last_comment_id
	*/
	public function comments_views($comments = array(),$count = 0){
				if($uid = $this->user->get('id')){
					$output = array();

					if($comments_views = array4key($this->db->where(array('uid'=>$uid))->get($this->views_table)->result_array(),$this->link_field)){
						$node_views = isset($comments_views[$this->data->id]) ? $comments_views[$this->data->id] : FALSE;
					}

					if(!isset($node_views)){
					 $node_views = $this->db->order_by('id','desc')->get_where($this->views_table,array($this->link_field=>$this->data->id,'uid'=>$uid))->row_array();
					}
					$comm_num = $this->db->where($this->link_field,$this->data->id)->count_all_results($this->links_table);
					if($comments){
						$last_id = max(array_keys($comments));
						if($node_views){
							$last_id_db = $node_views['cid'];
							if($last_id != $last_id_db){
								$this->db->delete($this->views_table,array($this->link_field=>$this->data->id,'uid'=>$uid));
								$this->db->insert($this->views_table,array($this->link_field=>$this->data->id,'cid'=>$last_id,'uid'=>$uid,'count'=>$comm_num));
							}
						}
						else {
								$this->db->insert($this->views_table,array($this->link_field=>$this->data->id,'cid'=>$last_id,'uid'=>$uid,'count'=>$comm_num));
						}
					}
					else {
						$last_id = 0;
						if(!$node_views){
							$this->db->insert($this->views_table,array($this->link_field=>$this->data->id,'cid'=>0,'uid'=>$uid,'count'=>$comm_num));
						}
					}
					$comments_views[$this->data->id]['cid'] = $last_id;
					$comments_views[$this->data->id]['count'] = $comm_num;
					$this->session->set('comments_'.$this->table.'_views',$comments_views);
					return isset($last_id_db) ? $last_id_db : FALSE;
				}
				return FALSE;
	}

	/**
	* Create comments form
	*
	* @param	boolean
	* @return	object
	*/
	function form($compile = TRUE){
		d('comments');
		if(!$this->user->get('id')){
			$this->_template(array(t('comments loginza')));
		}
		if(!acl('comments add')){
			return FALSE;
		}
		$CI =& get_instance();
		$link = $CI->builder->a(t('post_comment'),'#post_comment',FALSE,'post_comment');
		$avatar = $CI->builder->img(reset(make_icons($CI->user->get('avatar'))),'avatar','avatar','avatar');
		$CI->builder->h1($link.' '.$avatar.' '.$CI->builder->a($CI->user->get('name'),l('/user/'.$CI->user->get('url_name').'/')),FALSE,'add_comments_place',TRUE);
		$CI->form->set('add_comments')->key();
		$CI->form->backlink = FALSE;
		$config = array();
		if(isset($this->data)){
		  $config['value'] = $this->data->id;
		}
		else {
		  $config['validation'] = 'integer';
		}
		$CI->form->hidden($this->link_field,$config);
		$CI->form->hidden('parent-id');
		$CI->form->hidden('id');
		$CI->form->hidden('target',array('value'=>$this->table));
		$CI->form->editor('body',array('label_hidden'=>TRUE,'validation'=>'required|min_length[5]','js_validation'=>'required'));
		$CI->form->buttons(array(
		'preview' => array('value'=>t('edit preview')),
		'submit' => array('value'=>t('edit send')),
		));
		if($compile){
		 if($result = $CI->form->result()){
			 if(is_array($result)){
				 $result = $this->createdit($result);
				 redirect($CI->uri->uri_string,'#comment-'.$result['id']);
			 }
		 }
		 $CI->form->compile();
		}
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Create or edit comments
	*
	* @param	array
	* @return	object
	*/
	function _createdit($result){
		if(!acl('comments add')) return FALSE;
		$this->cache->clear('comments/'.$this->table.'/'.$result[$this->link_field]);
		$this->cache->tags('comments,users/'.$this->user->get('id'))->clear();
		$CI =& get_instance();
		$result['body'] = $CI->parser->parse($result['body'],'comment','prepare');
		// If isset 'id' - action is edit
		if(isset($result['id']) && acl('comments edit') && $this->query() &&  $comment = $this->db->get_where('comments',array('comments.id'=>$result['id']))->row()){
			if($this->user->get('id') != $comment->aid && !acl('comments edit_all')) return FALSE;
			if($this->db->update('comments',array('body'=>$result['body']),array('id'=>$comment->id))){
/*
				$comment->body = $result['body'];
				$this->process($comment);
				return $comment;
*/
				$this->session->set('comm_edit_id',$comment->id);
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		// Otherwise - new comment will be created
		else {
			if(!isset($result['parent-id'])){
				$data = array(
				'aid' => $this->user->get('id'),
				'body' => $result['body'],
				'ip' => $this->session->get('ip_address'),
				);
				$this->db->insert('comments',$data);
				$this->insert_id =  $this->db->insert_id();
				$this->db->insert($this->links_table,array($this->link_field=>$result[$this->link_field],'cid'=>$this->insert_id));
				$count = $this->db->where($this->link_field,$result[$this->link_field])->count_all_results($this->links_table);
				$this->db->update($this->table,array('comments'=>$count),array('id'=>$result[$this->link_field]));
				if($this->insert_id && $this->db->update('comments',array('path'=>str_pad($this->insert_id,10,' ',STR_PAD_LEFT)),array('id'=>$this->insert_id))){
					//$data['id'] = $this->insert_id;
/*
					$this->query();
					$data = $this->db->get_where('comments',array('comments.id'=>$this->insert_id))->row_array();
					$this->process($data);
					return $data;
*/
					return TRUE;
				}
				else {
					return FALSE;
				}
			}
			else {
				$parent = $this->db->get_where('comments',array('id'=>$result['parent-id']))->row();
				if(!$parent) return FALSE;
				$data = array(
				'aid' => $this->user->get('id'),
				'level'=>($parent->level+1),
				'body' => $result['body'],
				'ip' => $this->session->get('ip_address'),
				);
				$this->db->insert('comments',$data);
				$this->insert_id =  $this->db->insert_id();
				$this->db->insert($this->links_table,array($this->link_field=>$result[$this->link_field],'cid'=>$this->insert_id));
				$count = $this->db->where($this->link_field,$result[$this->link_field])->count_all_results($this->links_table);
				$this->db->update($this->table,array('comments'=>$count),array('id'=>$result[$this->link_field]));
				if($this->insert_id && $this->db->update('comments',array('path'=>$parent->path.'.'.$this->insert_id),array('id'=>$this->insert_id))){
/*
					$this->query();
					$data = $this->db->get_where('comments',array('comments.id'=>$this->insert_id))->row_array();
					$this->process($data);
					return $data;
*/
					return TRUE;
				}
				else {
					return FALSE;
				}
			}
		}
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	*  Process comment
	*
	* @param	mixed
	* @param	string
	* @param	boolean
	* @return	mixed
	*/
	function _process($comment,$type = 'tree'){
			if(is_object($comment)){
				$is_object = TRUE;
				$comment = (array)$comment;
			}
			$this->check_access();
			if(!isset($comment['author'])) $comment['author'] = $this->user->get('name');
			if(!isset($comment['author_url_name'])) $comment['author_url_name'] = $this->user->get('url_name');
			if(!isset($comment['avatar'])){
				if($comment['aid'] == $this->user->get('id')) $comment['avatar'] = $this->user->get('avatar');
				else $comment['avatar'] = '';
			}
			if($author_id = isset($this->data->from) ? $this->data->from : (isset($this->data->aid) ? $this->data->aid : FALSE)){
				if($author_id == $comment['aid']){
					$comment['is_author'] = TRUE;
					$before = '<img src="/gears/comments/img/icon/author.png" class="author" width="16" height="16" alt="'.t('comments item_author').'" title="'.t('comments item_author').'"/>';
					$comment['before'] = empty($comment['before']) ? $before : $comment['before'].$before;
				}
			}
			$comment['avatar'] = reset(make_icons($comment['avatar']));
			if(!isset($comment['created_date'])) $comment['created_date'] = date('Y-m-d H:i');
				$data = (object)$comment;
				$CI =& get_instance();
				$CI->breadcrumb->set('comment_header')->data($data);
				$CI->breadcrumb->add('<a href="'.l('/user/'.$comment['author_url_name']).'"><img class="avatar" src="'.$comment['avatar'].'" alt="'.$comment['author'].'"></a> <a href="'.l('/user/'.$comment['author_url_name']).'">'.$comment['author'].'</a>');
				$CI->breadcrumb->add('<img src="/gears/nodes/img/icon/time.png" alt="time"> '.df($comment['created_date']));
				$CI->breadcrumb->add('<a href="#comment-'.$comment['id'].'">#</a>');
				if($this->access['view_ip'] && $comment['author'] == $this->user->get('name') OR $this->access['view_ip_all']){
					$CI->breadcrumb->add(' <small>'.$comment['ip'].'</small> ');
				}
				$comment['body'] = $CI->parser->parse($comment['body'],'comment');
			if($type == 'tree'){
				if($comment['aid'] == $this->user->get('id') && $this->access['edit'] && (time() - strtotime($comment['created_date']) < ($this->gears->comments->time_to_edit ? $this->gears->comments->time_to_edit : 60)) OR $this->access['edit_all']){
					$CI->breadcrumb->add('<a href="javascript:void(0)" class="edit" id="edit-'.($comment['id']).'" onclick="comment_edit('.($comment['id']).')"><img src="/gears/global/img/icon/edit.png" alt="'.t('edit edit').'" title="'.t('edit edit').'"></a>');
				}
				if($this->access['destroy']){
					$CI->breadcrumb->add(' <a href="javascript:void(0)" class="destroy" id="destroy-'.($comment['id']).'" onclick="comment_destroy('.($comment['id']).')"><img src="/gears/comments/img/icon/destroy.png" title="'.t('edit destroy').'" alt="'.t('edit destroy').'"></a>');
				}
			}
			$comment['header'] = $CI->breadcrumb->compile();

			if($type == 'tree' && $this->user->get('id') && $this->access['add']){
				$comment['extra'] = '<a href="#comment-'.$comment['id'].'" id="reply-'.$comment['id'].'" class="reply" onClick="showReply(this)">'.t('edit reply').'</a>';
			}
			elseif($type == 'plain') {
				$comment['level'] = 0;
			}
			if(isset($is_object)) $comment = (object)$comment;
			return $comment;
	}
	// ------------------------------------------------------------------------

	/**
	* Destroy comment
	*
	* @param	int
	* @return	object
	*/
	function destroy($id){
			$this->db->select('comments.*,'.$this->links_table.'.'.$this->link_field,FALSE);
			$this->db->join($this->links_table,$this->links_table.'.cid = comments.id');
			$comment = $this->db->get_where('comments',array('comments.id'=>$id))->row();
			if(!$comment) return;
			$field = $this->link_field;
			$this->cache->tags($this->table.'/'.$comment->$field)->clear();
			if(trim($comment->path) == ''){
				$all_comments = array((array)$comment);
			}
			else {
				$field = $this->link_field;
				$this->db->like('path',$comment->path,'after');
				$all_comments = $this->db->get('comments')->result_array();
			}
			if($all_comments){
				$keys = array_keys(array4key($all_comments,'id'));
				$this->db->where_in('comments.id',$keys);
				$this->db->delete('comments');
				$this->db->where_in($this->links_table.'.cid',$keys);
				$this->db->delete($this->links_table);
				$field = $this->link_field;
			}
			$this->cache->clear('comments/'.$this->table.'/'.$comment->$field);
			$count = $this->db->where($this->link_field,$comment->$field)->count_all_results($this->links_table);
			$this->db->update($this->table,array('comments'=>$count),array('id'=>$comment->$field));
			$this->cache->tags('comments')->clear();
			return $this;
	}
	// ------------------------------------------------------------------------


	/**
	* Comments get simple query
	*
	* @return	object
	*/
	function _query(){
			$this->db->select('comments.*',FALSE);
			$this->db->select('users.name as author, users.url_name as author_url_name, users.avatar as avatar',FALSE);
			$this->db->join('users','comments.aid = users.id');
			return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Comments get extended query
	*
	* @return	object
	*/
	function _full_query(){
		$this->db->select('comments.id as cid, comments.body as body,comments.aid as caid, comments.ip as cip',FALSE);
		$this->db->select('u1.name as cauthor, u1.url_name as cauthor_url_name, u1.avatar as avatar',FALSE);
		$this->db->join('users u1','comments.aid = u1.id','inner');
		$this->db->join($this->links_table,'comments.id = '.$this->links_table.'.cid','inner');
		$this->db->join($this->table,$this->table.'.id = '.$this->links_table.'.'.$this->link_field,'inner');
		return $this;
	}
	// ------------------------------------------------------------------------

	/**
	* Get comments
	* @param	array
	* @param	string
	* @return	object
	*/
	function get($value,$field = 'comments.id'){
		if(is_array($value)) $this->db->where($value);
		else $this->db->where($field,$value);
		$this->query();
		return $this->db->get('comments');
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------