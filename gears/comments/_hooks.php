<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package  CoGear
 * @author   CodeMotion, Dmitriy Belyaev
 * @copyright  Copyright (c) 2009, CodeMotion
 * @license   http://cogear.ru/license.html
 * @link    http://cogear.ru
 * @since   Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Comments hhoks
 *
 * @package  CoGear
 * @subpackage Comments
 * @category  Gears hooks
 * @author   CodeMotion, Dmitriy Belyaev
 * @link    http://cogear.ru/user_guide/
 */
/**
 *  Add link to user comments into userinfo panel
 *
 * @param object  $Panel
 * @return void
 */
function comments_panel_compile_($Panel)
{
  $CI =& get_instance();
  if ($Panel->name == 'userinfo_tabs') {
    $count = $CI->cache->get('counters/comments/'.$Panel->data->id, TRUE);
    if ($count === FALSE) {
      $CI->db->swop();
      $CI->db->join('comments_nodes cn', 'cn.cid = comments.id', 'inner');

      $CI->db->join('nodes n', 'cn.nid = n.id', 'inner');
      $CI->db->where('n.published IS NOT NULL');
      $count = $CI->db->where('comments.aid', $Panel->data->id)->count_all_results('comments', FALSE);    
      $CI->db->swop();
      $CI->cache->tags('users/'.$Panel->data->id)->set('counters/comments/'.$Panel->data->id, $count);
    }
    $count = $count > 0 ? ' ('.$count.')' : '';
    $CI->userinfo_tabs->add(array('name'=>'pulse', 'text'=>fc_t('gears comments').$count, 'link'=>l('/comments/'.$Panel->data->url_name)));
    if ($CI->name == 'comments') {
      $Panel->set_active('pulse');
    }
  }
}
/**
 * Add new comments info
 *
 * @param object  $Node
 * @param object  $node
 * @return void
 */
function comments_node_show_($Node, $node)
{
  $CI =& get_instance();
  if ($CI->user->get('id')) {
    $cnv = $CI->session->get('comments_nodes_views', TRUE);
    if (isset($cnv[$node->id])) $node->last_comments = $cnv[$node->id]['count'];
  }
}
// ------------------------------------------------------------------------

/**
 * Refresh user comments view after refresh him
 *
 * @param object  $User
 * @param boolean $result
 * @return void
 */
function comments_user_refresh_after_($User, $result)
{
  if ($result && $User->get('id')) {
    $CI =& get_instance();
    $cnv = array4key($CI->db->get_where('comments_nodes_views', array('uid'=>$User->get('id')))->result_array(), 'nid');
    $CI->session->set('comments_nodes_views', $cnv);
  }
}
// ------------------------------------------------------------------------


/**
 * Add comments count and unseen comments count to node info breadcrumb
 *
 * @param object  $Breadcrumb
 * @return void
 */
function comments_breadcrumb_compile_($Breadcrumb)
{
  $CI =& get_instance();
  if ($Breadcrumb->name == 'node_info') {
    $node =& $Breadcrumb->data;
    $href = $CI->node->create_link($node, '#comments');
    //$Panel->data->comments
    $link = $CI->node->create_link($node, '#comments');
    $Breadcrumb->add('<a href="'.$link.'"><img src="/gears/comments/img/icon/comments.png" alt="comments"></a> <a href="'.$link.'" class="comments_counter">'.$node->comments.'</a>', 100);
    if (isset($node->last_comments) && $node->last_comments > 0 && $node->comments > $node->last_comments) {
      $Breadcrumb->add('<a class="new_comments" href="'.$CI->node->create_link($node, '#comments').'">+'.($node->comments - $node->last_comments).'</a>', 101);
    }
  }
}
// ------------------------------------------------------------------------

/**
 * Show comments after node
 *
 * @param object
 * @param mixed
 * @param object
 * @param string
 * @return void
 */
function comments_node_show_after_($Node, $result, $node, $type)
{
  if ($type == 'full') {
    $CI =& get_instance();
    $CI->load->model('comments comments');
    $CI->comments->set('nodes', $node);
    $CI->_template('comments comments', array('comments'=>$CI->comments->show(FALSE, TRUE), 'wrapper'=>TRUE, 'type'=>'tree'), 100);
    $CI->comments->form();
  }
}

// ------------------------------------------------------------------------

// ------------------------------------------------------------------------