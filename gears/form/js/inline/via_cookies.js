$$('form').each(funciton(form){
	form.addEvent('submit', function()  {
		if($defined(this.isValid) && !this.isValid) {
			return false;
		}
		else {
			this.getElements(".via_cookie").each(function(elem){
				Cookie.write(elem.id,$defined(elem.id+'_md5') ? $(elem.id+'_md5').value : elem.value,{domain: "."+url, path: "/"});
				elem.removeProperty('name');
			});
		}
	});
});