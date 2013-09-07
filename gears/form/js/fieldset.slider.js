window.addEvent('domready',function(){
 $$('fieldset').each(function(fieldset){
  if(fieldset.getFirst().get('tag') != 'legend') return;
  else var el = fieldset.getElement('legend');
  var text = el.get('text');
  el.erase('text');
  var a = new Element("a").addClass('toggler').addClass('s_on').set('html',text).inject(el,'top');
  new Element("div").adopt(fieldset.getChildren().erase(el)).addClass('clear').inject(el,'after');
  });
  mkSlider("legend a.s_on");
  mkSlider("legend a.s_off");
});
function mkSlider(es, hide){
    $$(es).each(function(elem,i){
        var next = elem.getParent().getNext();
        var fieldset = elem.getParent().getParent();
        var slide = new Fx.Slide(next);
        elem.onclick = elem.ondblclick = function(e){
            var e = new Event(e);
            e.stop();
            if(!elem.hasClass("s_off")){
                elem.removeClass("s_on");
                elem.addClass("s_off");
                slide.slideOut();
                if(fieldset.get("tag") == "fieldset") {
                    fieldset.addClass.delay(500,fieldset,"collapsed");
                }
                if(fieldset.get("tag") == "div") {
                    fieldset.addClass.delay(500,fieldset,"collapsed");
                }
                next.setStyle.delay(500,next,["opacity","0"]);
            }
            else {
                elem.removeClass("s_off");
                elem.addClass("s_on");
                if(fieldset.get("tag") == "div") {
                    fieldset.removeClass("collapsed");
                }
                next.setStyle("opacity","1");
                if(fieldset.get("tag") == "fieldset" && fieldset.hasClass("collapsed")) fieldset.removeClass("collapsed");
                
                slide.slideIn();
            }
        };
        if(elem.hasClass("s_off")){
            slide.hide();
            if(fieldset.get("tag") == "fieldset") {
                fieldset.addClass("collapsed");
            }
            if(fieldset.get("tag") == "div") {
                fieldset.addClass("collapsed");
            }
        }
        
    });
}