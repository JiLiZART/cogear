window.addEvent('domready',function(){
	if($defined($('search_widget'))){
		var input_field = $('search_widget').getElement('input[type=text]');
		input_field.set('value','Поиск…');
		input_field.addEvents({
		'blur' : function(){
			if(this.get('value') == '') this.set('value','Поиск…');
		},
		'focus' : function(){
			this.set('value','');
		}
		})
	}
});