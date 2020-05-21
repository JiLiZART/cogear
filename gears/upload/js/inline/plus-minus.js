window.addEvent('domready',function(){
	$('plus').addEvent('click',function(){
		plus_img_field();
	});
	$('minus').addEvent('click',function(){
		minus_img_field();
	});
	window.addEvent('keypress',function(e){
		var e = new Event(e);
		switch(e.key){
			case '+':
			case '=':
				plus_img_field();
			break;
			case '-':
			case '_':
				minus_img_field();
			break;
		}
	})
});

function plus_img_field(){
		var images = $$('input[name^=image]');
		var last = images.getLast();
		var new_one = last.getParent().clone();
		new_one.getElement('input').set('name','image_'+(images.length+1)).set('id','image_'+(images.length+1));
		new_one.inject(last.getParent(),'after');
}
function minus_img_field(){
		var images = $$('input[name^=image]');
		if(images.length > 1){
			var last = images.getLast();
			last.getParent().destroy();
		}	
}