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
 * Extending CI_Benchmark class for further functionality.
 *
 * @package		CoGear
 * @subpackage	Benchmark
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class CG_Benchmark extends CI_Benchmark{
	/**
	* Points for benchmark
	*
	* @var	array
	*/
	public $points = array();
	
	/**
	* Set points and measure time and mem between them.
	*
	* @param	string	$name	Point name
	* @return	void
	*/
	function point($name){
		$this->points[$name] = array(
		'time' => microtime(),
		'mem' => memory_get_usage()
		);
	}
	// ------------------------------------------------------------------------
	
	/**
	*	Get points
	*
	* @return	array
	*/
	function get_points(){
		return $this->points;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------