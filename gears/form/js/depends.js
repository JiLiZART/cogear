document.addEvent('domready', function()  {
		$$('[class^=depends]').each(function(elem){
			var args = elem.get('class').replace('depends','').replace(/['\[\]]/gi,'').split(',');
			var field = args[0];
			var values = args[1];
			var slider = new Fx.Slide(elem.getParent());
			slider.hide();
			elem.set('disabled','disabled');
			if($defined($(field))) {
				switch($(field).get('type')){
					case 'checkbox':
					var value = $(field).get('checked') ? 'on' : 'off';
					default:
					var value = $(field).value;
				}
				if(values.contains(value)){
					slider.show();
					elem.removeProperty('disabled');
				}
				$(field).addEvent('change',function(){
					switch($(field).get('type')){
						case 'checkbox':
						value = $(field).get('checked') ? 'on' : 'off';
						default:
						value = $(field).value;
					}
					if(values.contains(value)){
						slider.slideIn();
						elem.removeProperty('disabled');
					}
					else {
						slider.slideOut();
						elem.set('disabled','disabled');
					}	
				});
			}	
		})
	});