window.addEvent('domready',function(){
	$$('input.index-promote').each(function(checkbox){
		checkbox.addEvent('click',function(){
			var nid = this.get('id').split('-').getLast();
			new Request.JSON({
				url: "/ajax/index/promote/",
				data: 'nid='+nid,
				method: 'post',
				onComplete: function(re){
					msg(re.msg);
				}
			}).post();
		});
	});
});