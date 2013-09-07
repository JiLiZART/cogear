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
 * Loader class
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears javascripts
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
var Loader = new Class({
    Implements: Options,
    options: {
    width: 400,
    height: 100,
    image : "/gears/global/img/ajax-loader.gif",
    z: 10029
    },
    initialize: function(options) {
        this.setOptions(options);
		this.imgHolder = new Element("img",{src:this.options.image});
    },
    elem: function(elem, width, height){
	    elem = typeof(elem) == 'string' ? $(elem) : elem;
	    var options = {
		handler:'adopt',
		destroy: false,
        size: {x: width ? width : this.options.width, y: height ? height : this.options.height},
		onOpen:function(){
				$('sbox-content').getElements('.hidden').removeClass('hidden');
				var size = $('sbox-content').getFirst().getSize();
				$('sbox-window').setStyles({
					width: null,
					height: null,
				});
				this.reposition();
			}
		};
		SqueezeBox.open(elem,options);
	    
	    
    },
    hide: function(){
	    SqueezeBox.close();
    },
    frame: function(url,width,height){
	    if(!width) width = this.options.width;
	    if(!height) height = this.options.height;
		SqueezeBox.open(url,{
	        size: {x: width, y: height},
	        handler: 'iframe'
	    });
    },
    inline: function(elem,where,hide){
		if($type(where) == 'string'){
			if(!hide){
				if(!$('inline-loading'))
				{
					new Element("img",{
					"id": "inline-loading",
					"src": "/gears//global/img/loader.gif",
					"class": "inline-loading"
					}).inject(elem,where);
				}
			}
			else {
				if($('inline-loading'))
				{
					$('inline-loading').destroy();
				}
			}
		}
		else if($type(where) == 'boolean'){
			if(!hide){
				this.imgHolder.injectAfter(elem).setStyle('display','block');
				elem.setStyle('display','none');
			}
			else {
				this.imgHolder.setStyle('display','none');
				elem.setStyle('display','inline-block');
			}
		}
		else {
			if(hide){
			 elem.setStyle('background','none');
			}
			else {
			 elem.setStyle('background','url(/gears//global/img/loader.gif) no-repeat 99% center');
			}
		}
    }
})
window.addEvent('domready',function(){
	loader = new Loader();
});