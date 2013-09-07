window.addEvent('domready',function(){
	var fx = new Fx.Tween('docs');
	if(!$('docs-toggler')) return;
	$('docs').setStyle('top',-$('docs-contents').getStyle('height').toInt());
	$('docs-toggler').addEvent('click',function(){
		if($('docs').getStyle('top').toInt() < 0){
			 fx.start('top',$('docs').getStyle('top').toInt(),0);
		 }
		 else {
			 fx.start('top',$('docs').getStyle('top').toInt(),-$('docs-contents').getStyle('height').toInt());
		 }
	});
});