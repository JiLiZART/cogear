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
 * Mail controller
 *
 * @package   CoGear
 * @subpackage  Mail
 * @category    Gears controllers
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
     if(!$this->user->is_logged()) return _403();
    d('mail');
    $this->panel->links_base = "/{$this->gears->mail->url}/";
    $this->panel->name = 'mail';
    $this->panel->template = 'global tabs';
    if($this->gear->mode == 'mail'){
      $this->panel->add(array('name'=>'inbox','text'=>t('inbox').' ('.$this->db->where(array('to'=>$this->user->get('id'),'owner'=>'to'))->count_all_results('pm').') ','index'=>TRUE));
      $this->panel->add(array('name'=>'outbox','text'=>t('outbox').' ('.$this->db->where(array('from'=>$this->user->get('id'),'owner'=>'from'))->count_all_results('pm').') '));
      $this->panel->add(array('name'=>'create','text'=>t('edit create')));
      $method = isset($this->uri->segments[2]) ? $this->uri->segments[2] : 'inbox';
      }
      else {
        $uid = $this->user->get('id');                                          
        $count = $this->db->where('owner','to')->where('(FIND_IN_SET('.$uid.','.$this->db->dbprefix.'pm.to) OR ('.$this->db->dbprefix.'pm.from = '.$uid.' AND system IS NULL))')->count_all_results('pm');
      $this->panel->add(array('name'=>'read','text'=>t('messages').' ('.$count.') ','index'=>TRUE));
      $this->panel->add(array('name'=>'create','text'=>t('edit create')));
      $method = isset($this->uri->segments[2]) && method_exists($this,$this->uri->segments[2]) ? $this->uri->segments[2] : 'read';
      }
    if(!in_array($method,array('view','reply'))){
     $this->panel->set_active($method);
     $this->panel->compile(20); 
    }
  }
  // ------------------------------------------------------------------------

  /**
  * Redirect from index page to 'inbox'
  *
  * @return void
  */
  function index($page = 0){
    if(!$this->user->is_logged()) return _403();
    if($this->gear->mode == 'line'){
      redirect("/{$this->gears->mail->url}/inbox/");
    }
    $this->_template('search');
      $uid = $this->user->get('id');
    $cpv = $this->session->get('comments_pm_views',TRUE);
    if($query = $this->input->get('query')){
      $query = trim($query);
      if($user = $this->user->info($query)){
        $this->db->where('FIND_IN_SET('.$user->id.',pm.to) != ',0);
      }
      else {
        $this->db->like('body',$query,'both');
        $this->db->or_like('subject',$query,'both');
      }
    }
    $this->db->where('owner','to')->where('(FIND_IN_SET('.$uid.",{$this->db->dbprefix}pm.to) OR ({$this->db->dbprefix}pm.from = ".$uid.' AND system IS NULL))');
    $page = $this->pager($page, $this->db->count_all_results('pm',FALSE));
    $result = $this->db->order_by('pm.last_update','desc')->limit($page['limit'],$page['start'])->get('pm')->result_array();
    foreach($result as $key=>&$msg){
        $msg['recipients'] = '';
        $recipients = explode(',',$msg['to']);
        $recipients[] = $msg['from'];
        $recipients = array_unique($recipients);
        
        foreach($recipients as $user_id){
          if($user = $this->user->info($user_id)){
            $msg['recipients'][] = $this->user->get('id') == $user_id ? t('i') : '<a href="'.l('/user/'.$user->url_name).'">'.$user->name.'</a>';
          }
        }
        
        switch(count($recipients)){
          case 1:
            $msg['recipients'] = implode($msg['recipients']);
            break;
          case 2:
            $msg['recipients'] = implode(t('and'),$msg['recipients']);
            break;
          default:
            $last = array_pop($msg['recipients']);
            $msg['recipients'] = implode(', ',$msg['recipients']).' '.t('and').' '.$last;
        }
          if(isset($cpv[$msg['id']])){
          $msg['last_comments'] = $cpv[$msg['id']]['count'];
            if($msg['comments'] > $msg['last_comments']){
              $msg['new_comments'] = $msg['comments'] - $msg['last_comments'];
              $msg['class'] = 'unread';
          }
        }
/*
        elseif($msg['comments'] > 0) {
          $msg['new_comments'] = $msg['comments'];
        }
*/
        if(!in_array($uid,_explode(',',$msg['has_read']))){
          $msg['class'] = 'unread';
        }
        

        $msg['subject'] = '<a class="subject" href="'.l('/'.$this->gears->mail->url.'/read/'.$msg['id']).'">'.$msg['subject'].'</a>';
        if($msg['comments'] > 0){
        $msg['subject'] .=  ' <a href="'.l('/'.$this->gears->mail->url.'/read/'.$msg['id']).'#comments" class="comments_counter"><img alt="'.t('gears comments').'" src="/gears/comments/img/icon/comments.png"></a> <a href="'.l('/'.$this->gears->mail->url.'/read/'.$msg['id']).'#comments" class="comments_counter">'.$msg['comments'].'</a>';
        }
        
        if(!empty($msg['new_comments'])){
          $msg['subject'] .=  ' <span class="new_comments">+'.$msg['new_comments'].'</span>';
        }
    }
    $header = array(
      'id' => array('','','1%'),
      'recipients' => array(fc_t('mail recipients'),'text','19%',FALSE,'left'),
      'subject' => array(fc_t('mail subject'),'text','40%',FALSE,'left'),
      'last_update' => array(fc_t('global date'),'date','30%'),
      'delete' => array(t('edit delete'),'checkbox','10%')
    );
      
    $info = array(
    'link' => array("/{$this->gears->mail->url}/read"),
    'link_add' => array('id'),
    'primary' => 'id',
    'ajax' => FALSE,
    'ajax_delete' => FALSE,
    'class' => 'grid list inbox',
    'noname' => TRUE
    );
    if($delete = $this->input->post('delete')){
      $this->comments->set('pm');
      foreach($delete as $id){
          $this->comments->destroy($id);
      }           
    }
    $this->form->set('inbox_list');
    $this->form->grid('pm',$header,$result,$info);
    $this->form->compile();
  }
  // ------------------------------------------------------------------------
  
  /**
   * Read inbox
   *
   * @param int $id Inbox id
   * @return  void
   */
  public function read($id = FALSE){
    if(!$this->user->is_logged()) return _403();
    if(!$id OR $this->gear->mode != 'inbox') return _404();
    $this->db->select('pm.*',FALSE);
    if(!$pm = $this->db->where('(FIND_IN_SET('.$this->user->get('id').',pm.to) OR (pm.from = '.$this->user->get('id').' AND system IS NULL))')->where('pm.id',$id)->where('pm.owner','to')->get('pm')->row()){
      return _403();
    }
    $recipients = explode(',',$pm->to);
    if($this->input->post('invite') && $pm->from == $this->user->get('id')){
      $user_names = explode(',',$this->input->post('invite'));
      foreach($user_names as $user_name){
        if(trim($user_name) == '') continue;
        if($user = $this->user->info(trim($user_name))){
          if(!in_array($user->id,$recipients)){
            $recipients[] = $user->id;
            $this->user->refresh($user->id);
            if(!empty($pm->has_read)){
              $has_read = explode(',',$pm->has_read);
              foreach($has_read as $k=>$user_read){
                if($user_read == $user->id){
                  unset($has_read[$k]);
                }
              }
              $pm->has_read = implode(',',$has_read);
            }
          }     
        }     
      }
      $this->db->update('pm',array('pm.to'=>implode(',',$recipients),'pm.has_read'=>$pm->has_read,'last_update'=>date('Y-m-d H:i:s')),array('id'=>$pm->id));
    }
    if(!$users = explode(',',$pm->has_read)){
      $users = array();
    }
    if(empty($pm->has_read) OR !in_array($this->user->get('id'),$users)){
      $users[] = $this->user->get('id');
      $this->db->update('pm',array('has_read'=>implode(',',$users)),array('id'=>$pm->id));
      $this->user->refresh($this->user->get('id'));
    }
      $recipients[] = $pm->from;
    $recipients = array_unique($recipients);
    // Check access
    if(!in_array($this->user->get('id'),$recipients)){
      return _403();
    }
    $author = $this->user->info($pm->from);
    $cpv = $this->session->get('comments_pm_views',TRUE);
    if(isset($cpv[$pm->id])){
      $pm->last_comments = $cpv[$pm->id]['count'];
      if($pm->last_comments > 0 && $pm->comments > $pm->last_comments){
        $pm->new_comments = $pm->comments - $pm->last_comments;
        $this->user->refresh($this->user->get('id'));
      }
     }

    $this->breadcrumb->set('inbox_read')->data($pm);
    $pm->body = $this->parser->parse($pm->body,'textarea').$this->breadcrumb->compile();
    $users = array();
    foreach($recipients as $user){
      if(empty($user)) continue;
      $user = $this->user->info($user);
      $users[] = $user;
      $this->user->refresh($user->id);
    }
    $this->_template('read',array('pm'=>$pm,'author'=>$author,'users'=>$users));
    if(empty($pm->system)){
      js('/gears/mail/js/inline/inbox.js',FALSE,TRUE);
      $this->comments->set('pm',$pm);
      $this->_template('comments comments',array('comments'=>$this->comments->show(FALSE,TRUE),'wrapper'=>TRUE,'type'=>'tree'),100);
      $this->comments->form();
    }
  } 

  /**
  * Show inbox messages
  *
  * @param  int page
  * @return void
  */
  function inbox($page = 0, $subpage = 0){
    if($this->gear->mode != 'line') return _404();
    if(!is_numeric($page)){
      $this->db->where('u.url_name',url_name($page));
      $this->db->join('users u'," pm.from = u.id","inner left");
    }
    else {
      $page = $this->pager($page, $this->db->where(array('to'=>$this->user->get('id'),'owner'=>'to'))->count_all_results('pm',FALSE));
    }
    $this->db->select("pm.id,pm.from,pm.to,pm.subject,pm.created_date,pm.is_read,users.avatar,users.name,users.url_name");
    $this->db->join('users'," pm.from = users.id","inner left");
    $this->db->where(array('to'=>$this->user->get('id'),'owner'=>'to'));
    $this->show_list($page);
  }
  // ------------------------------------------------------------------------

  /**
  * Show outbox messages
  *
  * @param  int page
  * @return void
  */
  function outbox($page = 0){
    if($this->gear->mode != 'line') return _404();    
    $page = $this->pager($page, $this->db->where(array('from'=>$this->user->get('id'),'owner'=>'from'))->count_all_results('pm'));
    $this->db->select("pm.id,pm.owner,pm.from,pm.to,pm.subject,pm.created_date,pm.is_read,users.avatar,users.name,users.url_name");
    $this->db->join('users'," pm.to = users.id","inner left");
    $this->db->where(array('from'=>$this->user->get('id'),'owner'=>'from'));
    $this->show_list($page);
  }
  // ------------------------------------------------------------------------

  /**
  * Show list of messages
  *
  * @param  int page
  * @return void
  */
  function show_list($page){
    $result = $this->db->order_by('pm.id','desc')->limit($page['limit'],$page['start'])->get('pm')->result_array();
      foreach($result as $key=>$msg){
      $result[$key]['avatar'] = make_icons($msg['avatar']);
      if(!$msg['is_read']) $result[$key]['class'] = "unread";
      }
      $header = array(
      'avatar' => array('','image','5%','class'=>'avatar'),
      'name' => array(fc_t('mail from'),'link','15%'),
      'subject' => array(fc_t('mail subject'),'link','40%'),
      'created_date' => array(fc_t('global date'),'date','30%'),
      'delete' => array(t('edit delete'),'checkbox','10%')
      );
      
      $info = array(
      'link' => array('/user', "/{$this->gears->mail->url}/view"),
      'link_add' => array('url_name','id'),
      'primary' => 'id',
      'ajax' => FALSE,
      'ajax_delete' => FALSE,
      'class' => 'grid list',
      'noname' => TRUE
      );
      $this->form->set('pm_list');
      $this->form->grid('pm',$header,$result,$info);
      $this->form->compile();
  }
  // ------------------------------------------------------------------------

  /**
  * Create new message
  *
  * @param  int id of message to reply
  * @param  * @param  * @return void
  */
  function create($id = FALSE){
    $this->form->set('create_mail');
    d('mail');
    $this->form->input('to',array('label'=>t('to'),'description'=>t('to_description'),'js_validation'=>'required','validation'=>'required'))
    ->input('subject',array('label'=>t('subject'),'js_validation'=>'required','validation'=>'required'))
    ->editor('body',array('label'=>t('message'),'js_validation'=>'required','validation'=>'required'))
    ->buttons('preview',array('send'=>array('class'=>'submit')));
    if($result = $this->form->result(TRUE)){
      switch($result['action']){
       case 'preview':
       $result['body'] = nl2br($result['body']);
       $this->_template("preview",$result,3);
       $this->form->set_values($result);
       $this->form->compile();
       break;
       case 'send':
       default:
       if($this->pm->send($result)){
         redirect(l("/{$this->gears->mail->url}/"));
       }
       else {
         $this->form->compile();
       }
       break;
      }
    }
    else {
      if($id && is_numeric($id)){
          if($this->gear->mode != 'line') return _404();
        $this->db->select("pm.id,pm.from,pm.to,pm.subject,pm.body,pm.created_date,pm.is_read,pm.owner,users.avatar,users.name,users.url_name");
        $this->db->join('users'," pm.from = users.id","inner");
        $this->db->where(array('pm.id'=>(int)$id));
        if($message = $this->db->get('pm')->row()){
          // Message layout
          if(!in_array($this->user->get('id'),array($message->from,$message->to))){
            msg(t('cannot_read'),FALSE);
            redirect("/{$this->gears->mail->url}/");
          }
          $message->to = $message->name;
          if(!preg_match("#Re\([\d]+\)\:#si",$message->subject)) {
            $message->subject = "Re(0): ".$message->subject;
          } 
          else {
            $message->subject = preg_replace("#Re\(([\d]+)\)\:#ie","\$this->make_reply_subject('\\1')",$message->subject);
          }
          $message->body = '<blockquote>'."\n".$message->body."\n".'</blockquote>';
          $this->form->set_values((array)$message);         
        }
        else {
          error(t('message_not_found'));
        }
      }
      else{
        $this->form->set_values(array('to'=>$id));
      }
      $this->form->compile();
    }
  }
  // ------------------------------------------------------------------------

  /**
  * Create reply message subject
  *
  * @param  int 
  * @return string
  */
  function make_reply_subject($int){
    return "Re(".++$int."):";
  }
  // ------------------------------------------------------------------------

  /**
  * Reply to message
  *
  * @param  int id
  * @return void
  */
  function reply($id = FALSE){
    if(!$id OR $this->gear->mode != 'line') return _404();
    $this->panel->set_active('create');
    $this->panel->compile(2); 
    $this->create($id);
  }
  // ------------------------------------------------------------------------

  /**
  * View message
  *
  * @param  int id
  * @return void
  */
  function view($id = FALSE){
    if(!$id) show404();
    d('mail');
      $this->db->select("pm.id,pm.from,pm.to,pm.subject,pm.body,pm.created_date,pm.is_read,pm.owner,users.avatar,users.name,users.url_name");
    $this->db->join('users'," pm.from = users.id","inner");
    $this->db->where(array('pm.id'=>(int)$id));
    if($message = $this->db->get('pm')->row()){
      // Message layout
      if(!in_array($this->user->get('id'),array($message->from,$message->to))){
        msg(t('cannot_read'),FALSE);
        redirect(l("/{$this->gears->mail->url}/"));
      }
      $this->user->remove('pm');
      $message->avatar = make_icons($message->avatar);
      $this->_hook('pm','view','get',$message);
      $this->_template("view",(array)$message,3);
      if($message->owner == 'to'){
        if(!$message->is_read){
          // Clear cpanel messages counter
          $this->session->remove('pm_check');
          $this->db->update('pm',array('is_read'=>'true'),array('id'=>$id));
          $result = $this->db->select('id')->get_where('pm',array('created_date'=>$message->created_date,'to'=>$message->to,'from'=>$message->from,'owner'=>'from'))->row();
          if($result){
           $this->db->update('pm',array('is_read'=>'true'),array('id'=>$result->id));
          }
          }
          $this->breadcrumb->set('pm_reply')->wrapper();
          $this->breadcrumb->data($message);
          $this->breadcrumb->add('<span class="button"><input type="button" value="'.t('reply').'" onclick="document.location=\''.l('/'.$this->gears->mail->url.'/reply/'.$id.'/').'\'"></span>');
          $this->breadcrumb->compile(8);
      }
      $this->panel->set_active($message->to == $this->user->get('id') ? 'inbox' : 'outbox');
      $this->panel->compile(2); 
    }
    else {
      error(t('message_not_found'));
    }
  }
  // ------------------------------------------------------------------------
  
  /**
   * Kick user from inbox
   *
   * @return json
   */
  public function kickout(){
    $pid = $this->input->post('pm');
    $uid = $this->input->post('user');
    $success = FALSE;
    $user = $this->user->info($uid);
    $msg = t('inbox_kickout_failure');
    if($pm = $this->db->get_where('pm',array('id'=>$pid))->row()){
      if($pm->from == $this->user->get('id')){
        $to = explode(',',trim($pm->to));
        foreach($to as $key=>$user_id){
          if($user_id == $uid){
            unset($to[$key]);
          }
        }
        $pm->to = implode(',',$to);
        $this->db->update('pm',array('pm.to'=>$pm->to),array('id'=>$pm->id));
        $msg = t('inbox_kickout_success');
        $success = TRUE;
      }
    }
    ajax($success,$msg);
  } 
}
// ------------------------------------------------------------------------