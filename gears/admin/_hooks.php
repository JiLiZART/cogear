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
 * Admin Control Panel hooks
 *
 * @package		CoGear
 * @subpackage	Admin Control Panel
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	*  Add button for CP into CPanel and manage title, sidebar.
	*
	* @param	object $CI
	* @return	void
	*/
	function admin_header($CI){
		if($CI->user->get('user_group') == 1){
			$CI->cpanel->add(array('text'=>t('!admin admin_button'),'data'=>'/gears/admin/img/icon/admin.png','link'=>l('/admin/')),10);
			$CI->cpanel->add(array('text'=>t('admin clear_cache'),'data'=>'/gears/admin/img/icon/clear_cache.png','link'=>l('/admin/global/clear_cache/')),10);
		}
		if(isset($CI->uri->segments[1]) && $CI->uri->segments[1] == 'admin') {
			$CI->content['meta']['title'] = array_merge(array($CI->content['meta']['title'][0],t('!admin control_panel')),array_slice($CI->content['meta']['title'],count($CI->uri->segments) < 2 ? 2 : 1));
			// Explain count($CI->uri->segments) < 2 ? 2 : 1)
			// By default engine adds gear name to title;
			// If gear is admin it should be automatically replaced to Control Panel
			// If gear is no admin then there is nothing toreplace
			// Try to temporary replace it with 1 and take a look at /admin/ title before and after
		}
		if(isset($CI->uri->segments[1]) && $CI->uri->segments[1] == 'admin') $CI->no_sidebar = TRUE;
	}
	// ------------------------------------------------------------------------

	/**
	*  Manage navigation breadcrumb.
	*
	* @param	object $CI
	* @return	void
	*/
	function admin_footer($CI){
		// If user is in CP
		if(isset($CI->uri->segments[1]) && $CI->uri->segments[1] == 'admin' && isset($CI->uri->segments[2])) {
			// Link breadcrumb model
			$CI->navbar = NULL;
			$CI->navbar = $CI->breadcrumb;
			// Set data for navbar
			$CI->navbar->set('admin_navbar',' &rarr; ','navbar')->wrapper();
				$names = array();
				// Explore uri segments
					// Add primary link to CP dashboard 
					$CI->navbar->add('<a href="'.l('/admin/').'">'.t('!admin control_panel').'</a>')
					->add('<a href="'.l('/admin/'.$CI->name.'/').'">'.t('!gears '.$CI->name).'</a>');
					$segments = array_slice($CI->uri->segments,array_search($CI->name,$CI->uri->segments));
					$path = '/admin/'.$CI->name.'/';
					$i = 1;
					// Manage segments
					foreach($segments as $key=>$segment){
							$path .= $segment.'/';
						// If it's not numeric (we don't edit something)
						if(!is_numeric($segment)){
							if($segment == 'createdit'){
								if(isset($segments[$key+1]) && is_numeric($segments[$key+1])){
									$segment = 'edit';
								}
								else $segment = 'create';
							}
							// Try to get name from title
							if(isset($CI->content['meta']['title'][(3+$key)])){
								$name = $CI->content['meta']['title'][(3+$key)];
							}
							// Try to get name from uri segments
							else if(has_t('!edit '.$segment)){
								$name = t('!edit '.$segment);
							}
							else {
								$name = FALSE;
							}
							// If it's not the last segment - add link
							if($i != count($CI->uri->segments) && $name) $CI->navbar->add('<a href="'.l($path).'">'.$name.'</a>');
							// Else - set just plain text
							else if($name) $CI->navbar->add($name);
							$names[] = $name;
						}
						// If segment is numeric
						else {
							// Try to get name from title
							if(isset($CI->content['meta']['title'][(3+$key)])){
								$name = $CI->content['meta']['title'][(3+$key)];
							}
							// If it's not last and it's not duplicate
							if($i != count($CI->uri->segments) && isset($name) && !in_array($name,$names)){
							 $CI->navbar->add($name);
							 $names[] = $name;
							}
						}
						$i++;
					}
				 $CI->navbar->compile(2);
				}
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------