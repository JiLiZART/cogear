window.addEvent('domready',function(){
	for(name in editor.editors){
		ed = editor.editors[name];
			var upload_zone = new Element('div').injectAfter(ed.body.getNext());
			var uploader = new qq.FileUploader({
			    // pass the dom node (ex. $(selector)[0] for jQuery users)
			    element: upload_zone,
			    // path to server-side upload script
			    action: '/upload',
			      template: '<div class="qq-uploader">' + 
                '<div class="qq-upload-drop-area"><span>'+lang.upload.drop_zone_info+'</span></div>' +
                '<span class="qq-upload-button button">'+lang.upload.upload_file+'</span>' +
                '<ul class="qq-upload-list"></ul>' + 
             '</div>',

        // template for one item in file list
        fileTemplate: '<li>' +
                '<span class="qq-upload-file"></span>' +
                '<span class="qq-upload-spinner"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<a class="qq-upload-cancel" href="#">'+lang.upload.cancel+'</a>' +
                '<span class="qq-upload-failed-text">'+lang.upload.failed+'</span>' +
            '</li>',        
        onSubmit: function(id,fileName){
        
        },    
		onComplete: function(id, fileName, responseJSON){
			ed.insert(responseJSON.image);
		}
		});
	}

});

