window.addEvent('domready', function(){
    if($('user_profile')) {
		var container = $('tabs');
			new SimpleTabs(container, {
				selector: 'h1'
			});
	}
});
