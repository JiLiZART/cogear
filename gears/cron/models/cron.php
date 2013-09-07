<?php
/**
 * Cron model
 *
 * 
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Cron
 * @subpackage	Models
 * @version			$Id$
 */
class Cron extends Model
{
	private $pid;
	private $key;
		 
	/**
	 * Constructor
	 */
	public function __construct(){
		parent::Model();
		$this->pid = dirname(dirname(__FILE__)).'/cron.pid';
	} 
	
	/**
	 * Check if cron runs and start if not
	 *
	 * @return	void
	 */
	 public function check(){
		$restart = FALSE;
		if($existance = file_exists($this->pid)){
			$time = file_get_contents($this->pid);
			// If file is to old — run cron again
			if(intval($time) + 60 < time()){
				$restart = TRUE;
			}
		}
		if(!$existance OR $restart){
			$this->start();
		}
	 }
	 
	 /**
	  * Start cron
	  *
	  * @return	void
	  */
	  public function start(){
/*
		  ignore_user_abort();
		  do {
*/
			  set_time_limit(0);
			  $this->run();
			  file_put_contents($this->pid,time());
/* 			  sleep(60); */
/* 		  } while(TRUE); */
	  }
	  
	  /**
	   * Run cron
	   *
	   * @return	void
	   */
	   public function run(){
		   $tasks = $this->db->where('("'.time().'" - last_call) > period')->order_by('position')->get('cron')->result();
		   foreach($tasks as $task){
				    $args = preg_split('/\W+/',$task->callback,-1,PREG_SPLIT_NO_EMPTY);
				    $count = count($args);
				    if($count > 1){
						$result = $this->$args[0]->$args[1]();
				    }
				    elseif($count == 1){
						$result = call_user_func($args[0]);
					}
					if(!empty($result)){
						$this->db->update('cron',array('last_call'=>time()),array('id'=>$task->id));
					}
   		   }
	   }
}