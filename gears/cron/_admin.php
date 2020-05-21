<?php
/**
 * Cron scheduler
 *
 * 
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Cron
 * @version			$Id$
 */
class _Admin extends Controller
{
	/**
	 * Constructor
	 */
    public function __construct(){
	    parent::Controller();
	    d('cron');
    }	 
    
    /**
     * Index
     */
    public function index($action = 'list', $id = FALSE){
	    $minutes = array(1,5,10,15,30,45);
	    $hours = array(1,2,3,4,8,12,24);
	    $days = array(1,2,3,4,5,6,7,15,30);
	    $time_options = array();
	    foreach($minutes as $min){
		    $period = $min*60;
		    $time_options[$period] = t('minutes',$min);
	    }
	    foreach($hours as $hour){
		    $period = $hour*3600;
		    $time_options[$period] = t('hours',$hour);
	    }
	    foreach($days as $day){
		    $period = $day*86400;
		    $time_options[$period] = t('days',$day);
	    }

	    switch($action){
	    case 'edit':
	    $task = $this->db->get_where('cron',array('id'=>$id))->row();
	    case 'create':
		    $config = array('validation'=>'required','js_validation'=>'required');
		    $this->form->set('admin/cron/createdit')
		    ->input('name',$config)
		    ->input('callback',$config)
		    ->select('period',array(
		    'options'=>$time_options))
		    ->buttons($action == 'create' ? $action : 'save');
		    if(!empty($task)) $this->form->set_values($task);
		    if($result = $this->form->result()){
				    if(!empty($task)){
					    $this->form->update('cron',$result,array('id'=>$task->id));
				    }
				    else {
					    $this->form->save('cron',$result);
				    }
				    redirect('/admin/cron');
		    }
		    $this->form->compile();
	    
	    break;
	    // List all tasks
	    default:
		    $header = array(
		    'position' => array('','dragndrop','10%'),
		    'name' => array(t('edit name'),'link','20%',FALSE,'left'),
		    'id' => array('','icon','10%','/gears/global/img/icon/edit.png','left'),
		    'callback' => array(t('callback'),'text','20%',FALSE),
		    'period' => array(t('period'),'text','20%',FALSE),
		    'delete' => array(t('edit delete'),'checkbox','10%',FALSE),
		    );
		    $data = $this->db->order_by('position','asc')->get('cron')->result_array();
		    foreach($data as &$item){
			    $item['period'] = $time_options[$item['period']];
		    }
		    $info = array(
		    'link' => array('/admin/cron/edit/','/admin/cron/edit/'),
		    'link_add' => array('id','id'),
		    'dragndrop' => 'position',
		    'primary' => 'id',
		    'noname' => TRUE
		    );
		    $this->form->set('admin/cron/')
		    ->grid('cron',$header,$data,$info)
		    ->compile();		    
		    button(t('create'),'create/');
	    }
    } 
    
} 