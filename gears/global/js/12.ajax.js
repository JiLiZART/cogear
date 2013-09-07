var Ajax = new Class({
	elements: {},
	page: function(url,element){
		if(this.elements[url]){
			if(this.elements[url].holder.hasClass('hidden')){
				this.elements[url].holder.removeClass('hidden')
			}
			else this.elements[url].holder.addClass('hidden');
		}
		else {
			this.elements[url] = {
				url: url,
				element: element
			};
			var el = this.elements[url];
			var parent = element.getParent();
			el.holder = new Element('div').addClass('ajax-page hidden').inject(parent,'after');
			new Request.HTML({
				url: url,
				update: el.holder,
				onComplete: function(){
					el.holder.removeClass('hidden');
				}
			}).post();
		}
	}
});

window.addEvent('domready',function(){
	ajax = new Ajax();
});