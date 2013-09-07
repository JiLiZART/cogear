function create_preview(form){
    var form = form ? $(form) : $('createdit');
    var textarea = form.getElement('textarea[name=body]');
    if($('preview')) var value =  $('preview').get('value');
    form.addEvent('submit',function(){
	if(form.getElement('[name=action]').value == 'preview'){
		if(!$('preview-layer')){
			new Request.JSON({
			url : '/ajax/parser/preview/',
			data : form.toQueryString(),
			onRequest : function(){
				loader.inline(form.preview.getParent(),'after');	
			    $('preview').set('disabled',true);
			},
			onComplete : function(response){
				$('preview').removeProperty('disabled');
				if(!$chk(response)) return;
				if(response.success){
						  var preview = new Element('div').set('id','preview-layer').addClass('preview').setStyle('min-height',textarea.getStyle('height')).setStyle('width',textarea.getStyle('width')).inject(textarea,'before');
						  var node = new Element('div').addClass('node').inject(preview,'top');
						  var body = new Element('div').addClass('body').inject(node,'top').set('html',response.msg);
						  textarea.setStyle('display','none');
						  preview.addEvent('click',function(){
							  $('preview-layer').destroy();
							  textarea.setStyle('display','block');
							  $('preview').set('value',value);
						  });
						  $('preview').set('value',lang.edit.edit);
						  var p = $('preview-layer').getPosition();
						  new Fx.Scroll(window,{ duration: 500,wait: false}).start(0,p.y-window.getSize().y/2);
				}
				else {
					msg(response.msg);
				}
				loader.inline(form.preview.getParent(),'after',true);
			}
			}).post();
		}	
		else {
			new Fx.Scroll(window,{ duration: 1000}).toElement(form);
			textarea.setStyle('display','block');
			$('preview').set('value',value);
			$('preview-layer').destroy();
		}
		return false;
	}
	if($('preview-layer')){
		$('preview-layer').getNext().setStyle('display','block');
		$('preview').set('value',value);
		$('preview-layer').destroy();
	}
	});
}

window.addEvent('domready',function(){
	$$('form').each(function(form){
		if(form.getElement('#preview')){
			create_preview(form);
		}
	 });
})