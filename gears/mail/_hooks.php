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
 * Mail hooks
 *
 * @package   CoGear
 * @subpackage  Mail
 * @category    Gears hooks
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
  /**
  * Set mail icon to user cpanel
  *
  * @param  object
  * @return void
  */
  function mail_header($CI){
	if(!$CI->user->get('id')) return;
	$uid = $CI->user->get('id');
	$pm_count = $CI->user->get('pm');
	//$CI->name == 'mail' && $CI->method == 'index' OR
	//if($CI->user->get('id') == 1) debug($pm_count);
	if($pm_count === FALSE){
	//if(TRUE){
	  if($CI->gears->mail->mode == 'line'){
		$pm_count = $CI->db->where(array('to'=>$CI->user->get('id'),'is_read'=>NULL,'owner'=>'to'))->count_all_results('pm');
	  }
	  elseif($CI->gears->mail->mode == 'inbox'){
		$pm_count = $CI->db->where(array('owner'=>'to'))->where('FIND_IN_SET('.$uid.','.$CI->db->dbprefix('pm.to').') != 0')->where('FIND_IN_SET('.$uid.',has_read) = 0')->count_all_results('pm');

		$prefix = $CI->db->dbprefix;

		if($comments = $CI->db
		->select("({$prefix}p.comments - {$prefix}cpv.count) as num")
		->join("comments_pm_views {$prefix}cpv","cp.pid = cpv.pid")
		->join("comments {$prefix}c",'c.id = cp.cid')
		->join("pm {$prefix}p",'p.id = cpv.pid')
		->where('cpv.uid',$uid)
		->where("cpv.count < {$prefix}p.comments")
		->where('p.owner = "to"')
		->where('c.aid !=',$uid)
		->where('cpv.uid',$uid)
		->group_by('p.id')
		->get("comments_pm {$prefix}cp")->result()){
		  foreach($comments as $comment){
			$pm_count += $comment->num;
		  }
		}
	  }
	  $CI->user->set('pm',$pm_count);
	  if($CI->name != 'mail' && $pm_count) msg(t('mail you_have_recieved',$pm_count,declOfNum($pm_count,'new_msg')),'gears mail');
	}
	$pm = array(
	'data' => (int)$pm_count,
	'type'=>'text',
	'class'=>'cpanel-mail',
	'link'=>l("/{$CI->gears->mail->url}/"),
	);
	$pm['text'] = '<span style="white-space: nowrap;">'.t('mail new_msg',$pm['data']).'</span>';
	$CI->cpanel->add($pm,2);
  }
  // ------------------------------------------------------------------------

