function selectIcon(select_id,elem){
 var options = $(select_id).options;
 elem = $type(elem) == 'string' ? $(elem) : elem;
 var value = elem.get('id').split('-').pop();
 for(var i = 0; i < options.length; i++){
	 if(options[i].value == value){
		  if(options[i].get('selected')){
		   elem.removeClass('selectIcons_on').addClass('selectIcons');
		   options[i].removeProperty('selected');
		  }
		  else {
		   elem.removeClass('selectIcons').addClass('selectIcons_on');
		   options[i].set('selected','true');
		  }
	 }
 }
 markIcons();
}

function markIcons(){
 $$('.image_select select').each(function(select){
   var options = select.options;
   for(var i = 0; i < options.length; i++){
	elem = $(select.id+'-'+options[i].value);
	if($chk(elem)){
		if(options[i].get('selected')){
		 elem.hasClass('selectIcons_on') ? true : elem.removeClass('selectIcons').addClass('selectIcons_on');
		}
		else if(!select.get('multiple')){
		 elem.removeClass('selectIcons_on').addClass('selectIcons');
		}
	 }	
   }
 }); 
}

window.addEvent('domready',function(){
  $$('.image_select select').each(function(select){
   select.addEvent('change', function(){markIcons()});
  });
 markIcons();
});