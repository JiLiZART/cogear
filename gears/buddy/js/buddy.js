window.addEvent('domready',function(){
	var userinfo_panel = $('userinfo_panel');
	if(userinfo_panel){
		var link = userinfo_panel.getElement('a[id^=buddy]');
		if(link){
			var id = link.id.split('-')[1];
			var action = link.getFirst().get('src').search('remove') == '-1' ? 'add' : 'remove';
			var img = link.getElement('img');
			link.removeEvents('click');
			link.addEvent('click',function(){
					new Request.JSON({
					url: '/ajax/buddy/check/',
					data: 'id='+id+'&'+form.toQueryString(),
					onComplete: function(re){
						if(re.success){
							loader.elem('buddy-holder');			
						}
						else {
							msg(re.msg);
						}
					}
					}).post();
					return false;
			});
			form = document.body.getElement('form#buddy');
			//form.removeEvents('submit');
			form.addEvent('submit',function(){
				var form_action = form.getElement('[name=action]').get('value');
				action = link.getFirst().get('src').search('remove') == '-1' ? 'add' : 'remove';
				if(form_action == 'submit'){
					new Request.JSON({
					url: '/ajax/buddy/'+action,
					data: 'id='+id+'&'+form.toQueryString(),
					onSuccess: function(re){
						loader.hide();
						if(re.success){
							switch(action){
								case 'add':
								img.src = '/gears/buddy/img/icons/remove.png';
								img.set('title',lang.buddy.remove);
								break;
								case 'remove':
								img.src = '/gears/buddy/img/icons/add.png';
								img.set('title',lang.buddy.add);
								break;
							}
						}
						msg(re.msg)
					}
					}).post();
					return false;
				}
			});
		}
	}
});