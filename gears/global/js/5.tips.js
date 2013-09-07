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
 * Tips
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears javasscripts
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
window.addEvent('domready',function(){
	if(Browser.Engine.trident && Browser.Plugins.Flash.version == 4) return;
	new Tips('[title]');
});

// ------------------------------------------------------------------------