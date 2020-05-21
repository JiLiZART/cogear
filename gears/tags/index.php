<?php
/**
* Tags controller
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Tags
* @version		$Id$
*/
class Index extends Controller
{
	/**
	* Constructor
	*/
	public function __construct(){
		parent::Controller();
	}
	
	/**
	* Show nodes indexes with tag
	*
	* @param
	* @param
	* @param
	* @return
	*/
	public function index($tag =  FALSE,$page = 0){
		if(!$tag){
			$tags = $this->db->select('COUNT(nodes_tags.nid) as count, tags.name')->join('tags','tags.id = nodes_tags.tid','inner')->group_by('tid')->order_by('tags.name','asc')->get('nodes_tags')->result();
			$output = array();
			$max = 0;
			foreach($tags as $tag){
				if($tag->count > $max) $max = $tag->count;
			}
			foreach($tags as $tag){
				$rating = floor($tag->count/$max*100);
				$font_size = str_replace(',','.',(($rating < 100 ? '1.'.$rating : '2')/1.5).'em');
				$level = round($rating/10);
				$output[] = '<a href="'.l('/tags/'.$tag->name).'" class="l'.$level.'" style="font-size: '.$font_size.'">'.$tag->name.'</a>';
			}

			$this->builder->h1(t('widgets.tags'),TRUE);
			$this->builder->div(implode(' ',$output),FALSE,'tags-cloud',TRUE);
			
		}
		else {
			$tag = $this->db->where('name',$tag)->get('tags')->row();
			if($tag){
				title($tag->name);
				$this->builder->div('<h1><a href="'.l('/tags/').'">'.t('widgets tags').'</a> &rarr; '.$tag->name.'</h1>',TRUE);
				$this->db->join('nodes_tags','nodes.id = nodes_tags.nid AND nodes_tags.tid = '.$tag->id,'inner');
				$this->nodes->get($page,FALSE,TRUE,FALSE,array('tags'));
			}
			else _404();	
		}
	}
	
	/**
	* Tags autocompletion
	*
	* @return	void
	*/
	public function complete(){
		$value = $this->input->post('value');
		$output = array();
		if($result = $this->db->order_by('name','asc')->where('name LIKE "%'.$value.'%"')->limit('20')->get('tags')->result()){
			foreach($result as $row){
				$output[] = $row->name;
			}
		}
		die(json_encode($output));
	}
}
