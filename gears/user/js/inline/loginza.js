window.addEvent('domready',function(){
	$$('.loginza-delete').each(function(elem){
		elem.addEvent('click',function(e){
			var event = new Event(e);
			e.stop();
			if(confirm(lang.form.are_you_shure)){
				new Request.JSON({
				url: elem.get('href'),
				onSuccess: function(response){
					if(response.success){
						elem.getParent().destroy();
						msg(lang.form.deleted_success);
					}
				}
				}).post();
			}
			return false;
		})
	});
});