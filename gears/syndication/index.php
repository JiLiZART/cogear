<?php
/**
* Syndication controller
*
* Show list of items
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright	Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Syndication
* @version		$Id$
*/
class Index extends Controller{
	/**
	 * Constructor
	 */
	public function __construct(){
		parent::Controller();
		d('syndication');
	} 
	
	/** 
	 * Show syndicated items list
	 */
	public function index($page = 0){
		$header = array(
		'info'=>array(t('name'),'text','60%',FALSE,'left'),
		'created_date'=>array(t('created_date'),'text','40%'),
		);
		$page = $this->pager($page,$this->db->count_all('syndication_items'),array(
		'per_page' => $this->gears->syndication->index_per_page
		));
		$items = $this->db
		->select('syndication_sources.name as sname, syndication_sources.favicon, syndication_sources.link as slink, 
		syndication_items.name,syndication_items.link,syndication_items.created_date
		',FALSE)
		->join('syndication_sources','syndication_items.sid = syndication_sources.id','inner')
		->order_by('created_date','desc')
		->limit($page['limit'],$page['start'])
		->get('syndication_items')
		 ->result_array();
		 foreach($items as &$item){
			 $item['created_date'] = '<small>'.df($item['created_date']).'</small>';
			 $item['info'] = '<a href="'.$item['slink'].'"><img src="'.$item['favicon'].'" width="16" height="16" border="0" alt="'.$item['sname'].'"/></a> <a href="'.$item['link'].'">'.$item['name'].'</a>';
		 }
	      $info = array(
	      'primary' => 'id',
	      'multiple' => FALSE,
	      'no_class' => TRUE,
	      'noname' => TRUE,
	      );
	      $this->form->set('syndication')
	      ->title(t('title'))
	      ->grid('syndication',$header,$items,$info)
	      ->compile();
	      
	}
}