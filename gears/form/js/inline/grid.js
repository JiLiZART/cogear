window.addEvent('domready',function(){
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
	 $$(".grid").each(function(grid){
	  var thead = grid.getElement('thead');
	  var delta = ((document.documentElement.scrollTop || document.body.scrollTop) - thead.getPosition().y);
	  if(delta > 0 && !thead.retrieve('cloned')){
	   var gfh = new Element("table").addClass("grid-fixed-thead").setStyle('width',thead.getParent().getStyle('width')).inject(grid,'before');
	   var clone = thead.clone().inject(gfh,'top');
	   thead.store('cloned',true);
	  }
	  else if(delta <= 0 && thead.retrieve('cloned')){
	   grid.getPrevious().destroy();
	   thead.store('cloned',null)
	  }
	 });
  });
});
