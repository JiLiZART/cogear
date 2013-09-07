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
 * Community model
 *
 * @package   CoGear
 * @subpackage  Community
 * @category    Gears models
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
class Community extends Model{
  public $info = TRUE;
  public $roles = array();
  /**
  * Constructor
  *
  * @return void
  */
  function Community(){
    parent::Model();
  }
  // ------------------------------------------------------------------------

  /**
  * Show community nodes, members, invite page
  *
  * @param  string
  * @param  int
  * @param  int
  * @return void
  */
  function show($url_name,$page = 0, $expage = 0){
    $CI =& get_instance();
    // get community
    $community = $this->get($url_name);
    if(!$community) {
      return _404();
    }
    title($community->name);
    
	if(!empty($community->keywords)) meta($community->keywords,'keywords',TRUE);
	if(!empty($community->description)) meta($community->description,'description',TRUE);

    if($this->info == TRUE){
    // set community header breadcrumb
    $info = $CI->breadcrumb;
    $info->set('community_header')->data($community)->wrapper();
    if($community->icon)
    {
      $community->icon = make_icons($community->icon);
      $info->add('<a href="'.$community->icon['original'].'" target="_blank"><img src="'.$community->icon['24x24'].'" height="24" class="avatar"></a>');
    }
    $info->add('<h1><a href="'.l('/community/'.$community->url_name).'" id="community-'.$community->id.'">'.$community->name.'</a></h1>');
    $info->add('<span class="members">(<a href="'.l('/community/'.$community->url_name.'/members').'">'.$community->users_num.'</a>)</span>');
    if($this->user->get('id')){
      $role = $this->check($community->id);
      switch($role){
        case 'admin':
          $info->add('<a href="#admin"><img class="role" src="/gears/community/img/icon/admin.png" title="'.t('!community '.$role).'"></a>'); 
          $info->add('<a href="'.l('/community/edit/'.$community->url_name,'/',FALSE,TRUE).'"><img src="/gears/community/img/icon/settings.png" title="'.t('%settings').'"></a>'); 
        break;
        case 'member':
          $info->add('<a href="#change_role"><img class="role" src="/gears/community/img/icon/leave.png" title="'.t('!community leave').'"></a>');
        break;
        default:
          if(!$community->private && !$community->invites_only) $info->add('<a href="#change_role"><img class="role" src="/gears/community/img/icon/join.png" title="'.t('!community join').'"></a>');
      }
      if($role != "admin" && $this->user->get('id') == 1){
                $info->add('<a href="'.l('/community/edit/'.$community->url_name,'/',FALSE,TRUE).'"><img src="/gears/community/img/icon/settings.png" title="'.t('%settings').'"></a>'); 
      }
      if($community->private){
        $info->add('<img src="/gears/community/img/icon/private.png" title="'.t('!community private').'">');
      }
      else if($community->invites_only){
        $info->add('<img src="/gears/community/img/icon/invites_only.png" title="'.t('!community invites_only').'">');
      }
      //($community->private OR $community->invites_only) && (
      if(($this->check($community) == 'admin' OR $this->check($community) && acl('community invite_private'))){
        $info->add('<a href="'.l('/community/'.$community->url_name.'/invite/').'"><img src="/gears/community/img/icon/invite.png" title="'.t('!community invite').'"></a>');
      }   
    }
    if(trim($community->description) != ''){
     $info->add('<div class="clear padding-10">'.$community->description.'</div>',30);
    }
    $info->compile(2,6);
    $this->builder->div('','clear',7);
    }
    // If community is private and user doesn't have access
    if($community->private && !$this->check($community)){
      info(t('!community private_restricted'),FALSE,3,'all');   
    }
    // Show members list
    else if($page === "members"){
      $this->get_members($community,FALSE);
      $page = $CI->pager($expage,$this->db->count_all_results('community',FALSE),array('per_page'=>$CI->gear->per_page_users ? $CI->gear->per_page_users : $CI->site->per_page));
      $this->db->limit($page['limit'],$page['start']);
      $header = array(
      'avatar'=>array('','image','5%','class'=>'avatar'),
      'name'=>array(fc_t('!user name'),'link','30%',FALSE,'before'=>'<h1>','after'=>'</h1>'),
      'role_icon'=>array(fc_t('!community role'),'image','20%'),
      );
      if(acl('community kick') && $this->check($community) == 'admin' OR acl('community kick_any')){
        $header['delete'] = array(fc_t('!community kick'),'checkbox','5%');
      }
      $info = array(
      'link'=>array('/user'),
      'link_add'=>array('url_name'),
      'primary'=>'id',
      'noname'=>TRUE
      );
      $members = $this->db->get('community')->result_array();
      foreach($members as &$member){
        $member['avatar'] = make_icons($member['avatar']);
        $member['title'] = t('!community '.$member['role']);
        if($member['role'] == 'admin') $info['undel'][] = $member['id'];
        $member['role_icon'] = '/gears/community/img/icon/'.$member['role'].'.png';
      }
      if($this->input->post('delete')){
        $this->cache->clear('community/id/'.$community->id);
        $this->cache->clear('community/url_name/'.$community->url_name);
      }
      $CI->form->set('community_users')->title(fc_t('!community members'),-100,FALSE,TRUE)
      ->grid('community_users',$header,$members,$info)
      ->compile(8);     
    }
    // Show invites page
    else if($page === "invite"){
      if($community->private && (!$this->check($community) == 'admin' OR !acl('community invite_private'))) _404();
      d('community');
      $CI->form->set('community_invite')
      ->title('invite',FALSE,FALSE,TRUE)->fieldset('invites')
      ->input('invite_users',array('js_validation'=>'required','validation'=>'required'))
      ->buttons('send')
      ->compile(8);     
      if($result = $CI->form->result()){
        $CI->pm->redirect = FALSE;
        $CI->pm->set('community_invite')->data($community)->send($result['invite_users'],FALSE,t('invite_subject'),t('invite_body',l('/community/'.$community->url_name),$community->name),TRUE);
      }
      d();
    }
    // Show nodes
    else if(is_numeric($page) OR $page === FALSE) {
      $CI->db->where('community.url_name',$url_name);
      $CI->nodes->get((int)$page,8,TRUE);
    }
  }
  // ------------------------------------------------------------------------

