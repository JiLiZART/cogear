   document.addEvent("keydown", function(event){
        event = new Event(event);
        if (event.key == "left" && event.control) {
            if($('prevlink'))
            document.location = $('prevlink').href;
        }
        if (event.key == "right" && event.control) {
            if($('nextlink'))
            document.location = $('nextlink').href;
        }
    });