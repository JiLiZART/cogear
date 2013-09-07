window.addEvent('domready',function(){
	if(!$('community_header')) return;
	var icon = $('community_header').getElement('img.role');
	if(!icon) return;
	var state = icon.get('src').match(new RegExp(/\/(\w+)\.\w{3,4}$/i))[1];
	if(state == 'admin') return;
	var link = icon.getParent();
	var id = $('community_header').getElement("a[id^=community]").get('id').split('-')[1];
	var count = $('community_header').getElement('.members a');
	link.addEvent('click',function(){
		state = icon.get('src').match(/\/(\w+)\.\w{3,4}$/i)[1];
		new Request.JSON({
			url: '/ajax/community/state/',
			data: 'action='+state+'&cid='+id,
			onComplete: function(re){
				if(re.success){
					switch(state){
						case 'join':
						icon.set('src',icon.get('src').replace(state,'leave'));
						count.set('text',count.get('text').toInt()+1);
						break;
						case 'leave':
						icon.set('src',icon.get('src').replace(state,'join'));
						count.set('text',count.get('text').toInt()-1);
						break;
					}
					msg(re.msg)
				}
				else {
					msg(re.msg || lang.errors.error,false);
				}
			}
		}).post();
	});
});