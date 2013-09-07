function comment_destroy(id){
					var link = $('destroy-'+id);
					var comment = link.getParent().getParent();
					if(confirm(lang.form.are_you_shure)){
						var table = $$('.node').pop().get('id').split('-')[0];
						if(table.charAt(table.length-1) == 'e'){
							table += 's';
						}
						new Request.JSON({
						url: '/ajax/comments/destroy/'+ table + '/'+id+'/',
						onRequest: function(){
							loader.inline(link,'after');	
						},
						onComplete: function(){
							loader.inline(link,'after',true);	
							var next = comment.getAllNext();
							var count = 1;
							for(var i = 0; i < next.length; i++){
								if(next[i].getStyle('margin-left').toInt() > comment.getStyle('margin-left').toInt()){
									next[i].destroy();
									count += 1;
								}
								else {
									break;
								}
							}
							$$('.comments_counter').each(function(elem){
									if(elem.get('text').toInt() > 0) elem.set('text',elem.get('text').toInt()-count);
							})
							comment.destroy();
							msg(lang.form.deleted_success);
						}
						}).post();
					}
}