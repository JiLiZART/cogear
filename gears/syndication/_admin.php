<?php
/**
* Syndication control panel
*
* Add and manage rss-feeds.
*
* @author   Dmitriy Belyaev <admin@cogear.ru>
* @copyright    Copyright (c) 2009, Dmitriy Belyeav
* @license    http://cogear.ru/license.html
* @link     http://cogear.ru
* @package    Syndication
* @version    $Id$
*/
class _Admin extends Controller{
  /**
   * Constructor
   */
  public function __construct(){
    parent::Controller();
    d('syndication');
  }
  
  /**
   * Index page
   */
  public function index(){
      $header = array(
      'position' => array('','dragndrop','5%'),
        'favicon'=>array('','image','5%'),        
        'name'=>array(t('name'),'link','65%',FALSE,'left'),
        'items' =>array(t('count'),'link','25%'),
        'delete' => array(t('!edit delete'),'checkbox','5%')
        );                                              
        $this->db->select("syndication_sources.*, COUNT({$this->db->dbprefix}syndication_items.id) as items");
        $this->db->join('syndication_items','syndication_sources.id = syndication_items.sid','left')
        ->group_by('syndication_items.sid');
        $result = $this->db->order_by('id','asc')->get('syndication_sources')->result_array();
      foreach($result as $key=>&$value){
        }
        $info = array(
        'link' => array('/admin/syndication/createdit','/admin/syndication/items'),
        'link_add' => array('id'),
        'primary' => 'id',
        'multiple' => TRUE,
        'no_class' => TRUE,
        'noname' => TRUE,
          'dragndrop' => 'position'

        );
        if($sids = $this->input->post('delete')){
          foreach($sids as $sid){
            $this->db->where('sid',$sid)->delete('syndication_items');
          }
        }
        $this->form->grid('syndication_sources',$header,$result,$info);
        $this->form->compile();
    button('syndication add_source','createdit/');    
  }
  
  /**
   * Show items for source
   */
  public function items($sid = FALSE, $page = 0){
      if($sid){
        $source = $this->db->get_where('syndication_sources',array('id'=>$sid))->row();
        $this->db->where('sid',$sid);
        title(t('items','"'.$source->name.'"'));
      }
      else title(t('items',''));
      
      $header = array(
        'link'=>array(t('name'),'text','65%',FALSE,'left')
        );
        if(empty($source)){
        $header += array(
          'sname'=>array(t('sname'),'link','25%',FALSE,'left')
          );
        }
        $header += array(
        'created_date'=>array(t('created_date'),'text','30%'),
        'delete' => array(t('!edit delete'),'checkbox','5%')
        );
      $page = $this->pager($page,$this->db->count_all_results('syndication_items',FALSE),array('base_url'=>'/admin/syndication/items/'.intval($page).'/'));
      $this->db->limit($page['limit'],$page['start']);
        $this->db->select('syndication_sources.id as sid, syndication_sources.name as sname, syndication_items.id, syndication_items.name, created_date, syndication_items.link');
        $this->db->join('syndication_sources','syndication_sources.id = syndication_items.sid','left');
        $result = $this->db->order_by('syndication_items.created_date','desc')->get('syndication_items')->result_array();
      foreach($result as $key=>&$value){
        $value['created_date'] = '<small>'.df($value['created_date']).'</small>';
        $value['sname'] = '<small>'.$value['sname'].'</small>';
        $value['link'] = '<a href="'.$value['link'].'">'.$value['name'].'</a>';
        }
        $info = array(
        'link' => array('/admin/syndication/items'),
        'link_add' => array('sid'),
        'primary' => 'id',
        'multiple' => TRUE,
        'no_class' => TRUE,
        'noname' => TRUE,
        );
        $this->form->grid('syndication_items',$header,$result,$info);
      $this->form->compile();
      button('syndication refresh_button','/admin/syndication/refresh/'.$sid);
  }
  /**
   * Create and edit source
   */
  public function createdit($id = FALSE){
    if($id){
      $source = $this->db->get_where('syndication_sources',array('id'=>$id))->row();
    }
    $options = array();
    foreach(array(10,15,30,45) as $period){
      $options[$period] = t('refresh_rate_plural_minutes',$period);
    }
    foreach(array(1,2,3,6,12) as $period){
      $options[$period*60] = t('refresh_rate_plural_hours',$period);
    }
    foreach(array(1,3,7,30) as $period){
      $options[$period*60*60] = t('refresh_rate_plural_days',$period);
    }
    $this->form->set('admin/syndication/createdit');
    if(!empty($source)) $this->form->input('name',array('validation'=>'required','js_validation'=>'required'));
    $this->form->input('link',array('validation'=>'required|valid_url','js_validation'=>'required|url'))
    //->select('refresh_rate',array('options'=>$options))
    ->buttons(empty($source) ? 'create' : 'save');
    title(empty($source) ? t('add_source') : t('edit_source'));
    if(isset($source)){
      $this->form->set_values($source);
    }
    if($result = $this->form->result()){
      if(empty($source)){
        $this->feed->get($result['link']);
        $result['name'] = $this->feed->title;
        if($this->feed->favicon){
          $result['favicon'] = $this->feed->favicon;
        }
        $this->form->save('syndication_sources',$result);
        // Update feed items
        $this->feed->refresh($this->form->insert_id);
        redirect('/admin/syndication/');
      }
      else {
        $this->feed->get($result['link']);
        if($this->feed->favicon){
          $result['favicon'] = $this->feed->favicon;
        }
        $this->form->update('syndication_sources',$result,array('id'=>$source->id));
        redirect('/admin/syndication/');
      }
    }
    $this->form->compile();
  }
  
  /**
   * Refresh feed items
   *
   * @param int $sid Source id.
   */
  public function refresh($sid = 0){
    $this->feed->refresh($sid);
    msg('refresh_successed');
    redirect('/admin/syndication/items/'.$sid);
  }
   
}