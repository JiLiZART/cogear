function comment_edit(id){
	var link = $('edit-'+id);
	var comment = link.getParent().getParent();
	if(comment.getElement('form')){
		repos_add_comments_form();
		reply_comments_form();
		return;
	}
	var body = comment.getElement("div.body");
	var extra = comment.getElement("div.extra");
	body.addClass('hidden');
	if(extra) extra.addClass('hidden');
	$('add_comments').inject(body,'after');
	$('add_comments').getElement('#id').set('value',id);
	new Request.JSON({
		url: '/ajax/comments/edit/',
		data: 'id='+id,
		onRequest: function(){
			loader.inline(link,'after');
		},
		onSuccess: function(re){
			loader.inline(link,'after',true);
			if(re.success){
				$('add_comments').getElement('textarea').set('value',re.msg);
			}
			else msg(lang.errors.error,lang.global.failure);
		}
	}).post();
}