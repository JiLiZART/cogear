window.addEvent('domready',function(){
	for(name in editor.editors){
		var e = editor.editors[name];
		e.addButton({hotkey:'g',background:'/gears/video/img/icon/video.png',action:'video("'+e.el.id+'")'})
	}
});

function video(textarea){
	var ed = editor.get(textarea);
	textarea = $type(textarea) == 'string' ? $(textarea) : textarea;
	var link = prompt(lang.video.prompt,'');
	if(link){
		ed.tag('[media]','[/media]',link);
	}
}

