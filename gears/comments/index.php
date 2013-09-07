<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package   CoGear
 * @author      CodeMotion, Dmitriy Belyaev
 * @copyright   Copyright (c) 2009, CodeMotion
 * @license     http://cogear.ru/license.html
 * @link        http://cogear.ru
 * @since     Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Comments controller
 *
 * @package   CoGear
 * @subpackage  Comments
 * @category    Comments controllers
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
class Index extends Controller{
  /**
  * Constructor
  *
  * @return void
  */
  function __construct(){
    parent::Controller();
    $this->load->model('comments');
  }
  // ------------------------------------------------------------------------

  /**
  *  Post comments via ajax
  *
  * @return void
  */
  function post($target = 'nodes'){
    $this->comments->set($target);
    $this->comments->form(FALSE);
    if(count($_POST) === 0 OR !acl('comments add')){
      return _403();
    }
    elseif($result = $this->form->result()){
      if($result['body'] == $this->session->get('lastcomment') && !isset($result['id']) && $result['action'] == 'submit') ajax(FALSE,t('comments duplicate'));      
      $this->session->set('lastcomment',$result['body']);
      if($this->gears->comments->prevent_doubleposting_time){
        $lastcomment_time = $this->session->get('lastcomment/time');
        if(time() - $lastcomment_time < $this->gears->comments->prevent_doubleposting_time){
          ajax(FALSE,t('comments prevent_doubleposting',$this->gears->comments->prevent_doubleposting_time));
        }
        $this->session->set('lastcomment/time',time());     
      }
      if($this->comments->createdit($result)){
        ajax(TRUE,t('comments add_success'));
        remove('sidebar/widgets/comments');
      };
    }
    ajax(FALSE,t('comments add_failure').validation_errors('<p>','</p>'));
  }
  // ------------------------------------------------------------------------

  /**
  * Get comment body for edit via ajax
  *
  * @return void
  */
  function edit(){
    $id = $this->input->post('id');
    if(!acl('comments edit') OR !$id) ajax(FALSE);
    $this->session->set('comm_edit_id',$id);
    ajax(TRUE,$this->db->get_where('comments',array('id'=>$id))->row()->body);
  }
  // ------------------------------------------------------------------------

  /**
  *  Delete comment
  *
  * @param  mixed
  * @return void
  */
  function delete($type = 'nodes',$id = FALSE){
    if(!$id OR !acl('comments delete')) show404();
    $this->comments->set($type);
    $this->comments->delete($id);
    ajax(TRUE);
  }
  // ------------------------------------------------------------------------
  
  /**
  *  Destroy comment
  *
  * @param  mixed
  * @return void
  */
  function destroy($type = 'nodes',$id = FALSE){
    if(!$id OR !acl('comments destroy')) show404();
    $this->comments->set($type);
    $this->comments->destroy($id);
    ajax(TRUE);
  }
  // ------------------------------------------------------------------------

