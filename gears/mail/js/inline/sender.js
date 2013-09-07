window.addEvent('domready',function(){
	var form = $('mail_sender');
	form.addEvent('submit',function(e){
		if($defined(formCheck) && formCheck.form.isValid !== true) return;
		var e = new Event(e);
		if(form.getElement('#action').value != 'preview'){
			if(confirm(lang.form.are_you_shure)){
				loader.inline(form.getElement('#submit'),'after');
				sendMail(form);
			}
		}
		return false;
	});
})

function sendMail(form,start){
			if(start){
					form.getElement('#start').set('value',start);
					form.getElement('#action').set('value','submit');
			}
			new Request.JSON({
			url: form.get('action'),
			data: form.toQueryString(),
			onComplete: function(re){
				if(re.success && re.start){
					sendMail(form,re.start);
				}
				else {
					form.getElement('#start').set('value',0);
					loader.inline(form.getElement('#submit'),'after',true);
				}
				msg(re.msg);
			}
			}).post();			
}
	