$$('form').each(function(form){
	if(!form.getElement('.md5')) return;
	form.addEvent('submit', function()  {
	if($defined(this.isValid) && !this.isValid) {
		return false;
	}
	else {
		this.getElements(".md5").each(function(elem){
			new Element('input').set('type','hidden').set('name',elem.name).set('id',elem.name+'_md5').set('value',$chk(elem.value) ? hex_md5(hex_md5(elem.value)) : elem.value).inject(elem,'after');
			elem.removeProperty('name');
		});
	 }
	});
});
