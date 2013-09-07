function comment_delete(id){
					var link = $('delete-'+id);
					var comment = link.getParent().getParent();
					if(confirm(lang.form.are_you_shure)){
						var table = $$('.node').pop().get('id').split('-')[0];
						if(table.charAt(table.length-1) == 'e'){
							table += 's';
						}
						new Request.JSON({
						url: '/ajax/comments/delete/'+table+'/'+id+'/',
						onRequest: function(){
							loader.inline(link,'after');	
						},
						onComplete: function(){
							loader.inline(link,'after',true);	
							comment.hasClass('deleted') ? comment.removeClass('deleted') : comment.addClass('deleted');
							var img = link.getElement('img');
							img.set('src',img.get('src') == '/gears/comments/img/icon/delete.png' ? '/gears/nodes/img/icon/unpublished.png' : '/gears/comments/img/icon/delete.png')
							//msg(lang.form.deleted_success);
						}
						}).post();
					}
}