/*
  function mail_user_refresh_after_($User,$result){
	if($result && $User->get('id')){
	  $CI =& get_instance();
	  $cpv = array4key($CI->db->get_where('comments_pm_views',array('uid'=>$User->get('id')))->result_array(),'pid');
	  $CI->session->set('comments_pm_views',$cpv);
	}
  }
*/

  /**
  * Send mail to pm addressee
  *
  * @param  object
  * @param  object
  * @param  array
  * @return void
  */
  function mail_pm_sent_success_($Pm,$user,$data){
	$CI =& get_instance();
	$data['id'] = $CI->pm->insert_id;
	$data['author_url'] = $CI->user->get('url_name');
	$data['author'] = $CI->user->get('name');
	$data['user'] = $user->name;
	$CI->mail->send($user,FALSE,FALSE,t('mail new_pm_subject'),'new.pm',$data);
  }
  // ------------------------------------------------------------------------

  /**
   * Send email to inbox reciepients
   */
  function inbox_pm_sent_success_($Pm,$users,$data){
	$CI =& get_instance();
	$data['id'] = $CI->pm->insert_id;
	$data['author_url'] = $CI->user->get('url_name');
	$data['author'] = $CI->user->get('name');
	foreach($users as $uid){
	  if($user = $CI->user->get($uid)){
		$data['user']  = $user->name;
		$CI->mail->send($user,FALSE,FALSE,t('mail new_pm_subject'),'new.inbox',$data);
	  }
	}
  }

  /**
  * Add mailto-icon to user page breadcrumb
  *
  * @param  object
  * @return void
  */
  function mail_breadcrumb_compile_($Breadcrumb){
	$CI =& get_instance();
	if(!$CI->user->get('id')) return;
	if($Breadcrumb->name == 'userinfo_panel' && $Breadcrumb->data->id != $CI->user->get('id')){
	  $Breadcrumb->add('<a href="'.l('/'.$CI->gears->mail->url.'/create/'.$Breadcrumb->data->url_name).'"><img src="/gears/mail/img/icon/mail.png" title="'.t('mail send_message').'"/></a>',2);
	}
  }
  // ------------------------------------------------------------------------


  /**
  * Send mail after comments posting - to node author, to reply comment author
  *
  * @param  object
  * @param  array
  * @param  array
  * @return void
  */
  function mail_comments_createdit_after_($Comments,$result,$data){
	$CI =& get_instance();
	if($result && isset($data[$Comments->link_field]) && isset($CI->comments->insert_id)){
	  switch($Comments->table){
		case 'nodes':
		  $item = $CI->node->get($data['nid'])->row();
		  break;
		case 'pm':
		  $item =   $CI->db
		  ->select('p.*, u.id as aid, u.name as aname, u.url_name as aurl_name',FALSE)
		  ->join('users u','p.from = u.id')
		  ->where('p.id',$data['pid'])
		  ->get('pm p')
		  ->row();
		  $CI->db->update('pm',array('last_update'=>date('Y-m-d H:i:s')),array('id'=>$item->id));
		  break;
	  }
	  if(isset($data['parent-id'])){
		if(!$item) return;
		$comment = $CI->comments->get($data['parent-id'])->row();
		$comment = $CI->comments->process($comment);
		$comment->body = strip_tags($comment->body,'<br/><br>');
		if(!$comment) return;
		$author = $CI->user->info($comment->author);
		if($author->id != $CI->user->get('id')) $CI->user->refresh($author->id);
		if($CI->gears->mail->mail_comment_reply){
		  $reply = $CI->comments->get($CI->comments->insert_id)->row();
		  $reply = $CI->comments->process($reply);
		  $reply->item = $item;
		  $reply->original = $comment->body;
		  if($comment->aid == $reply->aid) return;
		  switch($Comments->table){
			case 'nodes':
			  $reply->link = $CI->node->create_link($item,'#comment-'.$reply->id);
			  $CI->mail->send($author,FALSE,FALSE,t('mail reply_comment_subject',$item->name),'comments/nodes/comment.reply',$reply);
			  break;
			case 'pm':
			  $reply->link = l('/'.$CI->gears->mail->url.'/read/'.$item->id,'#comment-'.$reply->id);
			  $CI->mail->send($author,FALSE,FALSE,t('mail reply_comment_inbox_subject',$item->subject),'comments/pm/comment.reply',$reply);
			  break;
		  }
		}
	  }
	  if($Comments->table == 'pm'){
		$users = _explode(',',$item->to.','.$item->from);
		foreach($users as $user){
		  $CI->user->refresh($user);
		}
		$CI->user->refresh();
	  }

	  if(isset($item) AND $item->aid != $CI->user->get('id')) $CI->user->refresh($item->aid);
	  if($CI->gears->mail->mail_comment_post_author){
		if(!isset($item) OR !$item OR !isset($CI->comments->insert_id)) return;
		$comment = $CI->comments->get($CI->comments->insert_id)->row();
		if(!$comment) return;
		  $author = $CI->user->info($item->aid);
		$comment = $CI->comments->process($comment);
		//$comment->body = strip_tags($comment->body,'<br/><br>');
		// If node author is also comment author - return
		if($comment->aid == $author->id) return;
		if(isset($data['parent-id'])){
		  $original = $CI->comments->get($data['parent-id'])->row();
		  if($original->aid == $item->aid) return;
		  $original = $CI->comments->process($original);
		  $original->body = strip_tags($original->body,'<br/><br>');
		  $comment->original = $original;
		}
		$comment->item = $item;
		switch($Comments->table){
		  case 'nodes':
			$comment->link = $CI->node->create_link($item,'#comment-'.$comment->id);
			$CI->mail->send($author,FALSE,FALSE,t('mail comment_node_author_subject',$item->name),'comments/nodes/comment.node.author',$comment);
			break;
		  case 'pm':
			$comment->link = l('/'.$CI->gears->mail->url.'/read/'.$item->id,'#comment-'.$comment->id);
			$CI->mail->send($author,FALSE,FALSE,t('mail comment_inbox_author_subject',$item->subject),'comments/pm/comment.inbox.author',$comment);
			break;
		}
	  }
	}
  }
  // ------------------------------------------------------------------------
// ------------------------------------------------------------------------