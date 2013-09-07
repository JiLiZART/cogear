var TextareaGrow = new Class ({
	set: function(elem){
	   var effect = new Fx.Morph(elem, {duration: 'short', transition: 'bounce:in:out'});
	   elem.store('height',elem.getSize().y.toInt());
	   var clone = elem.clone().setStyles({
					'width':elem.getStyle('width').toInt(),
					'position':'absolute',
					'top':elem.getPosition().y,
					'height':elem.retrieve('height'),
					'left':-10000,
					'font-size': elem.getStyle('font-size')
	   }).store('height',elem.getStyle('height')).inject($(document.body));
	   elem.setStyle('overflow-y', 'hidden');
	   
	   if(elem.get('value') != '')this.update(elem,clone,effect);
	   elem.addEvents({
	   'keyup' : function(e){
			this.update(elem,clone,effect);
	   }.bind(this),
	   'keydown' : function(e){
			this.update(elem,clone,effect);
	   }.bind(this),
	   'focus' : function(e){
			this.update(elem,clone,effect);
	   }.bind(this),
	   'blur' : function(e){
			this.update(elem,clone,effect);
	   }.bind(this),
	   'change' : function(e){
			this.update(elem,clone,effect);
	   }.bind(this)
	   });
	},
	update: function(elem,clone,effect){
			clone.set('value',elem.value);
			var height = clone.getScrollSize().y > elem.retrieve('height') ? clone.getScrollSize().y : elem.retrieve('height');
			if(clone.retrieve('height') != height){
			 effect.start({
					  'height': height
			 });
			 clone.store('height',height);
			}
	}
});

window.addEvent('domready', function() {
  txtGrow = new TextareaGrow();
  $$("textarea").each(function(textarea){
    if(textarea.hasClass('grow')) txtGrow.set(textarea);
  });
 });

