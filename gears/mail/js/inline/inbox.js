window.addEvent('domready',function(){
	
})

function kickoutFromInbox(pm_id,user_id){
	if(confirm(lang.mail.inbox_kickout_confirm)){
		new Request.JSON({
		url: '/ajax/'+document.location.href.replace('http://','').split('/')[1]+'/kickout/',
		data: { user: user_id,pm: pm_id },
		onComplete: function(re,response){
			if(re.success){
				$('user-' + user_id).destroy();
				msg(re.msg,lang.global.success);
			}
			else {
				msg(re.msg,lang.global.failure);
			}
		}
		}).post();
	}
}