var gEditor = new Class ({
	editors : {},
	add : function(editor){
		editor = $type(editor) == 'string' ? $(editor) : editor;
		if(editor.get('tag') == 'form'){
			editor.getElements('textarea').each(function(elem){
				if(elem.hasClass('no-editor')) return;
				if(!$defined(this.editors[editor.get('id')])) this.editors[elem.get('id')] = new Editor(elem);
			}.bind(this));
		}
		else if(editor.get('tag') == 'textarea' && editor.getStyle('position') != 'absolute') {
			if(!$defined(this.editors[editor.get('id')])) this.editors[editor.get('id')] = new Editor(editor);
		}		
	},
	get: function(name){
		return this.editors[name];
	}
});

document.addEvent('domready',function(){
  if(Browser.Engine.webkit){
   var doc = window;
  } 
  else if(Browser.Engine.presto){
   var doc = document;
  }
  else {
   var doc = document;
  }
  doc.addEvent('scroll',function(){
	 $$(".editor").each(function(editor){
	 var top = window.getScroll().y;
	 var delta = top  - editor.getNext().getPosition().y;
	  if(delta > 0 && delta < (editor.getNext().getSize().y - editor.getSize().y)){
			editor.setStyles({'position':'relative','top':delta+editor.getSize().y});
	  }
	  else {
			editor.setStyle('position','static');
	  }
	 });
  });
});