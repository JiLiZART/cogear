var Editor = new Class({
		Implements: [Options],
		options: {
			default_buttons : [
				{tag:'b',y:'0',keycode:98,title:'ctrl+b'},
				{tag:'i',y:'-32px',keycode:105,title:'ctrl+i'},
				{tag:'u',y:'-63px',keycode:117,title:'ctrl+u'},
				{tag:'s',y:'-96px',keycode:107,title:'ctrl+k'},
				{tag:'h1',y:'-128px',hotkey:'1',title:'ctrl+1'},
				{tag:'h2',y:'-160px',hotkey:'2',title:'ctrl+2'},
				{tag:'h3',y:'-192px',hotkey:'3',title:'ctrl+3'},
				{y:'-224px',action:"this.insertList('ul')",hotkey:'4',title:'ctrl+4'},
				{y:'-256px',action:"this.insertList('ol')",hotkey:'5',title:'ctrl+5'},
				{y:'-288px',action:"this.insertLink()",keycode:108,title:'ctrl+l'},
				{x:'-30px',y:'2px',action:"this.insertUser()"},
				{x:'-30px',y:'-30px',tag:'blockquote',keycode:113,title:'ctrl+q'}
			],	
			brackets: {open:'<',close:'>'},
			x: '2px',
			y: '2px',
			lang : {
				link : 'Укажите адрес ссылки:',
				link_text : "Укажите текст ссылки:",
				user : 'Укажите имя пользователя:'
			}
		},
		selection: '',
		start: '',
		end: '',
		initialize: function(element,options){
			this.el = element;
			this.setOptions(options);
			this.buttons = this.options.buttons ? $merge(this.options.default_buttons,this.options.buttons) : this.options.default_buttons;
			this.build();
		},
		build: function(){
			this.body = new Element('div').addClass('editor').injectBefore(this.el);
			this.body.setStyle('width','100%').set('id',this.el.id+'-editor');
			this.buttons.each(function(button){
				this.addButton(button);
			},this);			
		},
		addButton: function(button){
		button.el = new Element('a').set('html','&nbsp;');
				if($defined(button.background)){
					button.el.setStyle('background-image','url('+url+button.background+')');
				} 
				else {
					button.el.setStyle('background-image','url('+url+'/gears/editor/img/buttons.gif)');
				}
				if(($defined(button.x) || $defined(this.options.x)) && ($defined(button.y) || $defined(this.options.y))){
					button.el.setStyle('background-position',(button.x || this.options.x)+' '+(button.y || this.options.y));
				}
				if($defined(button.id)){
					button.el.set('id',button.id);
				}
				if($defined(button.title)){
					button.el.set('title',button.title);
				}
				if($defined(button.icon)){
					new Element('img').set('width','16px').set('height','16px').set('src',button.icon).inject(button.el,'top');
				}
				button.el.addClass('btn').inject(this.body,'bottom');
				if($defined(button.action)){
						 button.el.addEvent('click',function(){
						eval(button.action);
						this.el.focus();
						}.bind(this));
				}
				else {
					button.el.addEvent('click',function(){
					var open = this.options.brackets.open + button.tag + this.options.brackets.close;
					var close =  this.options.brackets.open + '/' + button.tag + this.options.brackets.close;
					this.tag(open,close);
					}.bind(this));
				}
				window.addEvent('keypress',function(event){
				if(event.control){
					if($defined(button.keycode) && button.keycode == event.code ||
					$defined(button.hotkey) && button.hotkey == event.key ||
					$defined(button.hotkeys) && button.hotkeys.contains(event.key)
					){
						button.el.fireEvent('click');
						event.stop();
					}
				}
			}.bind(this));
		},
		tag: function(open,close,inner,find_n_replace){
			this.el.focus();
			if(!close){
				open = this.options.brackets.open + open + this.options.brackets.close;
				close = this.options.brackets.open + '/' + open + this.options.brackets.close;
			}
			this.getSelection();
			if(find_n_replace){
				this.selection = this.selection.replace(/\r/g,'');
				if(this.selection.match(new RegExp(find_n_replace.find,'gm'))){
				this.selection = this.selection != '' ? this.selection : ' ';
				this.selection = this.selection.replace(new RegExp(find_n_replace.find,'gm'),find_n_replace.replace);
				}
				else this.selection = this.selection != '' ? this.selection : inner || '';
			}
			else this.selection = this.selection != '' ? this.selection : inner || '';
			var curPos = this.getCurPos();
			this.el.value = this.start + open + this.selection + close + this.end;
			if (curPos.start == curPos.end){
				var nCurPos = curPos.start + open.length;
			}
			else{
				var nCurPos = String(this.start + open + this.selection + close).length;
			}
			this.setCurPos(nCurPos,nCurPos);
			this.el.fireEvent('change');
		 },
		tagText: function(tag,text){
			this.tag(tag,false,false,text);	
		},
		 insertBeforeLine: function(text){
			this.getSelection();
			if (this.selection!='') {
				selection = text + ' ' + this.selection.replace(/\n/g,'\n'+text+' ');
				this.el.value = this.start + selection + this.end;	
			}
		 },
		 insertList: function(type){
			 this.getSelection();
			 var open = '<'+type+'>\n';
			 var close = '\n</'+type+'>';
			 var find_n_replace = {find:'^(.+)',replace:'\t<li>$1</li>'};
			 var inner = '\t<li></li>';
			 this.tag(open,close,inner,find_n_replace);			
		 },
		 insertLink: function(){
			 this.getSelection();
			 if (this.selection != '') {
				 var link = prompt(this.options.lang.link,'http://');
				 if(link){
					 this.el.value = this.start + '<a href="'+link+'">' + this.selection + '</a>' + this.end;	 
				 }
			 }
			 else {
				 var link = prompt(this.options.lang.link,"http://");
				 if(link){
					 var text = prompt(this.options.lang.link_text,link)
					 if(text){
						 this.tag('<a href="'+link+'">','</a>',text);
					 }
					 else {
						 this.tag('<a href="'+link+'">','</a>',link);
					 }
				 }
			 }
			this.setCurPos(this.el.value.length,this.el.value.length);
			this.el.fireEvent('change');
		 },
		 insertUser: function(){
			 this.getSelection();
			 var user = prompt(this.options.lang.user,'');
			 if(user){
				this.insert('[user='+user+']');
			 }
			this.setCurPos(this.el.value.length,this.el.value.length);
		 },
		 insert: function(text){
			 this.el.focus();
			 this.getSelection();
			 if(text){
				 this.el.value = this.start+this.selection+text+this.end;
			 }
			 var cur = String(this.start+this.selection+text+this.end).length;
			 this.setCurPos(cur,cur);
			 this.el.fireEvent('change');
			 this.el.fireEvent('focus');
		 },
		 getSelection: function(){
			this.curPos = this.getCurPos();
			this.start = this.el.value.substring(0,this.curPos.start);
			this.selection = this.el.value.substring(this.curPos.start,this.curPos.end);
			this.end = this.el.value.substring(this.curPos.end);
		 },
		 setCurPos: function(start, end){
			this.el.focus();
			if(this.el.createTextRange) {
				var range = this.el.createTextRange();
				range.move("character", start);
				range.select();
			} 
			else if(this.el.selectionStart) {
				this.el.setSelectionRange(start, end);
			}
		 },
		getCurPos: function(){
			var cur = {start: 0, end: 0};
			// Add some Opera hack
			if (document.selection && $defined(document.selection.createRange) && !Browser.Engine.presto) {
				var range = document.selection.createRange();
				var tmp_range = range.duplicate();
				tmp_range.moveToElementText(this.el);
				tmp_range.setEndPoint('EndToEnd', range);
				cur.start = tmp_range.text.length - range.text.length;
				cur.end = cur.start + range.text.length;
			}
			else if (this.el.setSelectionRange){
				cur.start= this.el.selectionStart;
				cur.end = this.el.selectionEnd;
			} 
			else if (!document.selection) {
				return false;
			} 
			return cur;
		}		 
});