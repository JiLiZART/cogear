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
 * Twitter hooks
 *
 * @package		CoGear
 * @subpackage	Twitter
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
/**
 * Get last tweet by login
 *
 * @param	string	$login
 * @return	string
 */
 function getLastTweet($login){
		 $tweet = retrieve('twitter/status/'.$login);
			if(!$tweet){
				$CI =& get_instance();
				if($file = @file_get_contents('http://twitter.com/statuses/user_timeline/'.$login.'.json')){
					$data = json_decode($file);
					if(!is_array($data)) return;
					foreach($data as $message){
						if(!$tweet && $message->in_reply_to_screen_name == NULL && $message->truncated === FALSE){
							$tweet = (object) array();
/* 							$tweet->text = preg_replace('#(http://)([\w]+\.[\w]{2,5})/([^\s]*)#i','<a href="\1\2/\3" rel="nofollow">\2…</a> ',$message->text); */
							$tweet->text = parse_jevix($message->text);
							$tweet->text = preg_replace('#\s+@([\w_-]+)#',' <a href="http://twitter.com/$1">@$1</a>',$tweet->text);
							$tweet->time = $message->created_at;						
							store('twitter/status/'.$login,$tweet,$CI->gears->twitter->refresh);
						}
					}
				}
			}
			return $tweet;
 } 
 
	/**
	 * Shows last tweet from global_login user in site header.
	 */
	function twitter_after($CI){
		if(!$CI->gears->twitter->login OR !$CI->gears->twitter->template_tweet) return;		
		// Enter
		$login = $CI->gears->twitter->login;
		$tweet = getLastTweet($login);
			$limit = 140;
			if(!is_object($tweet)) return;
			if(strlen(strip_tags($tweet->text)) > $limit) $text = substr($tweet->text, 0,strpos($tweet->text,' ',$limit)).'... <a href="http://twitter.com/'.$CI->gears->twitter->login.'">&rarr;</a>';
			else $text = $tweet->text;
			$CI->content['twitter'] = $text;
			//.' <small>'.df($tweet->time).'</small>';
	}
	 
	/**
	*  Add tab and fields to user profile edit
	*
	* @param	object $Form
	* @return	void
	*/
	function twitter_form_result_($Form){
		if($Form->name == 'user_profile'){
			$Form->title('Twitter',FALSE,FALSE,FALSE)
			->fieldset('twitter-options')
			->input('twitter',array('label'=>t('name'),'id'=>'twitter-login'))
			->fieldset();
		}
	}
	// ------------------------------------------------------------------------

	
	/**
	* Add last tweet to user profile
	* Add post-link-to-twitter to node breadcrumb
	*
	* @param	object $Breadcrumb
	* @return	void
	*/
	function twitter_breadcrumb_compile_($Breadcrumb){
		$CI =& get_instance();
		if($Breadcrumb->name == 'userinfo_panel' && !empty($Breadcrumb->data->twitter)){
			js("window.addEvent('domready',function(){
				new Request.JSON({
				url: '/ajax/twitter/{$Breadcrumb->data->twitter}/',
				onComplete: function(re){
					if(re.success){
					new Element('div').addClass('tweet').set('html',re.msg).inject('userinfo_panel','bottom');
					}
				}
				}).post();
			});",TRUE,TRUE);
		}
		elseif($Breadcrumb->name == 'node_info' && $CI->gears->twitter->node_info){
				$Breadcrumb->add('<a href="http://twitter.com/home/?status='.urlencode($Breadcrumb->data->link.' '.$Breadcrumb->data->name.($CI->gears->twitter->suffix ? $CI->gears->twitter->suffix : ' @'.$CI->gears->twitter->login)).'"><img class="add-to-twitter" id="twitter-node-'.$Breadcrumb->data->id.'" src="/gears/twitter/img/icon/twitter-post.png" title="'.t('twitter.add_post').'" alt="'.t('twitter.add_post').'"></a>',5);
		}
	}
	// ------------------------------------------------------------------------
	
	/**
	* Sent item to twitter
	*
	* @return void
	*/
	function twitter_form_save_after_($Form,$result,$table,$data){
		$CI =& get_instance();
		if($Form->name == 'node_createdit' && $CI->gears->twitter->cross_posting && $CI->gears->twitter->login && $CI->gears->twitter->password){
				$CI->node->query();
				$node = $CI->db->get_where('nodes',array('nodes.id'=>$Form->insert_id))->row();
				if(!$node->published) return;
				$link = $CI->node->create_link($node);
				_mkdir(ROOTPATH.'/uploads/twitter_auth/');
				// Login to twitter
				$CI->curl->init(array(
				'cookie_file'=>ROOTPATH.'/uploads/twitter_auth/'.md5($CI->gears->twitter->login.time().date('Y.m.d H:i:s')),
				'site'=>'http://twitter.com',
				'followlocation'=>FALSE
				));
				$page = $CI->curl->get('/login');
				
				preg_match('#<input\s+name="authenticity_token"[^>]*value="([^\"]+)"#u',$page,$matches);
				$key = trim($matches[1]);
				// If isn't logged in
				$CI->curl->post('/sessions',array(
				'authenticity_token'=>$key,
				'session[username_or_email]'=>$CI->gears->twitter->login,
				'session[password]'=>$CI->gears->twitter->password,
				'remember_me'=>'1',
				'commit'=>'Sign In'
				));
				
				$CI->curl->post('/status/update',array(
				'tab' => 'index',
				'authenticity_token' => $key,
				'in_reply_to_status_id' => '',
				'in_reply_to' => '',
				'source' => '',
				'status' => $link.' '.$node->name.' №'.$node->id,
				'update'=>'update'
				));
	
		}
	}
// ------------------------------------------------------------------------