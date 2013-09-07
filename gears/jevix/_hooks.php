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
 * Jevix hooks
 *
 * @package		CoGear
 * @subpackage	Jevix
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Add parser rules
	*
	* @param	object
	* @return	void
	*/
	function jevix_parser_construct_(&$Parser){
		//$Parser->prepare['input'][] = 'parse_jevix|comment';
		array_insert($Parser->prepare['textarea'],'parse_jevix|node',0);
		array_insert($Parser->prepare['comment'],'parse_jevix|comment',0);
	}
	// ------------------------------------------------------------------------
	
	/**
	* Parse code
	*
	* @param	string	Value.
	* @param	string	Type.
	* @param	boolean  AutoBR mode.
	* @return	string
	*/
	function parse_jevix($value,$type = 'node',$autobr = TRUE){
		$CI =& get_instance();
		if($CI->input->post('disable_jevix') && acl('jevix disable')) return $value;
		$jevix =& $CI->jevix;
		switch($type){
			case 'comment':
				$tags = array('a','p','img','i','b','u','em','strong','s','strike','li','ol','ul','dl','dt','dd','sup','sub','abbr','br','table','tr','td','tbody','th','code','pre','blockquote');
			break;
			default:
				 $tags = array('a','p','img','i','b','u','em','strong','s','strike','nobr','li','ol','ul','dl','dt','dd','sup','abbr','pre','hr',	 'acronym','small','big','h1','h2','h3','h4','h5','h6','br','table','tr','td','tbody','th','code','pre','blockquote');
		}
		$jevix->cfgAllowTags($tags);

		$jevix->cfgSetTagShort(array('br','img'));

		$jevix->cfgSetTagPreformatted(array('code','pre'));

		 $jevix->cfgAllowTagParams('img', array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int','border','class'));
		$jevix->cfgAllowTagParams('a', array('title','href','class','target'));
		$jevix->cfgAllowTagParams('p', array('align'));
		$jevix->cfgAllowTagParams('code', array('class'));
		$jevix->cfgAllowTagParams('table', array('border', 'cellpadding', 'cellspacing', 'width','class'));
		$jevix->cfgAllowTagParams('td', array('width', 'colspan', 'rowspan','valign','align'));
		$jevix->cfgAllowTagParams('th', array('width', 'colspan', 'rowspan','valign','align'));

		$jevix->cfgSetTagParamsRequired('img', 'src');
		$jevix->cfgSetTagParamsRequired('a', 'href');

		$jevix->cfgSetTagCutWithContent(array('script', 'object', 'iframe', 'style'));

		$jevix->cfgSetTagChilds('ul', array('li'), FALSE, TRUE);
		$jevix->cfgSetTagChilds('ol', array('li'), FALSE, TRUE);
		$jevix->cfgSetTagChilds('dl', array('dt','dd'), FALSE, TRUE);
		$jevix->cfgSetXHTMLMode(false);
		$jevix->cfgSetTagParamDefault('a', 'rel', 'nofollow');
		$jevix->cfgSetAutoLinkMode(TRUE);
		$jevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)'), array('±', '©', '®'));
		$jevix->cfgSetAutoBrMode(TRUE);
		$errors = null;
		$jevix->cfgSetTagNoTypography('code');
		$result = $jevix->parse($value,$errors);
		$result = str_replace(array('<br>','<br/>'),'',$result);
		return $result;
	};
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------