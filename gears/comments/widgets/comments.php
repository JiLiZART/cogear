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
 * Comments widget
 *
 * @package   CoGear
 * @subpackage  Comments
 * @category    Gears widgets
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
  /**
  * Process comments widget
  *
  * @param  object
  * @param  array
  * @return mixed
  */
  function comments_widget($CI,$config){
      $comments = retrieve('sidebar/widgets/comments');
      if(!$comments){
        $comments_nodes = $CI->db->select('MAX(cid) as id')->group_by('nid')->order_by('id','desc')->get('comments_nodes',$config->num)->result();
        if($comments_nodes){
          $keys = array();
          foreach($comments_nodes as $cn){
            $keys[] = $cn->id;
          }
          $CI->db->select('comments.id as comment_id, users.name as cauthor, users.url_name as cauthor_url_name, author.name as author, author.url_name as author_url_name, nodes.name, nodes.url_name, nodes.comments, nodes.id, nodes.cid')
          ->join('comments','comments_nodes.cid = comments.id')
          ->join('users','users.id = comments.aid')
          ->join('nodes','comments_nodes.nid = nodes.id AND nodes.published IS NOT NULL')
          ->join('users author','nodes.aid = author.id');
          if($CI->gears->community){
           $CI->db->select('community.id as comm_id, community.name as comm_name, community.url_name as comm_url_name')
           ->join('community','community.id = nodes.cid','left');
          }
          
          $comments = $CI->db->where_in('comments_nodes.cid',$keys)->get('comments_nodes')->result();

          $output = array();
          foreach($comments as $comment){
            $CI->breadcrumb->set('comments_widget')->data($comment);
            // This thing will be avatar in allcomments page. Do not delete.
            $CI->breadcrumb->add('');
            $CI->breadcrumb->add(' <a href="'.l('/user/'.$comment->cauthor_url_name).'" class="user">'.$comment->cauthor.'</a> &rarr; ');
            $CI->breadcrumb->add('<a href="'.l('/blogs/'.$comment->author_url_name).'">'.t('!blogs blog').' '.$comment->author.'</a>');
            $CI->breadcrumb->add(' / <a href="'.$CI->node->create_link($comment).'">'.$comment->name.'</a>');
            $CI->breadcrumb->add('<a class="comm_num" href="'.$CI->node->create_link($comment,'#comment-'.$comment->comment_id).'">'.$comment->comments.'</a>');
            $CI->breadcrumb->add('<br>');
            $output[$comment->comment_id] = $CI->breadcrumb->compile();
          }
          krsort($output);
          $output = implode('',$output);
          $CI->builder->stop();
          $output .= $CI->builder->div($CI->builder->a(t('!comments all_comments'),l('/comments/')).'<a href="'.l('/rss/comments/','/',FALSE,TRUE).'"><img src="/gears/syndication/img/icon/rss.png" alt="RSS"></a>','tright');
          $comments = $CI->builder->div($output,FALSE,'comments_widget');
        }
        else $comments = $CI->builder->div(t('!errors empty'),'centered');
        store('sidebar/widgets/comments',$comments,$config->time,'comments');
      }
      return $comments;
  }
  // ------------------------------------------------------------------------
// ------------------------------------------------------------------------