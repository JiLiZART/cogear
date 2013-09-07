window.addEvent('domready',function(){
	comments_updater = new CommentsUpdater();
    /* comments_updater.update.periodical(30000,comments_updater,true); */
	var href = location.href.split('#');
	if($defined(href[1]) && $(href[1]) && href[1].search('comment') != -1){
		 new Fx.Scroll(window).start(0,$(href[1]).getPosition().y-window.getSize().y/2);
		 $(href[1]).addClass('new');
	 }
	if($('add_comments') && $('add_comments').get('tag') == 'form'){
		form = $('add_comments');
		form.addEvent('submit',function(){
			if(this.isValid && form.getElement('[name=action]').value == 'submit'){
				var submit = form.getElements('[type=submit]').pop();
				var table = $$('.node').pop().get('id').split('-')[0];
				if(table.charAt(table.length-1) == 'e'){
					table += 's';
				}
				new Request.JSON({
				url: '/ajax/comments/post/'+ table,
				data: this.toQueryString(),
				onRequest: function(){
					loader.inline(submit.getParent(),'after');	
					submit.set('disabled',true);
				},
				onComplete: function(re,response){
					submit.removeProperty('disabled');
					loader.inline(submit.getParent(),'after',true);
					if(!$chk(re)) return;
					if(re.success && re.success === true){
						msg(re.msg,lang.global.success);
						comments_updater.update();
					}
					else {
						msg(re.msg);
					}
				}
				}).post();
				return false;
			}
		})
	repos_add_comments_form();
	reply_comments_form();
	}
});
function repos_add_comments_form(pid, reply_id,back){
		if(!back) $('add_comments').inject($('add_comments_place'),'after');		
		$('add_comments').getElement('#parent-id').set('value',pid || '');
		var id = $('add_comments').getElement('#id').get('value');
		if(id && id != ''){
			var comment = $('comment-'+id);
			var body = comment.getElement('.body');
			var extra = comment.getElement('.extra');
			body.removeClass('hidden');
			if(extra) extra.removeClass('hidden');
		}
		$('add_comments').getElement('#id').set('value','');
		$$('.reply').each(function(el){
			if(reply_id && el.id != reply_id || !reply_id){
				el.setStyle('display','block');
			}
		});
		$('add_comments').getElement('textarea').set('value','');
}
function reply_comments_form(){
	$('post_comment').removeEvent('click');
	$('post_comment').addEvent('click',function(){
		repos_add_comments_form();
	});
	$$('.reply').each(function(reply,i,elements){
		reply.removeEvent('click');
		reply.addEvent('click',function(){
			showReply(this);
			return false;
		}.bind(reply));
	});
}

function showReply(element){
			if(element.getParent().getPrevious().get('tag') == 'form') return;
			// IE 6 fix - to show buttons normally
			if(Browser.Engine.trident){
			 var div = new Element('div').setStyle('float','left').inject(element.getParent(),'before');
			 new Element('div').addClass('clear').inject(div,'after');
			 $('add_comments').inject(div,'top');
			}
			else $('add_comments').inject(element.getParent(),'before');
			element.setStyle('display','none');
			repos_add_comments_form(element.id.split('-')[1],element.id,true);
			return false;
}