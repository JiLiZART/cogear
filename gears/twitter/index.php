<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author			CodeMotion, Dmitriy Belyaev
 * @copyright		Copyright (c) 2009, CodeMotion
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Twitter controller
 *
 * @package		CoGear
 * @subpackage	Twitter
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Index extends Controller{
	function __construct(){
		parent::Controller();
	}
	
	/**
	* Retrieve status from twitter
	*
	* @param	string	Login
	* @return 	json
	*/
	function index($login){
			$tweet = getLastTweet($login);
			if(is_object($tweet)){
				ajax(TRUE,'<a href="http://twitter.com/'.$login.'/"><img src="/gears/twitter/img/icon/twitter.png" width="32" height="32" border="0" title="Twitter" align="left"></a> <small>'.df($tweet->time,'%H:%M &#8592; %d %B').'</small><br>'.$tweet->text);
			}
			else ajax(FALSE);
		}
		
		/**
		* Post all nodes to twitter
		*/
		public function synchronize($start = 0){
			if($this->user->get('id') == 1){
				$limit = 20;
				_mkdir(ROOTPATH.'/uploads/twitter_auth/');
				// Login to twitter
				$this->curl->init(array(
				'cookie_file'=>ROOTPATH.'/uploads/twitter_auth/'.md5($this->gears->twitter->login.time().date('Y.m.d H:i:s')),
				'site'=>'http://twitter.com',
				'followlocation'=>FALSE
				));
				$page = $this->curl->get('/login');
				
				preg_match('#<input\s+name="authenticity_token"[^>]*value="([^\"]+)"#u',$page,$matches);
				if(!empty($matches)){
				
					$this->node->query();
					if($nodes = $this->db->order_by('id','asc')->get('nodes',$limit,$start)->result()){
						foreach($nodes as $node){
							$link = $this->node->create_link($node);
							$this->curl->post('/status/update',array(
							'tab' => 'index',
							'authenticity_token' => $key,
							'in_reply_to_status_id' => '',
							'in_reply_to' => '',
							'source' => '',
							'status' => $link.' '.$node->name.' ¹'.$node->id,
							'update'=>'update'
							));
							$log = $this->session->get('log');
							if(empty($log) OR !is_array($log)) $log = array();
							$log[] = "{$node->id}. Node <b>{$node->name}</b> has been post to Twitter.";
							$this->session->set('log',$log);
						}
						redirect('/twitter/synchronize/'.($start+$limit));
					}
					else {
						$this->builder->div(implode('<br>',$this->session->get('log',TRUE)));
					}
				}
			}
		}
}
// ------------------------------------------------------------------------