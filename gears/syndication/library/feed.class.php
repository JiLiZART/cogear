<?php
/**
* Feed class
*
* Based on SimplePie
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright	Copyright (c) 2010, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Syndication
* @subpackage	libraries
* @version		$Id$
*/
class Feed{
	public $handler;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->handler = new SimplePie();
		$this->handler->set_cache_location(_mkdir(ROOTPATH.'/engine/cache/simplepie/'));
	}
	
	/**
	 * Get url
	 *
	 * @param	string	$url	RSS url.
	 * @return	object	$handler
	 */
	public function get($url){
		$this->handler->set_feed_url($url);
		$this->handler->init();
		$this->handler->handle_content_type();
		return $this->handler;
	}
	
	/**
	 * Magic method
	 */
    public function __call($name,$args){
	    return call_user_func_array(array($this->hanlder,$name),$args);
    }
    
    /**
     * Magic get method
     */
    public function __get($name){
	    $method = 'get_'.$name;
	    return method_exists($this->handler,$method) ? call_user_func(array($this->handler,$method)) : null;
    }
    
    /**
     * Refresh items for source
     *
     * @param	int	Source id
     * @return	boolean	Imporant for cron
     */
     public function refresh($sid=FALSE){
	    $CI =& get_instance();
	    if($sid) $CI->db->where('id',$sid);
		$feeds = $CI->db->get('syndication_sources')->result();
		foreach($feeds as $feed){
			$CI->feed->get($feed->link);
			foreach($CI->feed->items as $item){
				$data = array(
				'name'=>$item->get_title(),
				'created_date'=>date('Y-m-d H:i:s',strtotime($item->get_date())),
				'sid'=>$feed->id,
				'link'=>$item->get_link(),
				);
				if($CI->db->get_where('syndication_items',$data)->row()){
					continue;
				}
				$data['body'] = $item->get_content();
				$CI->db->insert('syndication_items',$data);
			}
		}
		return TRUE;
     }
}