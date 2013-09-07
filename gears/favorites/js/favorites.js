window.addEvent('domready',function(){
	$$('.favorite-action').each(function(img){
		var link = img.getParent();
		var nid = img.get('id').split('-')[1];
		link.removeEvents('click');
		link.addEvent('click',function(){
					var action = link.getFirst().get('src').search('remove') == '-1' ? 'add' : 'remove';
					new Request.JSON({
					url: '/ajax/favorites/action/',
					data: 'nid='+nid+'&action='+action,
					onComplete: function(re){
						if(re.success){
								switch(action){
									case 'add':
									img.src = '/gears/favorites/img/icon/remove.png';
									img.set('title',lang.favorites.remove);
									break;
									case 'remove':
									img.src = '/gears/favorites/img/icon/add.png';
									img.set('title',lang.favorites.add);
									break;
								}
						}
						msg(re.msg || lang.errors.error);
					}
					}).post();
					return false;
			});
	});
});