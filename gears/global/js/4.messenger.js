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
 *  Messenger js class and function
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears javascripts
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
var msgBox = new Class({
   initialize: function(){
	this.roar = new Roar({
			position: 'upperRight'
	});
   },
   show: function(msg, success){
	if(typeof(success) == 'undefined'){
	 success = 'Внимание';
	}
	if($type(success) == 'boolean'){
		success = success ? lang.global.success : lang.global.failure;
	}
	this.roar.alert(success,msg);
   }
});
messenger = new msgBox();

function msg(msg,success){
 if (typeof(msg) != 'undefined' && msg) {
	messenger.show(""+msg,success);
 }
}
// ------------------------------------------------------------------------