  /**
  * Show pulse (lastcomments)
  *
  * @param  int
  * @return void
  */
  function index($page = 0, $subpage = 0){
    $all = $this->db->join('comments_nodes cn',"cn.cid = {$this->db->dbprefix}comments.id")->count_all_results('comments');
    if(!is_numeric($page)){
      $name = $page;
      $user = $this->user->info($page);
      $this->user->head($user,'comments');
      $trap = TRUE;
      $this->db->join('comments c','c.aid = '.$user->id." AND {$this->db->dbprefix}comments_nodes.cid = c.id",'inner');
      $page = $subpage;
      title($user->name);
    }
    $page = $this->pager($page, $this->db->count_all_results('comments_nodes',TRUE));
    if(!empty($trap)) $this->db->join('comments c','c.aid = '.$user->id." AND {$this->db->dbprefix}comments_nodes.cid = c.id",'inner');
    else {
      $this->builder->h1(t('gears comments').(isset($name) ? ' '.$name.' ' : '').$this->builder->sup($this->db->where('comments.created_date > ',date('Y-m-d'))->join('comments_nodes cn','cn.cid = comments.id')->count_all_results('comments').'/'.$all),TRUE);
    }
    $this->db->join('nodes','comments_nodes.nid = nodes.id','inner')->where('nodes.published IS NOT NULL');
    $comments_nodes = $this->db->select('comments_nodes.id')->order_by('id','desc')->get('comments_nodes',$page['limit'],$page['start'])->result();
    if($comments_nodes){
    $keys = array();
    foreach($comments_nodes as $cn){
      $keys[] = $cn->id;
    }
    $this->db->select('comments.id as comment_id, comments.*, users.id as aid, users.name as cauthor, users.avatar, users.url_name as cauthor_url_name, author.name as author, author.url_name as author_url_name,nodes.id, nodes.name, nodes.url_name, nodes.comments,nodes.cid, comments.body')
    ->join('nodes','comments_nodes.nid = nodes.id','inner')
    ->join('comments','comments_nodes.cid = comments.id','inner')
    ->join('users','users.id = comments.aid','inner')
    ->join('users author','nodes.aid = author.id');
    if($this->gears->community){
      if(!acl('community view_private')){
      $where = 'community.private IS NULL';
      }
      if($this->community->roles){
        $keys = array_keys($CI->community->roles);
        $where = '( '.$where.' OR community.id IN ('.implode(',',$keys).') )';
      }     
      if(!empty($where)) $this->db->where($where);
     $this->db->select('community.id as comm_id, community.name as comm_name, community.url_name as comm_url_name')
     ->join('community','community.id = nodes.cid','left');
    } 
    $this->db->where_in('comments_nodes.id',$keys);
    $output = array();
    $comments = $this->db->get('comments_nodes')->result();
    foreach($comments as $key=>&$comment){
      $this->breadcrumb->set('comments_widget')->data($comment);
      // This thing will be avatar in allcomments page. Do not delete.
      $comment->body = $this->parser->parse($comment->body,'textarea');
      $this->breadcrumb->add('');
      $avatar = reset(make_icons($comment->avatar));
      $link = $this->node->create_link($comment,'#comment-'.$comment->comment_id);
      $this->breadcrumb->add(' <img src="'.$avatar.'" class="avatar" alt="'.$comment->cauthor.'"> <a href="'.l('/user/'.$comment->cauthor_url_name).'">'.$comment->cauthor.'</a> &rarr; ');
      $this->breadcrumb->add('<a href="'.l('/blogs/'.$comment->author_url_name).'">'.t('blogs blog').' '.$comment->author.'</a>');
      $this->breadcrumb->add(' / <a href="'.$link.'">'.$comment->name.'</a>');
      $this->breadcrumb->add('<img src="/gears/comments/img/icon/comments.png" alt="'.t("comments comment_many").'"> <span>'.$comment->comments.'</span>');
      $this->breadcrumb->add(' <a href="'.$link.'">#'.$comment->comment_id.'</a>');
      $this->breadcrumb->add('<br>');
      $comment->header = $this->breadcrumb->compile();
      $output[$comment->comment_id] = (array)$comment;
    }
    krsort($output);
    $node = new stdClass();
    $node->id = 0;
    $node->comments = 0;
    $this->comments->set('nodes',$node);
    $this->_template('comments comments',array('comments'=>$this->comments->show($output,TRUE,'plain'),'wrapper'=>TRUE,'type'=>'plain'),100);
    }
    else {
      info();
    }
  }
  // ------------------------------------------------------------------------
  
  /**
  * Get comments by ajax request
  * 
  * @param  int Node id
  * @return json
  */
  function get($type = 'nodes', $id = FALSE){
    if(!$id) ajax(FALSE);
    else {
      $link_field = substr($type,0,1).'id';
      $node = new stdClass();
      $node->id = $id;
      $node->comments = 1;
      $this->comments->set($type,$node);
      $comments_count = 0;
      if($old_comments = $this->input->post('comments')){
        $old_comments = explode(',',$old_comments);
        $edit_id = $this->session->get('comm_edit_id');
        if($edit_id && in_array($edit_id,$old_comments)){
          unset($old_comments[array_search($edit_id,$old_comments)]);
          $this->session->remove('comm_edit_id');
        }
        if(!empty($old_comments)){
          $comments_count += count($old_comments);
          $this->db->where_not_in('comments.id',$old_comments);
        }
      }
      $this->comments->query();
      $this->db->order_by('comments.path','asc');
      if($comments = $this->db->join('comments_'.$type,'comments_'.$type.'.cid = comments.id AND comments_'.$type.'.'.$link_field.'= '.$id)->get('comments')->result_array()){
          $comments = array4key($comments,'id');
          $this->comments->comments_views($comments,count($old_comments));
          foreach($comments as $key=>&$comment){
            $comment['class'] = 'new';
            $comment = $this->comments->process($comment,'tree');
            $path = explode('.',trim($comment['path']));
            if(count($path) > 1){
              array_pop($path);
              $comment['parent'] = array_pop($path);
            }
            else {
              $comment['parent'] = FALSE;
            }
            $output[] = array(
            'id' => $comment['id'],
            'parent'=>$comment['parent'],
            'code'=>$this->_template('comments comment',array('comment'=>$comment,'type'=>'tree'),TRUE),
            'replace' => !empty($edit_id) && $comment['id'] == $edit_id ? $edit_id : FALSE
            );
          }
          $comments_count += count($comments);
      }
      echo json_encode(empty($output) ? array('success' => FALSE) : array('success' => TRUE,'comments'=>$output,'count'=>$comments_count,'new_count'=>count($comments)));
      exit();
    }
  }
}
// ------------------------------------------------------------------------