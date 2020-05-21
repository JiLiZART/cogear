window.addEvent('domready',function(){
	for(name in editor.editors){
		var e = editor.editors[name];
		e.addButton({hotkey:'g',background:'/gears/upload/img/icon/one-click-image.png',action:false,id:'one-click-upload'})	
		upload_init(e);
	}
});

function upload_init(editor){
	link = editor.body.getElement('#one-click-upload');
	var linkIdle = link.get('html');
	var background = link.getStyle('background-image');
	// Uploader instance
	var swf = new Swiff.Uploader({
		path: '/gears/upload/js/inline/Swiff.Uploader.swf',
		url: '/upload/image/',
		verbose: false,
		appendCookieData: true,
		queued: 3,
		allowDuplicates: true,
		multiple: true,
		target: link,
		instantStart: true,
		method: 'post',
		data: 'align=right',
		typeFilter: {
			'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
		},
		onSelectSuccess: function(files) {
			files.each(function(file){
				msg('Загружаю <em>' + file.name + '</em> (' + Swiff.Uploader.formatUnit(file.size, 'b') + ')','Загрузка');
			});
			align = prompt(lang.upload_image.align,'right') || 'center';
			this.setEnabled(false);
			link.setStyle('background-image','url(/gears/upload/img/icon/images_progress.gif)')
		},
		onFileComplete: function(file) {
			this.setEnabled(true);
			var result = JSON.decode(file.response.text);
			if(result.error) {
				msg(result.error);
			} else {
				editor.insert((align == 'center' ? '<p align="'+align+'">'+result.code+'</p>' : result.code.replace('img','img align="'+align+'"')) +"\n");
			}
			file.remove();
		},
		onComplete: function() {
			link.setStyle('background-image',background);
		}
	});
	
/*
	function linkUpdate(){
			if (!swf.uploading) return;
			var size = Swiff.Uploader.formatUnit(swf.size, 'b');
			link.set('html', '<span class="small">' + swf.percentLoaded + '% of ' + size + '</span>');
	}
*/
		// Button state
	link.addEvents({
		click: function() {
			swf.reposition();
		},
		mouseenter: function() {
			swf.reposition();
		},
		mouseleave: function() {
			this.blur();
		},
		mousedown: function() {
			this.focus();
		}
	});
}