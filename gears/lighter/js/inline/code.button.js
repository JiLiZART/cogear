window.addEvent('domready',function(){
	for(name in editor.editors){
		var e = editor.editors[name];
		e.addButton({title:'ctrl+e',keycode:101,background:'/gears/lighter/img/icon/code.png',action:'insert_code("'+e.el.id+'")'})	
	}
});

function insert_code(textarea){
	var ed = editor.get(textarea); 
	textarea = $type(textarea) == 'string' ? $(textarea) : textarea;
	ed.tag('<code class="php">','</code>');
}

