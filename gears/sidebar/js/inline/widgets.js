$$('ul.widget').each(function(ul){ 
	ul.getChildren().each(function(li){
	if(!li.hasClass('undraggable')){
		var link = new Element('a').set('href',li.get('text')).set('text',' ').injectTop(li);
		var img = new Element('img').set('src','/gears/global/img/icon/edit.png').injectTop(link);
	}
	});
});