  /**
  * Get community members
  *
  * @param  mixed
  * @param  boolean
  * @return object
  */
  function get_members($community,$execute_query = TRUE){
      if(is_numeric($community)){
        $this->db->where('community.id',$community);
      }
      else if(is_string($community)){
        $this->db->where('community.url_name',$community);
      }
      else if(is_object($community)){
        $this->db->where('community.id',$community->id);
      }
      $this->db->select('users.id,users.name,users.url_name,users.avatar,community_users.role,community_users.id',FALSE);
      $this->db->join('community_users','community_users.cid = community.id','inner');
      $this->db->join('users','community_users.uid = users.id','inner');
      $this->db->where('approved','true');
      return $execute_query ? $this->db->get('community') : $this->db;
  }
  // ------------------------------------------------------------------------

  /**
  * Get community by param 
  *
  * @param  mixed
  * @return object
  */
  function get($param){
      if(is_numeric($param)){
        if(!$community = $this->cache->get('community/id/'.$param,TRUE)){
          $this->db->where('community.id',$param);
        }       
      }
      else if(is_string($param)){
        if(!$community = $this->cache->get('community/url_name/'.$param,TRUE)){
          $this->db->where('community.url_name',$param);
        }
      }
      if(!isset($community) OR !$community){
        if($community = $this->query()->get('community')->row()){
          foreach(array('id','url_name') as $var){
            $this->cache->tags('community/'.$community->id)->set('community/'.$var.'/'.$param,$community);
          }
        }
      }   
      return $community;  
  }
  // ------------------------------------------------------------------------
  
  /**
  *  Community query
  *
  * @return object
  */
  function _query(){
      $prefix = $this->db->dbprefix;
      $this->db->select('community.*');
      $this->db->join('community_users',"community.id = community_users.cid AND {$prefix}community_users.role = 'admin'",'left');
      $this->db->select('users.name as aname,users.url_name as aurl_name,users.avatar as uavatar');
      $this->db->join('users',"users.id = {$prefix}community_users.uid");
      $this->db->select("(SELECT COUNT(id) FROM {$prefix}community_users WHERE cid = {$prefix}community.id && approved = true) as users_num",FALSE);
      $this->db->select("(SELECT COUNT(id) FROM {$prefix}nodes WHERE cid = {$prefix}community.id) as nodes_num",FALSE);
      return $this->db;
  }
  // ------------------------------------------------------------------------
  
  /**
  * Refresh user data
  *
  * @param  integer User id.
  * @return void
  */
  function refresh_user($uid = FALSE){
    if(!$uid){
      $CI =& get_instance();
      $uid = $CI->user->get('id');
    }
    $this->cache->clear('community_roles/'.$uid);
  }
  
  /**
  * Flush community cache
  *
  * @param  mixed
  * @return void
  */
  public function flush($community){
    if(is_numeric($community) OR !is_object($community)){
      $community = $this->get($community);
    }
    $this->cache->tags('community')->clear();
    $this->cache->clear('community/id/'.$community->id);
    $this->cache->clear('community/url_name/'.$community->url_name);
  }
  
  /**
  * Get roles for current user from db
  *
  * @param  integer User id.
  * @return array
  */
  function get_roles($uid = FALSE){
    $CI =& get_instance();
    if($uid === FALSE){
      if($this->roles) return $this->roles;
      
      $uid = $CI->user->get('id');
    }
    if(!$roles = $this->cache->get('community_roles/'.$uid,TRUE)){
      $this->db->select('community_users.role,community_users.id as cuid,community.*',FALSE);
      $this->db->join('community','community.id = community_users.cid','inner');
      $this->db->where('approved','true');
      $roles = array4key($this->db->get_where('community_users',array('uid'=>$uid))->result_array(),'id');
      if($uid == $CI->user->get('id')){
         $this->roles = $roles;
      }
      $this->cache->set('community_roles/'.$uid,$roles);
    }
    return $roles;
  }
  // ------------------------------------------------------------------------
  
  /**
  * Check user role in community
  *
  * @param  mixed
  * @return mixed
  */
  function check($param){
    $roles = $this->roles ? $this->roles : $this->get_roles();
    foreach($roles as $role){
      if(is_numeric($param)){
        if($role['id'] == $param) return $role['role'];
      }
      else if(is_string($param)){
        if($role['url_name'] == $param) return $role['role'];       
      }
      else if(is_object($param)){
        if($role['id'] == $param->id) return $role['role'];       
      }
    }
    return FALSE;
  } 
  // ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------