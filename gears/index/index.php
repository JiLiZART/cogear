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

/**
 * Index controller
 *
 * @package   CoGear
 * @subpackage  Index
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
    $this->notitle = TRUE;
  }

  /**
  * Show nodes on index page
  *
  * @param  int
  * @param  string
  * @return void
  */
  function index($page = 0, $filter = FALSE){
      if($this->gears->community){
        switch($filter){
          case 'blogs':
            $this->db->where('nodes.cid  = 0');
            $fast_query = FALSE;
          break;
          case 'community':
            $this->db->where('nodes.cid != 0');
            $fast_query = FALSE;
          break;
          default:
          $fast_query = TRUE;
        }
        $counter = retrieve('index/counter');
        if(!$counter){
          $this->db->swop();
          $where = array(
          'best' => FALSE,
          'blogs' => 'nodes.cid = 0',
          'community' => 'nodes.cid != 0',
          'new' => 'promoted IS NULL'
          );
          foreach($where as $item=>$where){
            $this->node->query();
            if($where) $this->db->where($where);
            if($item == 'best') $this->db->where('promoted','true');
            $this->db->where('published IS NOT NULL');
            $this->_hook('index','counter','query');
            $counter[$item] = $this->db->count_all_results('nodes',FALSE);
            $period = time() - 7*24*60*60;
            $counter[$item.'_new'] = $this->db->where('created_date > "'.date('Y-m-d',$period).'"')->count_all_results('nodes',TRUE);
          }
          store('index/counter',$counter,FALSE,'nodes/counters,nodes,nodes_views');
          $this->db->swop();
        }
      }
      if(!$filter){
        $this->db->where('promoted','true');
        $this->db->order_by('promoted_date','desc');
        $this->nodes->get($page,FALSE,TRUE,TRUE);
       }
       else {
         if($filter == 'new') $this->db->where('promoted',NULL);
        $this->nodes->get($page,FALSE,TRUE);       
       }
      
      $this->panel->set('index',FALSE,TRUE,'!global tabs')->base('/')->data($filter)
      ->add(array('name'=>'best','text'=>fc_t('index best').' ('.$counter['best'].')'.($counter['best_new'] > 0 ? ' <sup>+'.$counter['best_new'] .'</sup>' : ''),'index'=>TRUE,'title'=>FALSE));
      if($this->gears->community) $this->panel->add(array('name'=>'community','text'=>t('!gears community').' ('.$counter['community'].')'.($counter['community_new'] > 0 ? ' <sup>+'.$counter['community_new'] .'</sup>' : ''),'title'=>t('!gears community')))
      ->add(array('name'=>'blogs','text'=>t('!gears blogs').' ('.$counter['blogs'].')'.($counter['blogs_new'] > 0 ? ' <sup>+'.$counter['blogs_new'] .'</sup>' : ''),'title'=>t('!gears blogs')))
      ->add(array('name'=>'new','text'=>t('index new').' ('.$counter['new'].')'.($counter['new_new'] > 0 ? ' <sup>+'.$counter['new_new'] .'</sup>' : ''),'title'=>t('index new')))
      ->set_active($filter ? $filter : 'best')
      ->compile(2);   
  }
  
  /**
  * Promote node to index
  *
  * @return json
  */
  public function promote(){
    // Check access level
    if(!acl('index promote')) return _403();
    d('index');
    // Catch node id and some kind of filter it
    $nid = (int)$this->input->post('nid');
    // Predefine results in case of errors
     $success = FALSE;
     $msg = t('promote_error');
     // Get node
    if($node = $this->db->get_where('nodes',array('id'=>$nid))->row()){
      // Set i18n section/department
      if($promote = empty($node->promoted) ? $this->indexer->promote($nid) : $this->indexer->depromote($nid)){
        $success = TRUE;
        $msg = !empty($node->promoted) ? t('depromoted') : t('promoted'); 
      }
    }
    ajax($success,$msg);
  }
}