<?php  if( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Pager model
 *
 * @package		CoGear
 * @subpackage	Pager
 * @category		Gears models
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class Pager extends Model {
    public $base_url			= ''; 
    public $total_rows  		= ''; 
    public $per_page	 		= 10; 
    public $num_links			= 5; 
    public $cur_page	 		=  0; 
    public $uri_segment		= 4;
    private $prefix = FALSE;

	/**
	* Constructor
	*
	* @return	void
	*/
    function Pager(){
		parent::Model();
		$CI =& get_instance();
		$this->prefix = $CI->gears->pager->prefix;
    }
	// ------------------------------------------------------------------------

    
	/**
	* Set model params
	*
	* @param	array
	* @return	object
	*/
    function set($params){
		foreach ($params as $key => $val){
			$this->$key = $val;
		}
        return $this;
    }
	// ------------------------------------------------------------------------

	
  	/**
	* Create pager
	*
	* @param	boolean
	* @return	string
	*/
    function create_links($return = FALSE){
        $CI =& get_instance();
        $CI->builder->start();
        d('pager');
        if($this->total_rows == 0 OR $this->per_page == 0){
            return '';
        }
        
		$this->cur_page = intval($this->cur_page);
        
        $num_pages = ceil($this->total_rows/$this->per_page);

        if($num_pages == 1){
            return FALSE;
        }

        if($this->cur_page <= 0){
            $this->cur_page = $num_pages;
        }

        if($this->cur_page*$this->per_page > $this->total_rows)
        {
            $this->cur_page = $num_pages;
        }

        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        $this->base_url = rtrim($this->base_url, '/') .'/';

        // 'First' link
        if($this->cur_page < $num_pages){
            $data['first'] = $CI->builder->a(t('first'),$this->base_url,TRUE);
        }

        // 'Previous' link
        if($this->cur_page < $num_pages){
            $i =  $this->cur_page + 1;
            if($i == $num_pages) $i = '';
            else $i = $this->prefix.$i.'/';
            $data['prev'] = $CI->builder->a(t('prev'),$this->base_url.$i,FALSE,'prevlink',TRUE);
        }
        else {
			$data['prev'] = $CI->builder->span(t('prev'),TRUE);
        }

        // Digit links
        for ($page = $end; $page >= $start; $page--){
            $i = $page;

            if($i > 0){
                if($this->cur_page == $page){
                    $CI->builder->span($page,FALSE,'current_page'); // Current page
                }
                else{
                    $n = ($i == $num_pages) ? '' : $this->prefix.$i.'/';
                    $CI->builder->a($page,$this->base_url.$n);
                }
            }
        }

        // 'Next' link
        if($this->cur_page > 1){
            $data['next'] = $CI->builder->a(t('next'),$this->base_url.$this->prefix.($this->cur_page-1).'/',FALSE,'nextlink',TRUE);
        }
        else {
			$data['next'] = $CI->builder->span(t('next'),TRUE);
        }

        // 'Last' link
        if($this->cur_page != 1){
            $i = $num_pages.'/';
            $data['last'] = $CI->builder->a(t('last'),$this->base_url.$this->prefix.'1/',TRUE);
        }
		d();
        $data['pages'] = $CI->builder->compile(TRUE);
        $template = $CI->_template('pager pager',$data,TRUE);
        if(isset($CI->pager_use_get) && $CI->pager_use_get){
	        unset($_GET['action']);
	        $template = str_replace('/"','/?'.http_build_query($_GET).'"',$template);
        }
        return $template;
    }
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------