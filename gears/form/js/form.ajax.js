window.addEvent('domready',function(){
	$$('form.ajax').each(function(form){
			form.addEvent('submit',function(){
				if($defined(this.isValid) && !this.isValid) {
					return false;
				}
				else {
					new Request.JSON({
					url: form.get('action') ? form.get('action') : location.href,
					// Important - refresh form data taken
					data: $(form.get('id')).toQueryString(),
					onRequest: function(){
						form.getElements('input[type=button]').each(function(button){
							button.set('disabled',true);
						})
					},
					onSuccess: function(re,text){
						form.getElements('input[type=button]').each(function(button){
							button.removePropetry('disabled');
						})
						if(re && re.msg){
							msg(re.msg)
						}
						else {
							new Element('div').set('html',text).replaces(form);
						}
					}
					}).post();
					return false;
				}
			});
	});
});