function sortComments(a,b){
	return a.get('id').split('-').pop() - b.get('id').split('-').pop();
}
var CommentsUpdater = new Class({
	Implements : [Options, Events],
	options: {
		id: 'comments-update',
		position: 'right',
		updater_img: {
			passive: '/gears/comments/img/update.gif',
			active: '/gears/comments/img/update_active.gif'
		},
		target_id: false,
		target_type: false,
		update: false,
		new_comments_class: false,
		url: '/ajax/comments/get/',
		counters_class: 'comments_counter',
		highlight_color: '#D4EFFD'
	},
	initialize: function(options){
		this.setOptions(options);
		this.build();
		
	},
	build: function(){
		this.options.position = Cookie.read('comments-navigator-position') || this.options.position;
		this.updater = new Element('div').set('id',this.options.id).setStyles({
			position:'fixed',
			top:document.body.getSize().y/2,
			opacity: 0.8
		}).setStyle(this.options.position,0).inject(document.body,'bottom');	
		this.director = new Element('div').set('id','director').inject(this.updater,'top');
		this.mover = new Element('a').setStyle('cursor','pointer').set('html', this.options.position == 'left' ? '&rarr;' : '&larr;')
		.addEvent('click',function(){
				this.options.position = this.options.position == 'left' ? 'right' : 'left';
				this.updater.setStyle(this.options.position,0).setStyle(this.options.position == 'left' ? 'right' : 'left',null);
				this.mover.set('html',this.options.position == 'left' ? '&rarr;' : '&larr;');
				Cookie.write('comments-navigator-position',this.options.position,{domain:'.'+url,path:'/'});
		}.bind(this)).inject(this.director,'top');
		this.update_comments = new Element('img').set('title','Alt+Shift+R').set('src',this.options.updater_img.passive).setStyle('cursor','pointer').inject(this.updater,'bottom');
		var table = $$('.node').pop().get('id').split('-')[0];
		if(table.charAt(table.length-1) == 'e'){
			table += 's';
		}
		this.options.url += this.options.target_type || table+'/';
		this.options.url += this.options.target_id || $$('.node').pop().get('id').split('-').pop();
		this.update_comments.addEvent('click',function(){
				this.update();
		}.bind(this));

		this.build_linker();

		document.addEvent('keydown',function(e){
			var e = new Event(e);
			if(e.shift && e.alt && ('r').search(e.key) !== -1){
				this.update();
			}
			else if(e.shift && e.alt && ('d').search(e.key) !== -1 && this.linker){
				this.linker.fireEvent('click');
			}
		}.bind(this));
	},
	build_linker: function(){
		this.new_comments = this.options.new_comments_class ? $$(this.options.new_comments_class) : $$('.comment.new');
		this.new_comments.sort(sortComments);
		if(this.new_comments.length > 0){
			if(!this.linker_holder) this.linker_holder = new Element('div').set('id','linker-holder').inject(this.updater,'bottom');
			if(!this.linker){
				this.linker = new Element('a').setStyle('cursor','pointer').inject(this.linker_holder,'bottom').set
				('text',this.new_comments.length).set('title','Alt+Shift+D');

				this.linker.addEvent('click',function(){
						comment = this.new_comments.shift();
						new Fx.Scroll(window).start(0,comment.getPosition().y-window.getSize().y/2).chain(
							function(){comment.highlight('#D4EFFD').removeClass('new');}
						);
						this.linker.set('text',this.new_comments.length);
						$$('.new_comments')[0].set('text', this.new_comments.length > 0 ? ' +'+this.new_comments.length : '');
						if(this.new_comments.length == 0){ 
							this.linker.destroy();
						}
				}.bind(this))
			}
			else {
				this.linker.set('text',this.new_comments.length).inject(this.linker_holder,'bottom');
			}
		}
		else if(this.linker){
			this.linker.destroy();		
		 }
	},
	update: function(stop_reset){
		var comments = [];
		$$('.comment').each(function(div){
		     comments.push(div.get('id').split('-').pop());
		     //if(div.hasClass('new')) div.removeClass('new');
		});
		new Request.JSON({
				url: this.options.url,
				data: 'comments='+comments.join(','),
				onRequest: function(){
					this.update_comments.set('src',this.options.updater_img.active);
				}.bind(this),
				onComplete: function(re){
					this.update_comments.set('src',this.options.updater_img.passive);
					if(!stop_reset && $defined(repos_add_comments_form)){
						repos_add_comments_form(null,null,null,true);
					}
					if(re.success){
						holder = new Element('div').setStyle('display','none').inject('comments','top');
						re.comments.each(function(comment){
								if(comment.replace){
								holder.set('html',comment.code);
									repos_add_comments_form(null,null,null,true);
									holder.getFirst().set('id','temp');
									holder.getFirst().replaces($('comment-'+comment.replace));
									$('temp').set('id','comment-'+comment.replace);
								}
								if(!$('comments').getElement('#comment-'+comment.id)){
								holder.set('html',comment.code);
								 if(comment.parent){
									var parent = $('comment-'+comment.parent);
									var parent_margin = parent.getStyle('margin-left').toInt();
									var find = false;
									parent.getAllNext().each(function(next_comment){
										if(next_comment.getStyle('margin-left').toInt() <= parent_margin && find == false){
											holder.getFirst().inject(next_comment,'before').highlight(this.options.highlight_color);						
											find = true;
										}
									},this);
									if(!find){
										holder.getFirst().inject(parent.getAllNext().length == 0 ? parent : parent.getAllNext().getLast(),'after').highlight(this.options.highlight_color);		
									}
								}
								else {
									holder.getFirst().inject('comments','bottom').highlight(this.options.highlight_color);
								}
							}
						},this);
						this.build_linker();
						if($$('.new_comments').length == 0){
							new Element('a').addClass('new_comments').set('href',$$('.node > .info > .comments_counter')[0].get('href')).inject($$('.node > .info > .comments_counter')[0],'after');
						}
						$$('.new_comments')[0].set('text', re.new_count > 0 ? (' +' + re.new_count) : '');
						var counters = $$('.' + this.options.counters_class);
										if(counters){
							var comments = $$('.comment');
							counters.each(function(counter){
								counter.set('html',re.count);
							});
						}
						if(!stop_reset){
							reply_comments_form();
						}
					}
				}.bind(this)
		}).post();		
	}
});