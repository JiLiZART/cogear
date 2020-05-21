document.addEvent('domready', function()  {
		var sortlists = new Sortables($$('.sortables'),{
			clone: true,
			revert: { duration: 400, transition: 'quad' },
			onComplete : function(){
				$$('.undraggable').each(function(li){
					li.inject(li.getParent(),'bottom');
				})
				var from_name = $$('.sortables').getLast().get('id');
				items = new Array();
				$$('.sortables')[0].getElements('li').each(function(elem){
					if(elem.get('id') && elem.get('id')){
					 items.combine([elem.get('id').split('-').getLast()]);
					}
				});
				new Request({
					url:document.location.href,
					data:from_name+'='+items.join(',')
				}).post();
			}
		});
		sortlists.removeItems($$('.undraggable'));
	});