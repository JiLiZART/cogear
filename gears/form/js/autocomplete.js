function autocomplete(el,url,multiple){
		el = $type(el) == 'object' ? el : $(el);
		var completer = new Autocompleter.Ajax.Json(el.id, '/ajax'+url,{
		multiple: $chk(multiple) ? true : false,
		minLength: 2,
		autoSubmit: true,
		maxChoices: 5,
		data: 'value='+el.value,
		onRequest: function(){
			loader.inline(el)
		},
		onComplete: function(){
			loader.inline(el,null,true)
		}		
		});
    }