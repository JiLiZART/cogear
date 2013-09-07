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
 * Nodes controller
 *
 * @package		CoGear
 * @subpackage	Nodes
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		$this->no_title = TRUE;
	}
	// ------------------------------------------------------------------------

	/**
	* Check for new nodes
	*
	* @return	string
	*/
	function check(){
		$name = $this->input->post('data');
		$this->node->query();
		if($nodes = $this->db->order_by('id','desc')->where('MATCH (nodes.name) AGAINST("'.$name.'" IN BOOLEAN MODE)',FALSE,FALSE)->get('nodes',20)->result()){
			echo '<fieldset>';
			foreach($nodes as $node){
				echo $this->builder->a($node->name,$this->node->create_link($node),FALSE,FALSE,FALSE,'_blank').'<br>';
			}
			echo '</fieldset>';
		}
		ajax('',TRUE);
	}
	// ------------------------------------------------------------------------

	/**
	* Create and edit nodes
	*
	* @param	int
	* @return	void
	*/
	function createdit($id = FALSE){
		if(!acl('nodes create')) return _403();
		$this->no_sidebar = TRUE;
		d('nodes_createdit');
			$this->form->set('node_createdit');
			$this->form->key = FALSE;
			$this->form->input('name',array('validation'=>'required|min_length[3]|max_length[100]','js_validation'=>'required|length[3,100]','ajax'=>array('name'=>t('find_similar'),'url'=>'/nodes/check/','where'=>'name.after')));
			if($this->user->get('user_group') < 100 OR acl('nodes url_name')){
				$this->form->input('url_name',array('validation'=>'min_length[3]|max_length[100]','js_validation'=>'length[3,100]'));
			}
			$this->form->editor('body',array('validation'=>'required','js_validation'=>'required'));
			if(is_numeric($id)){
				$node = $this->node->get($id)->row();
				if($node->aid != $this->user->get('id') && !acl('nodes edit_all')) return _403();
				if(!$node) return _404();
				$this->form->data($node);
				$this->form->title('edit_post',-1,TRUE,TRUE);
				title($node->name,TRUE,TRUE);
				$this->form->set_values($node);
				$action = 'edit';
				if(acl('nodes delete') && $node->aid == $this->user->get('id') OR acl('nodes delete_all')){
				$can_delete =  TRUE;
				 $this->form->buttons('preview','save','publish','delete');
				}
				else {
				 $can_delete = FALSE;
				 $this->form->buttons('preview','save','publish');
				}
			}
			else {
				$this->form->title(t('create_new_node'),-1,TRUE,TRUE)->data();
				$action = 'create';
				$this->form->buttons('preview','save','publish');
			}
			if($result = $this->form->result()){
				$this->cache->tags('nodes/counter,nodes/views,nodes/count_all,users/'.$this->user->get('id'))->clear();
				$result['name'] = strip_tags(trim($result['name'],'.-'));
				$result['url_name'] = trim(!empty($result['url_name']) ? url_name($result['url_name']) : url_name($result['name']),'.-_!?');
				$result['aid'] = isset($node) ? $node->aid : $this->user->get('id');
				switch($result['action']){
					case 'save':
					$result['published'] = 'NULL';
					break;
					case 'publish':
					$result['published'] = 'true';
					break;
					case 'delete':
					$action = 'delete';
					break;
				}
				if($action == 'edit'){
				 $result['last_update'] = date('Y-m-d H:i:s');
				 if($result['action'] == 'delete') $action = 'delete';
				}
				// Remove drafts
				$this->cache->clear('drafts/'.$this->user->get('id'));

				if($action == 'create'){
					if($last = $this->db->order_by('id','desc')->get_where('nodes',array('aid'=>$this->user->get('id')),1)->row()){
						if($last->name == $result['name']){
							msg(t('prevent_double_post'),FALSE);
							$last->author = $this->user->get('name');
							$last->author_url_name = $this->user->get('url_name');
							redirect($this->node->create_link($last));
						}
					}
					$this->form->save('nodes',$result);
					$node = $this->node->get($this->form->insert_id)->row();
					redirect($this->node->create_link($node));
				}
				else if($action == 'edit' && $this->form->update('nodes',$result,array('id'=>$node->id))){
					$node->url_name = $result['url_name'];
					$this->cache->tags('nodes/'.$node->id)->clear();
					redirect($this->node->create_link($node));
				}
				else if($can_delete && $action == 'delete' && $this->form->delete('nodes',array('id'=>$node->id))){
					$this->cache->tags('nodes/'.$node->id)->clear();
					redirect('/user/'.$this->user->get('url_name'));
				}
			}
			else {
				$this->form->compile();
				js('/gears/nodes/js/inline/prevent.unload',FALSE,TRUE);
				if(!empty($this->gears->editor))
				{
					js('/gears/nodes/js/inline/draft',FALSE,TRUE);
					if($body = $this->cache->get('drafts/'.$this->user->get('id'),TRUE)){
						js('/gears/nodes/js/inline/draft.restore',FALSE,TRUE);
					}
				}
			}
	}
	// ------------------------------------------------------------------------

	/**
	* Show node
	*
	* @return	void
	*/
	function show($id = FALSE,$url_name = FALSE){
		$args = func_get_args();
		if(!$id){
			$request = func_get_args();
			$url = isset($this->gear->node->url) ? $this->gear->node->url : $this->gears->nodes->node;
			$count = substr_count($url,'%')/2;
			$request = array_slice($request,0,$count);
			preg_match_all('/%([\w]*)%/i',$url,$matches);
			$where = array_combine(array_add2values($matches[1],'nodes.'),$request);
		}
		else {
			$where = array('nodes.id'=>$id);
		}
		$this->node->query();
		$node = $this->db->get_where('nodes',$where)->row();
		//debug($this->db->last_query());
		if(empty($node)){
			if(!is_numeric($id)){
				$where = array('nodes.url_name'=>$id);
				$this->node->query();
				if(!$node = $this->db->get_where('nodes',$where)->row()){
					return _404();
				}
			}
			else return _404();
		}
		if(empty($node->published) && $node->aid != $this->user->get('id') && !acl('nodes view_drafts')) return _403();
		else $this->node->show($node,'full',10);
	}
	// ------------------------------------------------------------------------

	/**
	* Manage draft
	*
	* @param	string	action
	* @return	json
	*/
	public function draft($action = 'save'){
		if(!$this->user->is_logged()) return _403();
		d('nodes_draft');
		$msg = '';
		switch($action){
			case 'save':
				if($body = $this->input->post('body')){
					$this->cache->set('drafts/'.$this->user->get('id'),$body);
					if ($this->gears->nodes->draft_save_notify) $msg = t('saved');
					$success = TRUE;
				}
				else {
					$msg = t('saved_failure');
					$success = FALSE;
				}
			break;
			case 'load':
				if($body = $this->cache->get('drafts/'.$this->user->get('id'),TRUE)){
					$msg = t('loaded');
					$success = TRUE;
				}
				else {
					$msg = t('loaded_failure');
					$success = FALSE;
				}
			break;
		}
		echo json_encode(array('success'=>$success,'msg'=>$msg,'body'=>$body));
		exit();
	}

	/**
	 * Show nodes on index page
	 */
	public function index($page = 0){
		$this->nodes->get($page);
	}
}
// ------------------------------------------------------------------------