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
 * Show-hide elements by class (show-hide/hide-show)
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears javascripts
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
window.addEvent('domready',function(){
	$extend($$('.show-hide'),$$('.hide-show')).each(function(elem){
		var action = elem.hasClass('show-hide') ? 'show' : 'hide';
		var classes = {show:'show-hide', hide:'hide-show'};
		var show = new Element('img',{'src':'/gears/global/img/icon/'+action+'.png'}).setStyle('cursor','pointer');
		show.inject(elem,'before');
		var fx = new Fx.Slide(elem,{mode:'vertical'});
		var storage = new Hash.Cookie('show-hide');
		if(storage.get('action') != 'show' && action == 'hide'){
		 fx.hide();
		 show.set('src','/gears/global/img/icon/hide.png')
		}
		else show.set('src','/gears/global/img/icon/show.png')
		show.addEvent('click',function(e){
			var e = new Event(e);
			action = elem.getParent().getStyle('height').toInt() == 0 ? 'show' : 'hide';
			storage.set('action',action);
			show.set('src','/gears/global/img/icon/'+action+'.png')
			fx.toggle();
		});
	});
});
// ------------------------------------------------------------------------