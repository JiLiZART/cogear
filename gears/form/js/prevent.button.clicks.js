$$('form').each(function(form){
	form.getElements('input[type=submit]').each(function(button){
		button.addEvent('click',function(){
			this.set('disabled','true');
			this.set.delay(1000,this,['disabled','']);
		});
	});
});