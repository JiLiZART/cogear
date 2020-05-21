window.addEvent('domready',function(){
	for(name in editor.editors){
		var e = editor.editors[name];
		e.addButton({keycode:59,title:'ctrl+;',background:'/gears/nodes/img/icon/save_draft.png',action:'save_draft("'+e.el.id+'")',id:'save_draft'})	
		e.addButton({keycode:39,title:'ctrl+"',background:'/gears/nodes/img/icon/load_draft.png',action:'load_draft("'+e.el.id+'")',id:'load_draft'})	
		// Save draft every 30 seconds
		save_draft.periodical(30000,null,e.el.id);
	}
});

function save_draft(id){
	var ed = editor.get(id);
	var textarea = $(id);
	if(textarea.get('value').test(/^\s?$/)) return;
	new Request.JSON({
	url: '/nodes/draft/save/',
	data: 'body='+textarea.get('value'),
	onRequest: function(){
		loader.inline('save_draft','after');
	},
	onComplete: function(re){
		msg(re.msg,re.success);
		loader.inline('save_draft','after',true);
	}	
	}).post();
}

function load_draft(id){
	var ed = editor.get(id);
	var textarea = $(id);
	new Request.JSON({
	url: '/nodes/draft/load/',
	data: 'body='+textarea.get('value'),
	onRequest: function(){
		loader.inline('load_draft','after');
	},
	onComplete: function(re){
		if(re.success){
			textarea.set('value','');
			ed.insert(re.body);
		}
		msg(re.msg,re.success);
		loader.inline('load_draft','after',true);
	}	
	}).post();

}