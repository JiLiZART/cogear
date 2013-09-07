window.addEvent('domready',function(){
	for(name in editor.editors){
		var e = editor.editors[name];
		if($('node_createdit')){
			e.addButton({hotkey:'k',background:'/gears/cut/img/icon/cut.png',action:'insert_cut("'+e.el.id+'")'})	
		}
	}
});

function insert_cut(textarea){
	var ed = editor.get(textarea); 
	textarea = $type(textarea) == 'string' ? $(textarea) : textarea;
	var info = prompt(lang.cut.prompt,lang.cut.more);
	if(info && info != ''){
	 var cut = '[cut=' + info + ']';
	}
	else 	var cut = '[cut]';
	ed.insert(cut);
}

